<?php

namespace App\Http\Controllers;

use App\Mail\ComentarioSecuenciaNotificationMail;
use App\Mail\DictamenRevisionMail;
use App\Mail\DictamenCorrecionesMail;
use App\Mail\DictamenAprobadaMail;
use App\Models\Carrera;
use App\Models\HistorialEstado;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Secuencia;
use App\Models\SecuenciaArchivoVersion;
use App\Models\SecuenciaComentario;
use App\Models\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
use thiagoalessio\TesseractOCR\TesseractOCR;

class SecuenciaController extends Controller
{
    public function index()
    {
        $secuencias = $this->secuencias();
        $historialCambios = HistorialEstado::with('usuario')
            ->where('modulo', 'secuencias')
            ->latest('fecha_movimiento')
            ->take(10)
            ->get();

        return view('secuencias.index', compact('secuencias', 'historialCambios'));
    }

    public function show(Secuencia $secuencia)
    {
        $this->authorizeSecuenciaAccess($secuencia);

        $secuencia->load([
            'docente',
            'materia',
            'carrera',
            'periodo',
            'director',
            'revisor',
            'comentarios.usuario',
            'comentarios.respuestaUsuario',
            'archivoVersiones.usuario',
        ]);

        $archivoUrl = $secuencia->archivo_path
            ? route('secuencias.verArchivo', $secuencia)
            : asset('docs/secuencia-didactica-uth.pdf');

        $mime = $secuencia->archivo_mime ?: 'application/pdf';
        $isPreviewable = str_contains($mime, 'pdf') || str_contains($mime, 'image/');

        return view('secuencias.show', compact('secuencia', 'archivoUrl', 'isPreviewable', 'mime'));
    }

    public function editor(Secuencia $secuencia)
    {
        $this->authorizeSecuenciaAccess($secuencia);

        $secuencia->load(['docente', 'materia', 'carrera', 'periodo', 'revisor']);

        $archivoUrl = $secuencia->archivo_path
            ? route('secuencias.verArchivo', $secuencia)
            : asset('docs/secuencia-didactica-uth.pdf');

        $mime = strtolower((string) ($secuencia->archivo_mime ?? ''));
        $extension = strtolower((string) pathinfo((string) $secuencia->archivo_path, PATHINFO_EXTENSION));

        $pdfAvailable = ! $secuencia->archivo_path
            || str_contains($mime, 'pdf')
            || $extension === 'pdf';

        $pdfUrl = $pdfAvailable ? $archivoUrl : null;

        return view('secuencias.editor', compact('secuencia', 'archivoUrl', 'pdfUrl', 'pdfAvailable'));
    }

