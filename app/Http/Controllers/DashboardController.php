<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Secuencia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
         $totalDocentes = DB::table('users')
            ->join('user_has_role', 'user_has_role.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'user_has_role.role_id')
            ->where('roles.nombre', 'Docente')
            ->count();

        // Total de secuencias creadas
        $totalSecuencias = DB::table('secuencias')->count();

        // Secuenciass entregadas vs pendientes
        $secPendientes = DB::table('secuencias')
            ->where('estatus', 'pendiente')
            ->count();

        $secAprobadas = DB::table('secuencias')
            ->where('estatus', 'aprobada')
            ->count();

        // Materias activas
        $materiasActivas = DB::table('materias')->count();

        // Secuenciass por especialidad (carrera)
        $porEspecialidad = DB::table('secuencias')
            ->join('carreras', 'carreras.id', '=', 'secuencias.carrera_id')
            ->select(
                'carreras.nombre_carrera as especialidad',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('carreras.nombre_carrera')
            ->get();

        // Próximas fechas de entrega de esta semana
        $hoy = Carbon::now()->startOfDay();
        $finSemana = Carbon::now()->addDays(7)->endOfDay();

        /*$entregasSemana = DB::table('secuencias')
            ->whereBetween('fecha_entrega', [$hoy, $finSemana])
            ->orderBy('fecha_entrega')
            ->get();
        */

        return view('Dashboard', compact(
            'totalDocentes',
            'totalSecuencias',
            'secPendientes',
            'secAprobadas',
            'materiasActivas',
            'porEspecialidad'
            //'entregasSemana'
        ));
    }

    // ---------------------------------------------
    // 📊 G R A F I C A S  (JSON para Chart.js)
    // ---------------------------------------------

    // Barras: secuencias entregadas por mes
    public function chartSecuenciasMes()
    {
        $data = DB::table('secuencias')
            ->select(
                DB::raw('MONTH(created_at) as mes'),
                DB::raw('COUNT(*) as total')
            )
            ->where('estatus', 'aprobada')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return response()->json($data);
    }

    // Dona: secuencias creadas por especialidad
    public function chartEspecialidades()
    {
        $data = DB::table('secuencias')
            ->join('carreras', 'carreras.id', '=', 'secuencias.carrera_id')
            ->select(
                DB::raw('carreras.nombre_carrera as especialidad'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('especialidad')
            ->get();

        return response()->json($data);
    }

    // Línea: avance semanal de entregas
    public function entregasPorSemana()
    {
        $data = DB::table('secuencias')
            ->select(
                DB::raw('WEEK(fecha_entrega) as semana'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('semana')
            ->get();

        return response()->json($data);
    }

    // Barras apiladas: entregas completas vs incompletas por docente
    public function chartEntregadasDocente()
    {
        $data = DB::table('secuencias')
            ->join('users', 'users.id', '=', 'secuencias.docente_id')
            ->select(
                DB::raw("CONCAT(users.name, ' ', users.apellido_paterno) as docente"),
                DB::raw("SUM(estatus = 'aprobada') as completas"),
                DB::raw("SUM(estatus != 'aprobada') as incompletas")
            )
            ->groupBy('docente')
            ->get();

        return response()->json($data);
    }
}