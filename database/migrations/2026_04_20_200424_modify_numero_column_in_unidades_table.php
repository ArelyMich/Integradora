<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unidades', function (Blueprint $table) {

            // Borrar columna actual
            $table->dropColumn('numero');

        });

        Schema::table('unidades', function (Blueprint $table) {

            // Crear nueva como VARCHAR
            $table->string('numero', 10)->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('unidades', function (Blueprint $table) {

            $table->dropColumn('numero');

        });

        Schema::table('unidades', function (Blueprint $table) {

            $table->integer('numero')->nullable();

        });
    }
};