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
            if (! Schema::hasColumn('secuencias', 'archivo_path')) {
                $table->string('archivo_path')->nullable()->after('fecha_entrega');
            }

            if (! Schema::hasColumn('secuencias', 'archivo_nombre_original')) {
                $table->string('archivo_nombre_original')->nullable()->after('archivo_path');
            }

            if (! Schema::hasColumn('secuencias', 'archivo_mime')) {
                $table->string('archivo_mime', 100)->nullable()->after('archivo_nombre_original');
            }
        });

        Schema::create('secuencia_comentarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('secuencia_id')->constrained('secuencias')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('comentario');
            $table->text('respuesta')->nullable();
            $table->foreignId('respuesta_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('estatus', ['pendiente', 'respondido', 'cerrado'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secuencia_comentarios');

        Schema::table('secuencias', function (Blueprint $table) {
            if (Schema::hasColumn('secuencias', 'archivo_mime')) {
                $table->dropColumn('archivo_mime');
            }

            if (Schema::hasColumn('secuencias', 'archivo_nombre_original')) {
                $table->dropColumn('archivo_nombre_original');
            }

            if (Schema::hasColumn('secuencias', 'archivo_path')) {
                $table->dropColumn('archivo_path');
            }
        });
    }
};
