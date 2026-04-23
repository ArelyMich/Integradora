<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('secuencias', function (Blueprint $table) {
            // Hacer nullable los campos que se crean con NULL en uploadAndExtract
            $table->unsignedBigInteger('docente_id')->nullable()->change();
            $table->unsignedBigInteger('materia_id')->nullable()->change();
            $table->unsignedBigInteger('carrera_id')->nullable()->change();
            $table->unsignedBigInteger('periodo_id')->nullable()->change();
            
            // Agregar tutor_id si no existe
            if (!Schema::hasColumn('secuencias', 'tutor_id')) {
                $table->unsignedBigInteger('tutor_id')->nullable();
            }
            
            // Agregar ruta_pdf si no existe
            if (!Schema::hasColumn('secuencias', 'ruta_pdf')) {
                $table->string('ruta_pdf')->nullable();
            }
            
            // Agregar caratula_id si no existe
            if (!Schema::hasColumn('secuencias', 'caratula_id')) {
                $table->unsignedBigInteger('caratula_id')->nullable();
            }
            
            // Agregar fecha_entrega si no existe
            if (!Schema::hasColumn('secuencias', 'fecha_entrega')) {
                $table->timestamp('fecha_entrega')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('secuencias', function (Blueprint $table) {
            // Revertir cambios
            $table->unsignedBigInteger('docente_id')->change();
            $table->unsignedBigInteger('materia_id')->change();
            $table->unsignedBigInteger('carrera_id')->change();
            $table->unsignedBigInteger('periodo_id')->change();
        });
    }
};
