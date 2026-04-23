<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\Caratula;
use App\Models\HistorialEstado;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Secuencia;
use App\Models\SecuenciaArchivoVersion;
use App\Models\SecuenciaComentario;
use App\Models\Unidad;
use App\Models\User;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use setasign\Fpdi\Fpdi;
use Smalot\PdfParser\Parser;
use Spatie\PdfToText\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
use thiagoalessio\TesseractOCR\TesseractOCR;

class SecuenciaController extends Controller
{
    public function index(Request $request)
    {
        $query = Secuencia::with('caratula');

        if ($request->filled('buscar')) {
            $query->whereHas('caratula', function ($q) use ($request) {
                $q->where('asignatura', 'like', '%' . $request->buscar . '%');
            });
        }

        if ($request->filled('estado')) {
            $query->where('estatus', $request->estado);
        }

        switch ($request->orden) {
            case 'nombre_asc':
                $query->join('caratulas', 'secuencias.caratula_id', '=', 'caratulas.id')
                    ->orderBy('caratulas.asignatura', 'asc');
                break;
            case 'nombre_desc':
                $query->join('caratulas', 'secuencias.caratula_id', '=', 'caratulas.id')
                    ->orderBy('caratulas.asignatura', 'desc');
                break;
            case 'activo':
                $query->orderBy('estatus', 'desc');
                break;
            default:
                $query->latest();
        }

        $secuencias = $query->paginate(10);
        $total = Secuencia::count();
        $activas = Secuencia::where('estatus', 'activo')->count();
        $inactivas = Secuencia::where('estatus', 'borrador')->count();
        $historialCambios = HistorialEstado::with('usuario')
            ->where('modulo', 'secuencias')
            ->latest('fecha_movimiento')
            ->take(10)
            ->get();

        return view('secuencias.index', [
            'secuencias' => $secuencias,
            'total' => $total,
            'activas' => $activas,
            'inactivas' => $inactivas,
            'historialCambios' => $historialCambios,
            'caratula' => new Caratula(),
            'unidades' => collect(),
            'secuencia' => null,
        ]);
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

    public function createView($id = null)
    {
        $secuencia = null;
        $caratula = new Caratula();
        $unidades = collect();

        if ($id !== null) {
            $secuencia = Secuencia::with([
                'caratula',
                'unidades',
                'docente',
                'materia',
                'carrera',
                'periodo',
                'director',
                'revisor',
                'comentarios.usuario',
                'comentarios.respuestaUsuario',
                'archivoVersiones.usuario',
            ])->findOrFail($id);

            $caratula = $secuencia->caratula ?? new Caratula();
            $unidades = $secuencia->unidades ?? collect();
        }

        $docentes = User::where('status', 1)->orderBy('name')->get();
        $materias = Materia::orderBy('nombre')->get();
        $carreras = Carrera::orderBy('nombre_carrera')->get();
        $periodos = Periodo::orderByDesc('anio')->orderBy('nombre')->get();

        return view('secuencias.createView', compact(
            'secuencia',
            'caratula',
            'unidades',
            'docentes',
            'materias',
            'carreras',
            'periodos'
        ));
    }

    public function create()
    {
        return $this->createView();
    }

    private function secuencias()
    {
        $secuencias = Secuencia::all();
        return $secuencias;
    }

    public function store(Request $request): RedirectResponse
    {
        $usesCatalogFields = $request->filled('docente_id')
            || $request->filled('materia_id')
            || $request->filled('carrera_id')
            || $request->filled('periodo_id');

        if ($usesCatalogFields) {
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
                'director_id' => $carrera->director_id,
            ];

            if (Schema::hasColumn('secuencias', 'status')) {
                $data['status'] = 1;
            }

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
                $this->registrarVersionArchivo(
                    $secuencia,
                    $archivoPath,
                    $archivoNombreOriginal,
                    $archivoMime,
                    $request->file('archivo_secuencia')?->getSize(),
                    'creacion'
                );
            }

            return redirect()
                ->route('secuencias.index')
                ->with('success', 'La secuencia se creo correctamente.');
        }

        $validated = $request->validate([
            'carrera' => 'required|string|max:255',
            'asignatura' => 'required|string|max:255',
            'competencia' => 'nullable|string',
            'cuatrimestre' => 'nullable|string|max:50',
            'unidades' => 'nullable|array',
        ]);

