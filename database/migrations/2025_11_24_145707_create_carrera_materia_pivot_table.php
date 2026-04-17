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
       Schema::create('carrera_materia', function (Blueprint $table) {
        $table->id();
        $table->foreignId('carrera_id')->constrained();
        $table->foreignId('materia_id')->constrained();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrera_materia_pivot');
    }
};
