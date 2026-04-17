<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_carrera')->unique();
            $table->integer('status')->default(1);
            $table->dateTime('fecha_creacion');
            $table->timestamps();
        });

        DB::table('carreras')->insert([
            [
                'nombre_carrera' => 'Ingenieria en Alimentos',
                'status' => 1,
                'fecha_creacion' => now(),
            ],
            [
                'nombre_carrera' => 'Ingenieria Industrial',
                'status' => 1,
                'fecha_creacion' => now(),
            ],
            [
                'nombre_carrera' => 'Ingenieria en Mecatronica',
                'status' => 1,
                'fecha_creacion' => now(),
            ],
            [
                'nombre_carrera' => 'Ingeria en Tecnologias de la Informacion e Innovacion Digital',
                'status' => 1,
                'fecha_creacion' => now(),
            ],
            [
                'nombre_carrera' => 'Ingenieria en Diseno Textil y Moda',
                'status' => 1,
                'fecha_creacion' => now(),
            ],
            [
                'nombre_carrera' => 'Ingenieria en Mecanica',
                'status' => 1,
                'fecha_creacion' => now(),
            ],
            [
                'nombre_carrera' => 'Licenciatura en Administracion',
                'status' => 1,
                'fecha_creacion' => now(),
            ],
            [
                'nombre_carrera' => 'Licenciatura en Negocios y Mercadotecnia ',
                'status' => 1,
                'fecha_creacion' => now(),
            ],
            [
                'nombre_carrera' => 'Maestria en Gestion de Proyectos Estrategicos Sostenibles',
                'status' => 1,
                'fecha_creacion' => now(),
            ],
        ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carreras');
    }
};
