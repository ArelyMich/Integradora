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
        Schema::create('permission', function (Blueprint $table) {
            $table->id();
            $table->string('ruta')->unique();
            $table->string('sitio')->unique();
            $table->dateTime('fecha_creacion');
            $table->integer('status');
            $table->timestamps();
        });

        DB::table('permission')->insert([
        [
            'ruta' => 'usuarios.index',
            'sitio' => 'Usuarios Index',
            'fecha_creacion' => now(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'ruta' => 'permisos.index',
            'sitio' => 'Permisos Index',
            'fecha_creacion' => now(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'ruta' => 'permisos.store',
            'sitio' => 'Crear Permiso',
            'fecha_creacion' => now(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'ruta' => 'secuencias.index',
            'sitio' => 'Secuencias Index',
            'fecha_creacion' => now(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'ruta' => 'roles.index',
            'sitio' => 'Roles Index',
            'fecha_creacion' => now(),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    }

    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission');
    }
};
