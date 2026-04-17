<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Carrera;
use App\Models\User;

class CarreraController extends Controller
{
    //

    public function index(){

        $carreras = $this->getCarreras();
        $directores = $this->getDirectores();
        $docentes = $this->getDocentes();
        
        return view('carreras.index', compact('carreras','directores','docentes'));
    }

    public function store(Request $request){

            $carreraN = New Carrera();
            $carreraN->nombre_carrera = $request->nombre_carrera;
            $carreraN->descripcion = $request->descripcion;
            $carreraN->fecha_creacion = now();

            $carreraN->save();
            return redirect()->route('carreras.index');

    }

    public function getCarreras(){
        $user = Auth::user();
        $role_id = $user->roles()->pluck('roles.id')->first();

        if ($role_id == 2) { 
                $carrera = Carrera::where('director_id', $user->id)->get();
            }else{
                $carrera = Carrera::all();
        }
        // dd($carrera);
        return $carrera;
    }

    public function getDirectores(){
        return User::with('roles')
            ->whereHas('roles', function($q){
                $q->where('roles.id', 2);
            })
            ->where('status', 1)
            ->get();
    }

    public function getDocentes(){
        return User::with('roles')
            ->whereHas('roles', function($q){
                $q->where('roles.id', 4);
            })
            ->where('status', 1)
            ->get();
    }

    public function asignarDirector(Request $request){
        $request->validate([
            'carrera_id' => 'required|exists:carreras,id',
            'director_id' => 'required|exists:users,id'
        ]);

        $carrera = Carrera::find($request->carrera_id);
        $carrera->director_id = $request->director_id;
        $carrera->save();

        return back()->with('success', 'Director asignado correctamente');
    }

    public function asignarProfesores(Request $request){
        $request->validate([
            'carrera_id'   => 'required|exists:carreras,id',
            'profesores'   => 'required|array',
            'profesores.*' => 'exists:users,id'
        ]);

        $asignados = DB::table('carrera_has_profesor')
            ->where('carrera_id', $request->carrera_id)
            ->pluck('docente_id')
            ->toArray();

        foreach ($request->profesores as $docente) {

            //  Si ya está asignado → quitarlo (toggle)
            if (in_array($docente, $asignados)) {

                DB::table('carrera_has_profesor')
                    ->where('carrera_id', $request->carrera_id)
                    ->where('docente_id', $docente)
                    ->delete();

            } else {

            
                $existe = DB::table('carrera_has_profesor')
                    ->where('carrera_id', $request->carrera_id)
                    ->where('docente_id', $docente)
                    ->exists();

                if (!$existe) {
                    // Asignarlo
                    DB::table('carrera_has_profesor')->insert([
                        'carrera_id' => $request->carrera_id,
                        'docente_id' => $docente
                    ]);
                }
            }
        }

        return back()->with('success', 'Asignaciones actualizadas correctamente');
    }






}
