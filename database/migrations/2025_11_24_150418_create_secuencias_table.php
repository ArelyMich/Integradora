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
        Schema::create('secuencias', function (Blueprint $table) {
    $table->id();
    $table->foreignId('docente_id')->constrained('users');
    $table->foreignId('materia_id')->constrained();
    $table->foreignId('carrera_id')->constrained();
    $table->foreignId('periodo_id')->constrained();

    $table->enum('estatus', ['elaboracion','pendiente','revision','correcciones','entregada','aprobada'])->default('pendiente');

    $table->foreignId('revisor_id')->nullable()->constrained('users');
    $table->foreignId('director_id')->nullable()->constrained('users'); // quien autoriza

    $table->string('horas_programadas')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secuencias');
    }
};
