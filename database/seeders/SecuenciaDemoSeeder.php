<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Periodo;
use App\Models\Role;
use App\Models\Secuencia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

class SecuenciaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $directorRole = Role::query()->where('nombre', 'Director de carrera')->first();
        $revisorRole = Role::query()->where('nombre', 'Revisor')->first();
        $docenteRole = Role::query()->where('nombre', 'Docente')->first();

        if (! $directorRole || ! $revisorRole || ! $docenteRole) {
            $this->command?->warn('No se encontraron roles base (Director de carrera, Revisor, Docente).');
            return;
        }

        $director = User::query()->firstOrCreate(
            ['email' => 'director.demo@uth.edu.mx'],
            [
                'name' => 'Daniel',
                'apellido_paterno' => 'Ortega',
                'apellido_materno' => 'Mendez',
                'username' => 'director_demo',
                'password' => Hash::make('Demo1234!'),
                'status' => 1,
                'email_verified_at' => now(),
            ]
        );

        $revisor = User::query()->firstOrCreate(
            ['email' => 'revisor.demo@uth.edu.mx'],
            [
                'name' => 'Rocio',
                'apellido_paterno' => 'Navarro',
                'apellido_materno' => 'Solis',
                'username' => 'revisor_demo',
                'password' => Hash::make('Demo1234!'),
                'status' => 1,
                'email_verified_at' => now(),
            ]
        );

        $docente = User::query()->firstOrCreate(
            ['email' => 'docente.demo@uth.edu.mx'],
            [
                'name' => 'Carlos',
                'apellido_paterno' => 'Morales',
                'apellido_materno' => 'Reyes',
                'username' => 'docente_demo',
                'password' => Hash::make('Demo1234!'),
                'status' => 1,
                'email_verified_at' => now(),
            ]
        );

        $director->roles()->syncWithoutDetaching([$directorRole->id]);
        $revisor->roles()->syncWithoutDetaching([$revisorRole->id]);
        $docente->roles()->syncWithoutDetaching([$docenteRole->id]);

        $carrera = Carrera::query()->firstOrCreate(
            ['nombre_carrera' => 'Ingenieria en Tecnologias de la Informacion e Innovacion Digital'],
            [
                'descripcion' => 'Carrera demo para panel de revision de secuencias.',
                'status' => 1,
                'fecha_creacion' => now(),
                'director_id' => $director->id,
            ]
        );

        if ((int) ($carrera->director_id ?? 0) !== (int) $director->id) {
            $carrera->director_id = $director->id;
            $carrera->status = 1;
            $carrera->save();
        }

        $materiaWeb = Materia::query()->firstOrCreate(
            ['nombre' => 'Desarrollo de Aplicaciones Web'],
            [
                'codigo' => 'TID-301',
                'descripcion' => 'Materia demo para flujo de secuencia didactica.',
                'horas_semanales' => 6,
                'horas_totales' => 90,
            ]
        );

        $materiaIngenieria = Materia::query()->firstOrCreate(
            ['nombre' => 'Ingenieria de Software'],
            [
                'codigo' => 'TID-302',
                'descripcion' => 'Materia demo para revision y correcciones.',
                'horas_semanales' => 5,
                'horas_totales' => 75,
            ]
        );

        $periodo = Periodo::query()->firstOrCreate(
            [
                'nombre' => 'Enero-Abril',
                'anio' => 2026,
            ]
        );

        $hasHorasProgramadas = Schema::hasColumn('secuencias', 'horas_programadas');
        $hasFechaEntrega = Schema::hasColumn('secuencias', 'fecha_entrega');

        $payloadSecuenciaUno = [
            'estatus' => 'revision',
            'status' => 1,
            'revisor_id' => $revisor->id,
            'director_id' => $director->id,
        ];

        if ($hasHorasProgramadas) {
            $payloadSecuenciaUno['horas_programadas'] = '90';
        }

        if ($hasFechaEntrega) {
            $payloadSecuenciaUno['fecha_entrega'] = now()->subDays(2);
        }

        Secuencia::query()->updateOrCreate(
            [
                'docente_id' => $docente->id,
                'materia_id' => $materiaWeb->id,
                'carrera_id' => $carrera->id,
                'periodo_id' => $periodo->id,
            ],
            $payloadSecuenciaUno
        );

        $payloadSecuenciaDos = [
            'estatus' => 'correcciones',
            'status' => 1,
            'revisor_id' => $revisor->id,
            'director_id' => $director->id,
        ];

        if ($hasHorasProgramadas) {
            $payloadSecuenciaDos['horas_programadas'] = '75';
        }

        if ($hasFechaEntrega) {
            $payloadSecuenciaDos['fecha_entrega'] = now()->subDay();
        }

        Secuencia::query()->updateOrCreate(
            [
                'docente_id' => $docente->id,
                'materia_id' => $materiaIngenieria->id,
                'carrera_id' => $carrera->id,
                'periodo_id' => $periodo->id,
            ],
            $payloadSecuenciaDos
        );

        $this->command?->info('Seeder demo de secuencias ejecutado. Usuario revisor: revisor.demo@uth.edu.mx / Demo1234!');
    }
}
