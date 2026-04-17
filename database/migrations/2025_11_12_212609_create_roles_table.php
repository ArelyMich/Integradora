<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('status')->default(1);
            $table->dateTime('fecha_creacion');
            $table->timestamps();
        });

        DB::table('roles')->insert([
            [
                'nombre' => 'Administrador',
                'status' => 1,
                'fecha_creacion' => now(),
            ],
            [
                'nombre' => 'Director de carrera',
                'status' => 1,
                'fecha_creacion' => now(),
            ],
            [
                'nombre' => 'Revisor',
                'status' => 1,
                'fecha_creacion' => now(),
            ],
            [
                'nombre' => 'Docente',
                'status' => 1,
                'fecha_creacion' => now(),
            ],
            [
                'nombre' => 'Nuevo',
                'status' => 1,
                'fecha_creacion' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rols');
    }
};
