<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\HistorialEstado;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Secuencia;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Thiagoalessio\TesseractOCR\TesseractOCR;

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
        $roleId = $user->roles()->pluck('roles.id')->first();

        $query = Secuencia::with(['docente', 'materia', 'carrera', 'periodo', 'director', 'revisor'])
            ->orderByDesc('status')
            ->latest();

        if ((int) $roleId === 2) {
            $query->whereHas('carrera', function ($q) use ($user) {
                $q->where('director_id', $user->id);
            });
        }

        if ((int) $roleId === 4) {
            $query->where('docente_id', $user->id);
        }

        return $query->get();
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'docente_id' => 'required|exists:users,id',
            'materia_id' => 'required|exists:materias,id',
            'carrera_id' => 'required|exists:carreras,id',
            'periodo_id' => 'required|exists:periodos,id',
        ]);

        $carrera = Carrera::findOrFail($validated['carrera_id']);

        if (! $carrera->status) {
            return back()->with('error', 'No puedes crear secuencias en una carrera inactiva.');
        }

        $materia = Materia::find($validated['materia_id']);

        Secuencia::create([
            'docente_id' => $validated['docente_id'],
            'materia_id' => $validated['materia_id'],
            'carrera_id' => $validated['carrera_id'],
            'periodo_id' => $validated['periodo_id'],
            'estatus' => 'elaboracion',
            'status' => 1,
            'director_id' => $carrera->director_id,
            'horas_programadas' => $materia?->horas_totales,
            'fecha_entrega' => now(),
        ]);

        return redirect()
            ->route('secuencias.index')
            ->with('success', 'La secuencia se creó correctamente.');
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

        try {
            $path = $request->file('caratula_file')->store('temp/caratulas');
            $fullPath = Storage::path($path);

            $text = (new TesseractOCR($fullPath))
                ->lang('spa')
                ->run();

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
}