    public function ocrArchivo(Secuencia $secuencia)
    {
        $this->authorizeSecuenciaAccess($secuencia);

        $ocrDependencyError = $this->getOcrDependencyError();

        if ($ocrDependencyError) {
            return response()->json([
                'success' => false,
                'message' => $ocrDependencyError,
            ], 500);
        }

        $sourcePath = $this->resolveSourcePdfPath($secuencia);

        if (! $sourcePath) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontro un PDF para OCR en esta secuencia.',
            ], 404);
        }

        try {
            $text = $this->runOcrFromFile($sourcePath);

            $data = $this->parseCaratulaText($text);

            return response()->json([
                'success' => true,
                'message' => 'OCR ejecutado correctamente.',
                'extracted_data' => $data,
                'raw_excerpt' => mb_substr(trim(preg_replace('/\s+/', ' ', $text)), 0, 1200),
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => 'No fue posible procesar OCR para este archivo. Detalle: ' . $exception->getMessage(),
            ], 500);
        }
    }

    public function createView()
    {
        $carreras = Carrera::where('status', 1)
            ->orderBy('nombre_carrera')
            ->get(['id', 'nombre_carrera']);

        $materias = Materia::orderBy('nombre')
            ->get(['id', 'nombre']);

        $periodos = Periodo::orderByDesc('anio')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'anio']);

        $docentes = User::with('roles')
            ->whereHas('roles', function ($q) {
                $q->where('roles.id', 4);
            })
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'apellido_paterno', 'apellido_materno', 'email']);

        return view('secuencias.createView', compact('carreras', 'materias', 'periodos', 'docentes'));
    }

    private function secuencias()
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return collect();
        }

        $roleIds = $this->getRoleIds($user);

        $query = Secuencia::with(['docente', 'materia', 'carrera', 'periodo', 'director', 'revisor'])
            ->orderByDesc('status')
            ->latest();

        if (in_array(2, $roleIds, true)) {
            $query->whereHas('carrera', function ($q) use ($user) {
                $q->where('director_id', $user->id);
            });
        }

        if (in_array(3, $roleIds, true)) {
            $query->where('revisor_id', $user->id);
        }

        if (in_array(4, $roleIds, true)) {
            $query->where('docente_id', $user->id);
        }

        return $query->get();
    }

    public function actualizarEstatusAcademico(Request $request, Secuencia $secuencia): RedirectResponse
    {
        $validated = $request->validate([
            'estatus' => 'required|in:revision,correcciones,aprobada',
            'motivo' => 'required|string|min:5|max:500',
        ]);

        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        $roleIds = $this->getRoleIds($user);
        $isAdmin = in_array(1, $roleIds, true);
        $isReviewer = in_array(3, $roleIds, true);

        if (! $isAdmin && ! $isReviewer) {
            abort(403, 'No tienes permisos para actualizar el estatus academico.');
        }

        if ($isReviewer && (int) $secuencia->revisor_id !== (int) $user->id) {
            abort(403, 'Solo puedes actualizar secuencias asignadas a tu revision.');
        }

        $estatusAnterior = $secuencia->estatus;
        $estatusNuevo = $validated['estatus'];

        if ($estatusAnterior === $estatusNuevo) {
            return back()->with('success', 'El estatus academico ya se encontraba actualizado.');
        }

        $secuencia->update([
            'estatus' => $estatusNuevo,
        ]);

        $this->registrarHistorialEstatusAcademico($secuencia, $estatusAnterior, $estatusNuevo, $validated['motivo']);

        // ✅ ENVIAR NOTIFICACIÓN POR CORREO AL DOCENTE SEGÚN EL DICTAMEN
        try {
            $secuencia->load('docente');
            
            if ($secuencia->docente) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($secuencia, $user, $estatusNuevo, $validated) {
                    
                    // Enviar correo según el estado
                    match($estatusNuevo) {
                        'revision' => \Illuminate\Support\Facades\Mail::mailer('resend')
                            ->to($secuencia->docente->email)
                            ->send(new DictamenRevisionMail(
                                $secuencia,
                                $secuencia->docente,
                                $user,
                                $validated['motivo']
                            )),
                        
                        'correcciones' => \Illuminate\Support\Facades\Mail::mailer('resend')
                            ->to($secuencia->docente->email)
                            ->send(new DictamenCorrecionesMail(
                                $secuencia,
                                $secuencia->docente,
                                $user,
                                $validated['motivo'],
                                $secuencia->comentarios()
                                    ->where('estatus', 'pendiente')
                                    ->get()
                            )),
                        
                        'aprobada' => \Illuminate\Support\Facades\Mail::mailer('resend')
                            ->to($secuencia->docente->email)
                            ->send(new DictamenAprobadaMail(
                                $secuencia,
                                $secuencia->docente,
                                $user,
                                $validated['motivo']
                            )),
                        
                        default => null
                    };
                });
            }
        } catch (\Exception $e) {
            // 📝 Log del error pero no afecta la actualización del estatus
            \Illuminate\Support\Facades\Log::error('Error al enviar notificación de dictamen: ' . $e->getMessage(), [
                'secuencia_id' => $secuencia->id,
                'estatus' => $estatusNuevo,
                'error' => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Estatus academico actualizado correctamente.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'docente_id' => 'required|exists:users,id',
            'materia_id' => 'required|exists:materias,id',
            'carrera_id' => 'required|exists:carreras,id',
            'periodo_id' => 'required|exists:periodos,id',
            'archivo_secuencia' => 'nullable|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:20480',
        ]);

        $carrera = Carrera::findOrFail($validated['carrera_id']);

        if (! $carrera->status) {
            return back()->with('error', 'No puedes crear secuencias en una carrera inactiva.');
        }

        $materia = Materia::find($validated['materia_id']);

        $archivoPath = null;
        $archivoNombreOriginal = null;
        $archivoMime = null;

        if ($request->hasFile('archivo_secuencia')) {
            $archivo = $request->file('archivo_secuencia');
            $archivoPath = $archivo->store('secuencias/archivos', 'public');
            $archivoNombreOriginal = $archivo->getClientOriginalName();
            $archivoMime = $archivo->getMimeType();
        }

        $data = [
            'docente_id' => $validated['docente_id'],
            'materia_id' => $validated['materia_id'],
            'carrera_id' => $validated['carrera_id'],
            'periodo_id' => $validated['periodo_id'],
            'estatus' => 'elaboracion',
            'status' => 1,
            'director_id' => $carrera->director_id,
        ];

        if (Schema::hasColumn('secuencias', 'horas_programadas')) {
            $data['horas_programadas'] = $materia?->horas_totales;
        }

        if (Schema::hasColumn('secuencias', 'fecha_entrega')) {
            $data['fecha_entrega'] = now();
        }

        if (Schema::hasColumn('secuencias', 'archivo_path')) {
            $data['archivo_path'] = $archivoPath;
        }

        if (Schema::hasColumn('secuencias', 'archivo_nombre_original')) {
            $data['archivo_nombre_original'] = $archivoNombreOriginal;
        }

        if (Schema::hasColumn('secuencias', 'archivo_mime')) {
            $data['archivo_mime'] = $archivoMime;
        }

        $secuencia = Secuencia::create($data);

        if ($archivoPath) {
            $this->registrarVersionArchivo($secuencia, $archivoPath, $archivoNombreOriginal, $archivoMime, $request->file('archivo_secuencia')?->getSize(), 'creacion');
        }

        return redirect()
            ->route('secuencias.index')
            ->with('success', 'La secuencia se creó correctamente.');
    }

    public function verArchivo(Secuencia $secuencia): BinaryFileResponse
    {
        $this->authorizeSecuenciaAccess($secuencia);

        if (! $secuencia->archivo_path) {
            abort(404, 'La secuencia no tiene archivo asignado.');
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($secuencia->archivo_path)) {
            throw new FileNotFoundException('No se encontró el archivo asociado a la secuencia.');
        }

        $path = $disk->path($secuencia->archivo_path);

        return response()->file($path);
    }

    public function actualizarArchivo(Request $request, Secuencia $secuencia): RedirectResponse
    {
        $this->authorizeSecuenciaAccess($secuencia);

        $request->validate([
            'archivo_secuencia' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:20480',
        ]);

        $archivo = $request->file('archivo_secuencia');
        $nuevoPath = $archivo->store('secuencias/archivos', 'public');

        $payload = [];

        if (Schema::hasColumn('secuencias', 'archivo_path')) {
            $payload['archivo_path'] = $nuevoPath;
        }

        if (Schema::hasColumn('secuencias', 'archivo_nombre_original')) {
            $payload['archivo_nombre_original'] = $archivo->getClientOriginalName();
        }

        if (Schema::hasColumn('secuencias', 'archivo_mime')) {
            $payload['archivo_mime'] = $archivo->getMimeType();
        }

        $secuencia->update($payload);

        $this->registrarVersionArchivo(
            $secuencia,
            $nuevoPath,
            $archivo->getClientOriginalName(),
            $archivo->getMimeType(),
            $archivo->getSize(),
            'actualizacion'
        );

        return back()->with('success', 'Archivo de la secuencia actualizado correctamente.');
    }

    public function verArchivoVersion(Secuencia $secuencia, SecuenciaArchivoVersion $version): BinaryFileResponse
    {
        $this->authorizeSecuenciaAccess($secuencia);

        if ((int) $version->secuencia_id !== (int) $secuencia->id) {
            abort(404, 'La version no corresponde a la secuencia solicitada.');
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($version->archivo_path)) {
            throw new FileNotFoundException('No se encontro el archivo de la version solicitada.');
        }

        return response()->file($disk->path($version->archivo_path));
    }

    public function anotarArchivo(Request $request, Secuencia $secuencia): RedirectResponse
    {
        $this->authorizeSecuenciaAccess($secuencia);

        $validated = $request->validate([
            'page' => 'required|integer|min:1|max:500',
            'x' => 'required|numeric|min:0|max:300',
            'y' => 'required|numeric|min:0|max:300',
            'width' => 'required|numeric|min:10|max:300',
            'height' => 'required|numeric|min:10|max:300',
            'titulo' => 'nullable|string|max:120',
            'info' => 'nullable|string|max:300',
            'comentario' => 'required|string|min:2|max:1500',
            'usar_ocr' => 'nullable|boolean',
        ]);

        $sourcePath = $this->resolveSourcePdfPath($secuencia);

        if (! $sourcePath) {
            return back()->with('error', 'No se encontro un archivo PDF para editar.');
        }

        $comentario = trim($validated['comentario']);

        if ((bool) ($validated['usar_ocr'] ?? false)) {
            $ocrText = $this->extractOcrTextFromFile($sourcePath);

            if ($ocrText !== '') {
                $comentario .= "\n\nOCR:\n" . $ocrText;
            }
        }

        $nuevoPath = $this->buildAnnotatedPdf(
            sourcePdfPath: $sourcePath,
            targetPage: (int) $validated['page'],
            x: (float) $validated['x'],
            y: (float) $validated['y'],
            width: (float) $validated['width'],
            height: (float) $validated['height'],
            titulo: trim((string) ($validated['titulo'] ?? '')),
            info: trim((string) ($validated['info'] ?? '')),
            comentario: $comentario
        );

        if (! $nuevoPath) {
            return back()->with('error', 'No se pudo generar el PDF anotado. Verifica que el archivo sea PDF valido.');
        }

        $payload = [];

        if (Schema::hasColumn('secuencias', 'archivo_path')) {
            $payload['archivo_path'] = $nuevoPath;
        }

        if (Schema::hasColumn('secuencias', 'archivo_nombre_original')) {
            $payload['archivo_nombre_original'] = 'secuencia-anotada-' . $secuencia->id . '.pdf';
        }

        if (Schema::hasColumn('secuencias', 'archivo_mime')) {
            $payload['archivo_mime'] = 'application/pdf';
        }

        $secuencia->update($payload);

        $fullAnnotatedPath = Storage::disk('public')->path($nuevoPath);

        $this->registrarVersionArchivo(
            $secuencia,
            $nuevoPath,
            'secuencia-anotada-' . $secuencia->id . '.pdf',
            'application/pdf',
            @filesize($fullAnnotatedPath) ?: null,
            'anotacion'
        );

        return back()->with('success', 'Se genero una nueva version anotada del PDF.');
    }

    public function guardarComentario(Request $request, Secuencia $secuencia): RedirectResponse
    {
        $this->authorizeSecuenciaAccess($secuencia);

        $validated = $request->validate([
            'coord_mode' => 'required|in:percent',
            'page' => 'required|integer|min:1|max:500',
            'x' => 'required|numeric|min:0|max:100',
            'y' => 'required|numeric|min:0|max:100',
            'width' => 'required|numeric|min:0.5|max:100',
            'height' => 'required|numeric|min:0.5|max:100',
            'titulo' => 'nullable|string|max:120',
            'info' => 'nullable|string|max:300',
            'texto_seleccionado' => 'nullable|string|max:5000',
            'comentario' => 'required|string|min:5|max:1500',
        ]);

        $comentario = SecuenciaComentario::create([
            'secuencia_id' => $secuencia->id,
            'user_id' => Auth::id(),
            'coord_mode' => $validated['coord_mode'],
            'page' => (int) $validated['page'],
            'x' => (float) $validated['x'],
            'y' => (float) $validated['y'],
            'width' => (float) $validated['width'],
            'height' => (float) $validated['height'],
            'titulo' => trim((string) ($validated['titulo'] ?? '')),
            'info' => trim((string) ($validated['info'] ?? '')),
            'texto_seleccionado' => trim((string) ($validated['texto_seleccionado'] ?? '')),
            'comentario' => $validated['comentario'],
            'estatus' => 'pendiente',
        ]);

        // ✅ ENVIAR NOTIFICACIÓN POR CORREO AL DOCENTE (USANDO RESEND CON EMAIL DE PRUEBA)
        try {
            $secuencia->load('docente');
            $usuario = Auth::user();

            if ($secuencia->docente && $usuario) {
                // 🔒 Usar transacción para envío seguro de email
                \Illuminate\Support\Facades\DB::transaction(function () use ($comentario, $secuencia, $usuario) {
                    // Enviar desde onboarding@resend.dev (email de prueba de Resend)
                    \Illuminate\Support\Facades\Mail::mailer('resend')
                        ->to($secuencia->docente->email)  // 📧 Enviar AL docente
                        ->send(
                            new ComentarioSecuenciaNotificationMail(
                                $comentario,
                                $secuencia->docente,
                                $usuario
                            )
                        );
                });
            }
        } catch (\Exception $e) {
            // 📝 Log del error pero no afecta la creación del comentario
            \Illuminate\Support\Facades\Log::error('Error al enviar notificación de comentario: ' . $e->getMessage());
        }

        return back()->with('success', 'Comentario registrado correctamente.');
    }

    public function responderComentario(Request $request, Secuencia $secuencia, SecuenciaComentario $comentario): RedirectResponse
    {
        $this->authorizeSecuenciaAccess($secuencia);

        if ((int) $comentario->secuencia_id !== (int) $secuencia->id) {
            abort(404);
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        $roleIds = $this->getRoleIds($user);
        $isAdmin = in_array(1, $roleIds, true);
        $isReviewer = in_array(3, $roleIds, true) && (int) $secuencia->revisor_id === (int) $user->id;
        $isDocente = in_array(4, $roleIds, true) && (int) $secuencia->docente_id === (int) $user->id;

        if (! $isAdmin && ! $isReviewer && ! $isDocente) {
            abort(403, 'No tienes permisos para responder comentarios en esta secuencia.');
        }

        $validated = $request->validate([
            'respuesta' => 'required|string|min:2|max:1500',
            'estatus' => 'nullable|in:pendiente,reabierto,resuelto',
        ]);

        $nuevoEstatus = $comentario->estatus;

        if ($isReviewer || $isAdmin) {
            $nuevoEstatus = $validated['estatus'] ?? $comentario->estatus;
        } elseif ($isDocente) {
            // El docente notifica correccion y deja la observacion para nueva revision.
            $nuevoEstatus = 'pendiente';
        }

        $comentario->update([
            'respuesta' => $validated['respuesta'],
            'estatus' => $nuevoEstatus,
            'respuesta_user_id' => $user->id,
        ]);

        return back()->with('success', 'Respuesta del comentario guardada correctamente.');
    }

    public function actualizarEstadoComentario(Request $request, Secuencia $secuencia, SecuenciaComentario $comentario)
    {
        $this->authorizeSecuenciaAccess($secuencia);

        if ((int) $comentario->secuencia_id !== (int) $secuencia->id) {
            abort(404);
        }

        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        $roleIds = $this->getRoleIds($user);
        $isAdmin = in_array(1, $roleIds, true);
        $isReviewer = in_array(3, $roleIds, true) && (int) $secuencia->revisor_id === (int) $user->id;

        if (! $isAdmin && ! $isReviewer) {
            abort(403, 'Solo el revisor asignado puede actualizar el estado de la observacion.');
        }

        $validated = $request->validate([
            'estatus' => 'required|in:pendiente,reabierto,resuelto',
        ]);

        $comentario->update([
            'estatus' => $validated['estatus'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'estatus' => $validated['estatus'],
            ]);
        }

        return back()->with('success', 'Estado del comentario actualizado correctamente.');
    }

    public function cambiarEstado(Request $request, Secuencia $secuencia): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:0,1',
            'motivo' => 'required|string|min:5|max:500',
        ]);

        $estadoAnterior = (int) $secuencia->status;
        $estadoNuevo = (int) $validated['status'];

        if ($estadoAnterior === $estadoNuevo) {
            return back()->with('success', 'El estado de la secuencia ya estaba actualizado.');
        }

        $secuencia->update(['status' => $estadoNuevo]);

        $this->registrarHistorial($secuencia, $estadoAnterior, $estadoNuevo, $validated['motivo']);

        $mensaje = $estadoNuevo === 1
            ? 'La secuencia fue reactivada correctamente.'
            : 'La secuencia fue desactivada correctamente.';

        return back()->with('success', $mensaje);
    }

    public function uploadAndExtract(Request $request)
    {
        $request->validate(['caratula_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240']);

        $ocrDependencyError = $this->getOcrDependencyError();

        if ($ocrDependencyError) {
            return response()->json([
                'success' => false,
                'message' => $ocrDependencyError,
            ], 500);
        }

        try {
            $path = $request->file('caratula_file')->store('temp/caratulas');
            $fullPath = Storage::path($path);

            $text = $this->runOcrFromFile($fullPath);

            $data = $this->parseCaratulaText($text);

            Storage::delete($path);

            return response()->json([
                'success' => true,
                'message' => 'Datos extraídos correctamente.',
                'extracted_data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo. ' . $e->getMessage(),
            ], 500);
        }
    }

    private function parseCaratulaText(string $text): array
    {
        $lines = explode("\n", $text);
        $data = [];
        $rawText = $text;

        $patterns = [
            'carrera' => '/Carrera:\s*(.*)/i',
            'asignatura' => '/Asignatura:\s*(.*)/i',
            'docente' => '/Docente:\s*(.*)/i',
            'tutor' => '/Tutor\s*\(s\)\s*de\s*grupo:\s*(.*)/i',
            'link' => '/Link\s*en\s*Internet:\s*(.*)/i',
            'email' => '/e-mail\s*:\s*(.*)/i',
        ];

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/(O|A)?\s*(Enero-Abril|Mayo-Agosto|Septiembre-Diciembre)\s*(\d{4})\s*Cuatrimestre:\s*(\d+)\s*Grupo:\s*(\w+)/i', $line, $matches)) {
                $data['periodo_texto'] = trim($matches[2]);
                $data['anio'] = (int) $matches[3];
                $data['cuatrimestre'] = (int) $matches[4];
                $data['grupo'] = trim($matches[5]);
            }

            foreach ($patterns as $key => $pattern) {
                if (preg_match($pattern, $line, $matches)) {
                    $data[$key] = trim($matches[1], " \t\n\r\0\x0B:");
                }
            }
        }

        $startTag = 'Competencia(s):';
        $endTagPattern = '/(Periodo:|Período:|Carrera:|Asignatura:)/i';

        if (stripos($rawText, $startTag) !== false) {
            $startPos = stripos($rawText, $startTag) + strlen($startTag);
            $subText = substr($rawText, $startPos);

            if (preg_match($endTagPattern, $subText, $matches, PREG_OFFSET_CAPTURE)) {
                $endPos = $matches[0][1];
                $competenciaRaw = substr($subText, 0, $endPos);
            } else {
                $competenciaRaw = $subText;
            }

            $data['competencia'] = trim(preg_replace('/\s\s+/', ' ', $competenciaRaw));
        }

        $periodoMap = [
            'Enero-Abril' => 1,
            'Mayo-Agosto' => 2,
            'Septiembre-Diciembre' => 3,
        ];

        $data['periodo_id'] = $periodoMap[$data['periodo_texto'] ?? ''] ?? null;
        $data['grupo'] = strtoupper(trim($data['grupo'] ?? ''));

        return $data;
    }

    private function registrarHistorial(Secuencia $secuencia, int $estadoAnterior, int $estadoNuevo, string $motivo): void
    {
        $nombre = $secuencia->materia?->nombre
            ? $secuencia->materia->nombre . ' - ' . ($secuencia->carrera?->nombre_carrera ?? 'Sin carrera')
            : 'Secuencia #' . $secuencia->id;

        HistorialEstado::create([
            'modulo' => 'secuencias',
            'registro_id' => $secuencia->id,
            'registro_nombre' => $nombre,
            'accion' => $estadoNuevo === 1 ? 'reactivado' : 'desactivado',
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'motivo' => $motivo,
            'user_id' => Auth::id(),
            'fecha_movimiento' => now(),
        ]);
    }

    private function registrarHistorialEstatusAcademico(Secuencia $secuencia, string $estatusAnterior, string $estatusNuevo, string $motivo): void
    {
        $nombre = $secuencia->materia?->nombre
            ? $secuencia->materia->nombre . ' - ' . ($secuencia->carrera?->nombre_carrera ?? 'Sin carrera')
            : 'Secuencia #' . $secuencia->id;

        $estadoMap = [
            'elaboracion' => 1,
            'pendiente' => 2,
            'revision' => 3,
            'correcciones' => 4,
            'entregada' => 5,
            'aprobada' => 6,
        ];

        HistorialEstado::create([
            'modulo' => 'secuencias',
            'registro_id' => $secuencia->id,
            'registro_nombre' => $nombre,
            'accion' => 'estatus_academico',
            'estado_anterior' => $estadoMap[$estatusAnterior] ?? null,
            'estado_nuevo' => $estadoMap[$estatusNuevo] ?? 0,
            'motivo' => $motivo,
            'user_id' => Auth::id(),
            'fecha_movimiento' => now(),
        ]);
    }

    private function authorizeSecuenciaAccess(Secuencia $secuencia): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403, 'No hay sesion valida para acceder a esta secuencia.');
        }

        $roleIds = $this->getRoleIds($user);

        if (in_array(1, $roleIds, true)) {
            return;
        }

        if (in_array(2, $roleIds, true) && (int) $secuencia->director_id === (int) $user->id) {
            return;
        }

        if (in_array(3, $roleIds, true) && (int) $secuencia->revisor_id === (int) $user->id) {
            return;
        }

        if (in_array(4, $roleIds, true) && (int) $secuencia->docente_id === (int) $user->id) {
            return;
        }

        abort(403, 'No tienes permisos para acceder a esta secuencia.');
    }

    private function getRoleIds(User $user): array
    {
        return $user->roles()
            ->pluck('roles.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function registrarVersionArchivo(
        Secuencia $secuencia,
        string $path,
        ?string $nombreOriginal,
        ?string $mime,
        ?int $size,
        string $accion
    ): void {
        SecuenciaArchivoVersion::create([
            'secuencia_id' => $secuencia->id,
            'user_id' => Auth::id(),
            'archivo_path' => $path,
            'archivo_nombre_original' => $nombreOriginal,
            'archivo_mime' => $mime,
            'archivo_size' => $size,
            'accion' => $accion,
        ]);
    }

    private function resolveSourcePdfPath(Secuencia $secuencia): ?string
    {
        $disk = Storage::disk('public');

        if ($secuencia->archivo_path && $disk->exists($secuencia->archivo_path)) {
            $candidate = $disk->path($secuencia->archivo_path);

            if (strtolower(pathinfo($candidate, PATHINFO_EXTENSION)) === 'pdf') {
                return $candidate;
            }
        }

        $fallback = public_path('docs/secuencia-didactica-uth.pdf');

        if (is_file($fallback)) {
            return $fallback;
        }

        return null;
    }

    private function buildAnnotatedPdf(
        string $sourcePdfPath,
        int $targetPage,
        float $x,
        float $y,
        float $width,
        float $height,
        string $titulo,
        string $info,
        string $comentario
    ): ?string {
        try {
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($sourcePdfPath);

            for ($i = 1; $i <= $pageCount; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $orientation = $size['width'] > $size['height'] ? 'L' : 'P';

                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);

                if ($i === $targetPage) {
                    $pdf->SetDrawColor(0, 75, 84);
                    $pdf->SetLineWidth(0.5);
                    $pdf->Rect($x, $y, $width, $height);

                    $cursorY = $y + 2;

                    if ($titulo !== '') {
                        $pdf->SetFont('Arial', 'B', 10);
                        $pdf->SetXY($x + 2, $cursorY);
                        $pdf->MultiCell($width - 4, 5, utf8_decode('Titulo: ' . $titulo));
                        $cursorY = $pdf->GetY() + 1;
                    }

                    if ($info !== '') {
                        $pdf->SetFont('Arial', '', 9);
                        $pdf->SetXY($x + 2, $cursorY);
                        $pdf->MultiCell($width - 4, 4.5, utf8_decode('Info: ' . $info));
                        $cursorY = $pdf->GetY() + 1;
                    }

                    $pdf->SetFont('Arial', '', 9);
                    $pdf->SetXY($x + 2, $cursorY);
                    $pdf->MultiCell($width - 4, 4.5, utf8_decode('Comentario: ' . $comentario));
                }
            }

            $filename = 'secuencias/archivos/anotada_' . now()->format('Ymd_His') . '_' . uniqid() . '.pdf';
            $fullPath = Storage::disk('public')->path($filename);

            $dir = dirname($fullPath);

            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $pdf->Output('F', $fullPath);

            return $filename;
        } catch (\Throwable $exception) {
            report($exception);
            return null;
        }
    }

    private function extractOcrTextFromFile(string $path): string
    {
        if ($this->getOcrDependencyError()) {
            return '';
        }

        try {
            $text = $this->runOcrFromFile($path);

            $normalized = trim(preg_replace('/\s+/', ' ', $text ?? ''));

            return mb_substr($normalized, 0, 650);
        } catch (\Throwable $exception) {
            return '';
        }
    }

    private function runOcrFromFile(string $path): string
    {
        $ocrInput = $path;
        $tempImagePath = null;

        try {
            if ($this->isPdfPath($path)) {
                $ocrInput = $this->convertPdfFirstPageToImage($path);
                $tempImagePath = $ocrInput;
            }

            return (new TesseractOCR($ocrInput))
                ->lang('spa')
                ->run();
        } finally {
            if ($tempImagePath && is_file($tempImagePath)) {
                @unlink($tempImagePath);
            }
        }
    }

    private function convertPdfFirstPageToImage(string $pdfPath): string
    {
        $binary = $this->resolvePdftoppmBinary();

        if (! $binary) {
            throw new \RuntimeException('No se encontro la utilidad pdftoppm en PATH. Instala Poppler y reinicia el servidor web.');
        }

        $tempBase = tempnam(sys_get_temp_dir(), 'ocr_pdf_');

        if ($tempBase === false) {
            throw new \RuntimeException('No se pudo crear un archivo temporal para OCR.');
        }

        @unlink($tempBase);

        $process = new Process([
            $binary,
            '-png',
            '-f',
            '1',
            '-singlefile',
            $pdfPath,
            $tempBase,
        ]);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException('No fue posible convertir el PDF a imagen para OCR. Detalle: ' . trim($process->getErrorOutput() ?: $process->getOutput()));
        }

        $imagePath = $tempBase . '.png';

        if (! is_file($imagePath)) {
            throw new \RuntimeException('La conversion de PDF a imagen no genero un archivo utilizable.');
        }

        return $imagePath;
    }

    private function resolvePdftoppmBinary(): ?string
    {
        $configured = env('PDFTOPPM_BIN');

        if (is_string($configured) && $configured !== '' && is_file($configured)) {
            return $configured;
        }

        if ($this->commandExists('pdftoppm')) {
            return 'pdftoppm';
        }

        return null;
    }

    private function isPdfPath(string $path): bool
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';
    }

    private function commandExists(string $command): bool
    {
        $lookup = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
            ? 'where ' . $command
            : 'which ' . $command;

        $output = [];
        $statusCode = 1;

        @exec($lookup, $output, $statusCode);

        return $statusCode === 0 && ! empty($output);
    }

    private function getOcrDependencyError(): ?string
    {
        if (! class_exists(TesseractOCR::class)) {
            return 'Falta la libreria PHP thiagoalessio/tesseract_ocr. Ejecuta composer require thiagoalessio/tesseract_ocr:^2.13';
        }

        $command = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'where tesseract' : 'which tesseract';
        $output = [];
        $statusCode = 1;

        @exec($command, $output, $statusCode);

        if ($statusCode !== 0 || empty($output)) {
            return 'No se encontro Tesseract OCR instalado en el sistema o en PATH. Instala Tesseract y reinicia terminal/servidor.';
        }

        return null;
    }
}
