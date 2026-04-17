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
        
        Schema::create('profesor_has_materia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('carrera_id')->nullable();
            $table->unsignedBigInteger('docente_id')->nullable();
            $table->unsignedBigInteger('materia_id')->nullable();

            $table->foreign('carrera_id')->references('id')->on('carreras')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('docente_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->foreign('materia_id')->references('id')->on('materias')->onUpdate('cascade')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrera_has_materia');
    }
};