        $caratula = Caratula::create([
            'carrera' => $validated['carrera'],
            'docente' => null,
            'cuatrimestre' => $validated['cuatrimestre'] ?? null,
            'periodo_escolar' => null,
            'asignatura' => $validated['asignatura'],
            'grupo' => null,
            'competencia' => $validated['competencia'] ?? null,
            'tipo_competencia' => null,
            'creditos' => null,
            'modalidad' => null,
            'horas_saber' => null,
            'horas_saber_hacer' => null,
            'horas_totales' => null,
            'horas_semana' => null,
            'proposito' => null,
            'json_data' => json_encode($validated),
        ]);

        $secuencia = Secuencia::create([
            'caratula_id' => $caratula->id,
            'estatus' => 'borrador',
            'fecha_entrega' => now(),
        ]);

        if ($request->has('unidades')) {
            foreach ($request->unidades as $index => $u) {
                Unidad::create([
                    'secuencia_id' => $secuencia->id,
                    'nombre' => $u['titulo'] ?? '',
                    'numero' => $u['numero'] ?? (string) ($index + 1),
                    'horas' => $u['duracion'] ?? 0,
                ]);
            }
        }

        return redirect()
            ->route('secuencias.edit', $secuencia->id)
            ->with('success', 'Secuencia creada correctamente.');
    }

    public function uploadAndExtract(Request $request)
    {
        $request->validate([
            'caratula_file' => 'required|file|mimes:pdf|max:10240'
        ]);

        try {
            $uploadedFile = $request->file('caratula_file');

            $filename = time() . '_' . Str::slug(
                pathinfo(
                    $uploadedFile->getClientOriginalName(),
                    PATHINFO_FILENAME
                )
            ) . '.' . $uploadedFile->getClientOriginalExtension();

            $path = $uploadedFile->storeAs('public/secuencias/pdf', $filename);
            $fullPath = Storage::path($path);
            $relativePath = "secuencias/pdf/$filename";

            try {
                $text = Pdf::getText($fullPath);
                if (empty(trim($text))) {
                    throw new \Exception('Spatie returned empty');
                }
            } catch (\Throwable $e) {
                $parser = new Parser();
                $pdf = $parser->parseFile($fullPath);
                $text = $pdf->getText();
            }

            $textNormalized = preg_replace("/\r\n|\r|\n+/", "\n", $text);
            $textNormalized = preg_replace('/\s+\n/', "\n", $textNormalized);
            $textNormalized = preg_replace('/\n\s+/', "\n", $textNormalized);

            file_put_contents(storage_path('app/debug_text.txt'), $textNormalized);

            $programa = $this->extractPrograma($textNormalized);
            $unidades = $this->extractUnidades($textNormalized);

            $caratula = Caratula::create([
                'carrera' => $programa['programa_educativo'] ?? null,
                'docente' => null,
                'cuatrimestre' => $programa['cuatrimestre'] ?? null,
                'periodo_escolar' => null,
                'asignatura' => $programa['asignatura'] ?? null,
                'grupo' => null,
                'competencia' => $programa['competencia'] ?? null,
                'tipo_competencia' => $programa['tipo_compentencia'] ?? null,
                'creditos' => $programa['creditos'] ?? null,
                'modalidad' => $programa['modalidad'] ?? null,
                'horas_saber' => $programa['horas_saber'] ?? null,
                'horas_saber_hacer' => $programa['horas_hcaer'] ?? null,
                'horas_totales' => $programa['horas_totales'] ?? null,
                'horas_semana' => $programa['horas_semana'] ?? null,
                'proposito' => $programa['proposito'] ?? null,
                'json_data' => json_encode($programa)
            ]);

            $secuencia = Secuencia::create([
                'ruta_pdf' => $relativePath,
                'caratula_id' => $caratula->id,
                'docente_id' => null,
                'materia_id' => null,
                'carrera_id' => null,
                'periodo_id' => null,
                'revisor_id' => null,
                'director_id' => null,
                'tutor_id' => null,
                'estatus' => 'borrador',
                'fecha_entrega' => now()
            ]);

            foreach ($unidades as $u) {
                Unidad::create([
                    'secuencia_id' => $secuencia->id,
                    'nombre' => $u['nombre'] ?? null,
                    'numero' => $u['numero'] ?? null,
                    'horas' => $u['horas_totales'] ?? null
                ]);
            }

            $programa['ruta_pdf'] = $relativePath;

            return redirect()->route('secuencias.edit', ['id' => $secuencia->id]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function extractPrograma($text)
    {
        $out = [];

        $clean = preg_replace('/[ ]{2,}/', ' ', $text);
        $clean = preg_replace("/\r|\t/", "", $clean);
        $clean = preg_replace("/\n{2,}/", "\n", $clean);

        if (preg_match('/PROGRAMA\s+EDUCATIVO:?\s*(.+?)\s*EN\s+COMPETENCIAS\s+PROFESIONALES/si', $clean, $m)) {
            $out['programa_educativo'] = $this->normalizeMultiline($m[1]);
        }

        if (preg_match('/PROGRAMA DE ASIGNATURA:\s*(.*?)\s*CLAVE/i', $clean, $m)) {
            $out['asignatura'] = trim($m[1]);
        }

        if (preg_match('/CLAVE:\s*([A-Z0-9\-]+)/i', $clean, $m)) {
            $out['clave'] = trim($m[1]);
        }

        if (preg_match('/Prop[oó]sito de aprendizaje de la\nAsignatura.*?\n(.+?)\nCompetencia/s', $clean, $m)) {
            $out['proposito'] = $this->cleanBlock($m[1]);
        }

        if (preg_match('/Competencia a la que\ncontribuye la asignatura.*?\n(.+?)\nTipo de/s', $clean, $m)) {
            $out['competencia'] = $this->cleanBlock($m[1]);
        }

        if (preg_match('/Tipo de\s+competencia\s*\n.*\n([^\n]+)/i', $clean, $m)) {
            $line = trim($m[1]);
            if (preg_match('/\b(Base|Transversal|Espec[ií]fica)\b/i', $line, $m2)) {
                $out['tipo_compentencia'] = $m2[1];
            }
        }

        $clean = preg_replace('/\s+/u', ' ', $clean);

        if (preg_match('/(\d+)\s+(\d+[.,]?\d*)\s+([A-Za-zÁÉÍÓÚáéíóú]+)\s+(\d+)\s+(\d+)/u', $clean, $m)) {
            $out['cuatrimestre'] = (int) $m[1];
            $out['creditos'] = (float) $m[2];
            $out['modalidad'] = $m[3];
            $out['horas_semana'] = (int) $m[4];
            $out['horas_totales'] = (int) $m[5];
        }

        if (preg_match('/Totales\s+(\d+)\s+(\d+)\s+(\d+)/i', $text, $m)) {
            $out['horas_saber'] = (int) $m[1];
            $out['horas_hcaer'] = (int) $m[2];
        }

        return $out;
    }

    private function extractUnidades($text)
    {
        $units = [];

        preg_match_all('/Unidad de Aprendizaje\s+([IVX]+)\.\s*(.+?)\nProp[oó]sito Esperado([\s\S]*?)(?=Unidad de Aprendizaje|$)/i', $text, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $units[] = [
                'numero' => trim($m[1]),
                'nombre' => trim($m[2]),
                'proposito' => $this->extractBetween($m[3], 'Propósito esperado', 'Tiempo Asignado'),
                'horas_saber' => $this->findNumberNear($m[3], 'Horas del\s*Saber'),
                'horas_hacer' => $this->findNumberNear($m[3], 'Horas del\s*Saber Hacer'),
                'horas_totales' => $this->findNumberNear($m[3], 'Horas Totales'),
                'temas' => $this->extractTemasFromBlock($m[3]),
            ];
        }

        return $units;
    }

    private function extractTemasFromBlock($block)
    {
        $temas = [];

        if (preg_match_all('/([A-ZÁÉÍÓÚÑa-z0-9\-\s]+)\nDimensión Conceptual([\s\S]*?)Dimensión\s*Actuacional([\s\S]*?)Dimensión\s*Socioafectiva([\s\S]*?)\n\n/i', $block, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $temas[] = [
                    'tema' => trim($m[1]),
                    'dimension_concep' => trim($m[2]),
                    'dimension_actu' => trim($m[3]),
                    'dimension_socio' => trim($m[4]),
                ];
            }
        }

        return $temas;
    }

    private function normalizeMultiline($text)
    {
        return trim(preg_replace('/\s+/u', ' ', trim($text)));
    }

    private function cleanBlock($text)
    {
        $text = preg_replace("/\n/", " ", $text);
        $text = preg_replace("/\s{2,}/", " ", $text);
        return trim($text);
    }

    private function sliceNear($text, $needle, $chars = 200)
    {
        $pos = strpos($text, $needle);
        if ($pos === false) {
            return substr($text, 0, $chars);
        }
        $start = max(0, $pos - $chars);
        return substr($text, $start, $chars * 2);
    }

    private function extractBetween($text, $start, $end)
    {
        if (preg_match('/' . preg_quote($start, '/') . '\s*(.*?)\s*' . preg_quote($end, '/') . '/is', $text, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    private function findNumberNear($text, $label)
    {
        if (preg_match('/' . preg_quote($label, '/') . '.{0,30}?(\d+)/i', $text, $m)) {
            return (int) $m[1];
        }
        return null;
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'estatus' => 'required'
        ]);

        $secuencia = Secuencia::findOrFail($id);
        $secuencia->estatus = $request->estatus;
        $secuencia->save();

        return redirect()
            ->route('secuencias.index')
            ->with('success', 'Estatus actualizado');
    }

    public function verArchivo(Secuencia $secuencia): BinaryFileResponse
    {
        $this->authorizeSecuenciaAccess($secuencia);

        if (! $secuencia->archivo_path) {
            abort(404, 'La secuencia no tiene archivo asignado.');
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($secuencia->archivo_path)) {
            throw new FileNotFoundException('No se encontro el archivo asociado a la secuencia.');
        }

        return response()->file($disk->path($secuencia->archivo_path));
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

        SecuenciaComentario::create([
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
        $isSuperUser = $user->isSuperUser();
        $isAdmin = $isSuperUser || in_array(1, $roleIds, true);
        $isReviewer = $isSuperUser || (in_array(3, $roleIds, true) && (int) $secuencia->revisor_id === (int) $user->id);
        $isDocente = $isSuperUser || (in_array(4, $roleIds, true) && (int) $secuencia->docente_id === (int) $user->id);

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

        $validated = $request->validate([
            'estatus' => 'required|in:pendiente,reabierto,resuelto',
        ]);

        if ($user->isSuperUser()) {
            $comentario->update([
                'estatus' => $validated['estatus'],
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Estado actualizado correctamente',
                    'estatus' => $comentario->estatus,
                ]);
            }

            return back()->with('success', 'Estado del comentario actualizado correctamente.');
        }

        $roleIds = $this->getRoleIds($user);
        $isAdmin = in_array(1, $roleIds, true);
        $isReviewer = in_array(3, $roleIds, true) && (int) $secuencia->revisor_id === (int) $user->id;

        if (! $isAdmin && ! $isReviewer) {
            abort(403, 'Solo el revisor asignado puede actualizar el estado de la observacion.');
        }

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

        $estadoAnterior = (int) ($secuencia->status ?? 0);
        $estadoNuevo = (int) $validated['status'];

        if ($estadoAnterior === $estadoNuevo) {
            return back()->with('success', 'El estado de la secuencia ya estaba actualizado.');
        }

        $payload = [];

        if (Schema::hasColumn('secuencias', 'status')) {
            $payload['status'] = $estadoNuevo;
        }

        if (Schema::hasColumn('secuencias', 'estatus')) {
            $payload['estatus'] = $estadoNuevo === 1 ? 'activo' : 'inactivo';
        }

        if ($payload) {
            $secuencia->update($payload);
        }

        $this->registrarHistorial($secuencia, $estadoAnterior, $estadoNuevo, $validated['motivo']);

        $mensaje = $estadoNuevo === 1
            ? 'La secuencia fue reactivada correctamente.'
            : 'La secuencia fue desactivada correctamente.';

        return back()->with('success', $mensaje);
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
        $isSuperUser = $user->isSuperUser();
        $isAdmin = $isSuperUser || in_array(1, $roleIds, true);
        $isReviewer = $isSuperUser || in_array(3, $roleIds, true);

        if (! $isAdmin && ! $isReviewer) {
            abort(403, 'No tienes permisos para actualizar el estatus academico.');
        }

        if (! $isSuperUser && $isReviewer && (int) $secuencia->revisor_id !== (int) $user->id) {
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

        return back()->with('success', 'Estatus academico actualizado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $secuencia = Secuencia::findOrFail($id);

        $caratula = Caratula::find($secuencia->caratula_id) ?? new Caratula();

        $caratula->fill([
            'carrera' => $request->carrera,
            'asignatura' => $request->asignatura,
            'competencia' => $request->competencia,
            'cuatrimestre' => $request->cuatrimestre,
        ]);
        $caratula->save();

        if (! $secuencia->caratula_id) {
            $secuencia->caratula_id = $caratula->id;
            $secuencia->save();
        }

        if ($request->has('unidades')) {
            foreach ($request->unidades as $u) {
                if (isset($u['id'])) {
                    Unidad::where('id', $u['id'])->update([
                        'nombre' => $u['titulo'] ?? '',
                        'horas' => $u['duracion'] ?? 0,
                    ]);
                } else {
                    Unidad::create([
                        'secuencia_id' => $secuencia->id,
                        'nombre' => $u['titulo'] ?? '',
                        'numero' => $u['numero'] ?? 'NUEVA',
                        'horas' => $u['duracion'] ?? 0,
                    ]);
                }
            }
        }

        return redirect()
            ->route('secuencias.edit', $secuencia->id)
            ->with('success', 'Secuencia actualizada correctamente');
    }

    public function exportWord($id)
    {
        $secuencia = Secuencia::with([
            'caratula',
            'unidades'
        ])->findOrFail($id);

        $caratula = $secuencia->caratula;
        $unidades = $secuencia->unidades;

        $templatePath = storage_path('app/templates/plantilla_secuencia.docx');
        $template = new TemplateProcessor($templatePath);

        $template->setValue('carrera', $caratula->carrera ?? '');
        $template->setValue('cuatrimestre', $caratula->cuatrimestre ?? '');
        $template->setValue('asignatura', $caratula->asignatura ?? '');
        $template->setValue('proposito', $caratula->proposito ?? '');
        $template->setValue('competencia', $caratula->competencia ?? '');
        $template->setValue('tipo_competencia', $caratula->tipo_competencia ?? '');
        $template->setValue('creditos', $caratula->creditos ?? '');
        $template->setValue('modalidad', $caratula->modalidad ?? '');
        $template->setValue('horas_saber', $caratula->horas_saber ?? '');
        $template->setValue('horas_saber_hacer', $caratula->horas_saber_hacer ?? '');
        $template->setValue('horas_totales', $caratula->horas_totales ?? '');
        $template->setValue('horas_semana', $caratula->horas_semana ?? '');

        $this->fillUnidadBlock($template, $unidades);

        $fileName = 'secuencia_' . $secuencia->id . '.docx';
        $tempFile = storage_path('app/' . $fileName);

        $template->saveAs($tempFile);

        return response()->download(
            $tempFile,
            $fileName,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]
        )->deleteFileAfterSend(true);
    }

    private function fillUnidadBlock(TemplateProcessor $template, $unidades): void
    {
        $mainPart = $this->getTemplateMainPart($template);
        $pattern = '/<w:p\b(?:(?!<w:p\b).)*?\$\{unidad_block\}.*?<\/w:p>(.*?)<w:p\b(?:(?!<w:p\b).)*?\$\{\/unidad_block\}.*?<\/w:p>/s';
        $replacement = '';

        if ($unidades->count() > 0 && preg_match($pattern, $mainPart, $matches)) {
            $block = $matches[1];

            foreach ($unidades as $unidad) {
                $replacement .= str_replace(
                    ['${unidad_nombre}', '${unidad_horas}'],
                    [
                        $this->escapeWordXmlValue($this->formatUnidadNombre($unidad)),
                        $this->escapeWordXmlValue($unidad->horas ?? ''),
                    ],
                    $block
                );
            }
        }

        $mainPart = preg_replace_callback($pattern, fn () => $replacement, $mainPart, 1);
        $this->setTemplateMainPart($template, $mainPart);
    }

    private function formatUnidadNombre(Unidad $unidad): string
    {
        $numero = trim((string) ($unidad->numero ?? ''));
        $nombre = trim((string) ($unidad->nombre ?? ''));

        if ($numero === '') {
            return $nombre;
        }

        if ($nombre === '') {
            return $numero;
        }

        return $numero . '. ' . $nombre;
    }

    private function escapeWordXmlValue($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function getTemplateMainPart(TemplateProcessor $template): string
    {
        $property = new \ReflectionProperty(TemplateProcessor::class, 'tempDocumentMainPart');
        $property->setAccessible(true);

        return $property->getValue($template);
    }

    private function setTemplateMainPart(TemplateProcessor $template, string $mainPart): void
    {
        $property = new \ReflectionProperty(TemplateProcessor::class, 'tempDocumentMainPart');
        $property->setAccessible(true);
        $property->setValue($template, $mainPart);
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

        if ($user->isSuperUser()) {
            return;
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
