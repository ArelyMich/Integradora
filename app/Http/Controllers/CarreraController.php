<?php

namespace App\Http\Controllers;

use App\Models\Carrera;
use App\Models\HistorialEstado;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarreraController extends Controller
{
    public function index()
    {
        $carreras = $this->getCarreras();
        $directores = $this->getDirectores();
        $docentes = $this->getDocentes();
        $historialCambios = HistorialEstado::with('usuario')
            ->where('modulo', 'carreras')
            ->latest('fecha_movimiento')
            ->take(10)
            ->get();

        return view('carreras.index', compact('carreras', 'directores', 'docentes', 'historialCambios'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre_carrera' => 'required|string|max:255|unique:carreras,nombre_carrera',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $carrera = new Carrera();
        $carrera->nombre_carrera = $validated['nombre_carrera'];
        $carrera->descripcion = $validated['descripcion'] ?? null;
        $carrera->fecha_creacion = now();
        $carrera->status = 1;
        $carrera->save();

        return redirect()
            ->route('carreras.index')
            ->with('success', 'La carrera se registró correctamente.');
    }

    public function cambiarEstado(Request $request, Carrera $carrera): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:0,1',
            'motivo' => 'required|string|min:5|max:500',
        ]);

        $estadoAnterior = (int) $carrera->status;
        $estadoNuevo = (int) $validated['status'];

        if ($estadoAnterior === $estadoNuevo) {
            return back()->with('success', 'El estado de la carrera ya estaba actualizado.');
        }

        $carrera->update(['status' => $estadoNuevo]);

        $this->registrarHistorial($carrera, $estadoAnterior, $estadoNuevo, $validated['motivo']);

        $mensaje = $estadoNuevo === 1
            ? 'La carrera fue reactivada correctamente.'
            : 'La carrera fue desactivada correctamente.';

        return back()->with('success', $mensaje);
    }

    public function getCarreras()
    {
        $user = Auth::user();
        $roleId = $user->roles()->pluck('roles.id')->first();

        $query = Carrera::with(['director', 'docentes'])
            ->withCount('docentes')
            ->orderByDesc('status')
            ->orderBy('nombre_carrera');

        if ((int) $roleId === 2) {
            $query->where('director_id', $user->id);
        }

        return $query->get();
    }

    public function getDirectores()
    {
        return User::with('roles')
            ->whereHas('roles', function ($q) {
                $q->where('roles.id', 2);
            })
            ->where('status', 1)
            ->orderBy('name')
            ->get();
    }

    public function getDocentes()
    {
        return User::with('roles')
            ->whereHas('roles', function ($q) {
                $q->where('roles.id', 4);
            })
            ->where('status', 1)
            ->orderBy('name')
            ->get();
    }

    public function asignarDirector(Request $request): RedirectResponse
    {
        $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'director_id' => 'required|exists:users,id',
        ]);

        $carrera = Carrera::findOrFail($request->carrera_id);

        if (! $carrera->status) {
            return back()->with('error', 'No puedes asignar director a una carrera inactiva.');
        }

        $carrera->director_id = $request->director_id;
        $carrera->save();

        return back()->with('success', 'Director asignado correctamente.');
    }

    public function asignarProfesores(Request $request): RedirectResponse
    {
        $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'profesores' => 'nullable|array',
            'profesores.*' => 'exists:users,id',
        ]);

        $carrera = Carrera::findOrFail($request->carrera_id);

        if (! $carrera->status) {
            return back()->with('error', 'No puedes asignar docentes a una carrera inactiva.');
        }

        $carrera->docentes()->sync($request->input('profesores', []));

        return back()->with('success', 'Asignaciones actualizadas correctamente.');
    }

    private function registrarHistorial(Carrera $carrera, int $estadoAnterior, int $estadoNuevo, string $motivo): void
    {
        HistorialEstado::create([
            'modulo' => 'carreras',
            'registro_id' => $carrera->id,
            'registro_nombre' => $carrera->nombre_carrera,
            'accion' => $estadoNuevo === 1 ? 'reactivado' : 'desactivado',
            'estado_anterior' => $estadoAnterior,
            'estado_nuevo' => $estadoNuevo,
            'motivo' => $motivo,
            'user_id' => Auth::id(),
            'fecha_movimiento' => now(),
        ]);
    }
}
