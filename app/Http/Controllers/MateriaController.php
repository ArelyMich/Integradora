<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Materia;
use App\Models\Carrera;
use App\Models\User;

class MateriaController extends Controller
{
    //
    public function index(){

        $carreras = $this->getCarreras();
        $directores = $this->getDirectores();
        $docentes = $this->getDocentes();
        $materias = $this->getMaterias();

        // dd($materias);
        // dd($docentes);

        return view('materias.index',compact('carreras','directores','docentes','materias'));
    }


    public function store(Request $request){
    

        $materia = new Materia();
        $materia->nombre = $request->nombre;
        $materia->codigo = $request->codigo;
        $materia->descripcion = $request->descripcion;
        $materia->horas_semanales = $request->horas_semanales;
        $materia->horas_totales = $request->horas_totales;
        $materia->save();

        
        $materia->carreras()->attach($request->carrera_id);

        return redirect()->route('materias.index')->with('success', 'Materia creada');
}

    public function getCarreras(){
        $user = Auth::user();
        $role_id = $user->roles()->pluck('roles.id')->first();

        if ($role_id == 2) {
            return Carrera::where('director_id', $user->id)->get();
        }

        if ($role_id == 4) {

            $asignaciones = DB::table('profesor_has_materia')
                            ->where('docente_id', $user->id)
                            ->get();

            $carreras_ids = $asignaciones->pluck('carrera_id')->unique(); // IDs sin repetir

            return Carrera::whereIn('id', $carreras_ids)->get();
        }

        return Carrera::all();
    }

     public function getDirectores(){
        return User::with('roles')
            ->whereHas('roles', function($q){
                $q->where('roles.id', 2);
            })
            ->where('status', 1)
            ->get();
    }

    public function getMaterias(){
        $user = Auth::user();
        $role_id = $user->roles()->pluck('roles.id')->first();

        if ($role_id == 2) {
            $carreras = Carrera::where('director_id', $user->id)->get();
            $materias = Materia::whereHas('carreras', function ($q) use ($carreras) {
                $q->whereIn('carrera_id', $carreras->pluck('id'));
            })->with('carreras')->get();

            return $materias;
        } elseif ($role_id == 4) {

            $asignaciones = DB::table('profesor_has_materia')
                            ->where('docente_id', $user->id)
                            ->get();

            $materias_ids = $asignaciones->pluck('materia_id');

            $materias = Materia::whereIn('id', $materias_ids)
                        ->with('carreras')
                        ->get();

            return $materias;
        }else{
            return Materia::with('carreras')->get();
        }

    }

    public function getDocentes(){
        $user = Auth::user();
        $role_id = $user->roles()->pluck('roles.id')->first();

        // Si es director (role 2)
        if ($role_id == 2) {

            // 1. Carreras que dirige
            $carreras = Carrera::where('director_id', $user->id)->get();
            $carreras_ids = $carreras->pluck('id');

            $docentes_ids = DB::table('profesor_has_materia')
                            ->whereIn('carrera_id', $carreras_ids)
                            ->pluck('docente_id')
                            ->unique();

            $docentes = User::with('carreraProfesor')
                            ->whereIn('id', $docentes_ids)
                            ->where('status', 1)
                            ->whereHas('roles', function ($q) {
                                $q->where('roles.id', 4);
                            })
                            ->get();
            // dd($docentes_ids);
            return $docentes;
        }

        return User::with('carreraProfesor')
            ->where('status', 1)
            ->whereHas('roles', function($q){
                $q->where('roles.id', 4);
            })
            ->get();
    }


    public function asignarMateria(Request $request)
{

    // Ver si ya existe la asignación
    $existe = DB::table('profesor_has_materia')
                ->where('docente_id', $request->docente_id)
                ->where('materia_id', $request->materia_id)
                ->first();

    if ($existe) {
        return back()->with('error', 'Este profesor ya está asignado a esta materia');
    }

    DB::table('profesor_has_materia')->insert([
        'docente_id' => $request->docente_id,
        'materia_id' => $request->materia_id,
        'carrera_id' => $request->carrera_id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'Profesor asignado correctamente a la materia');
    }



}
