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
    Schema::create('log_accesos', function (Blueprint $table) {
        $table->id();

        // Email que intentó iniciar sesión (exista o no en la BD)
        $table->string('email');

        // IP del intento
        $table->string('ip_address', 45)->nullable();

        // Resultado del intento
        $table->enum('resultado', ['exitoso', 'fallido'])->default('fallido');

        // Información opcional
        $table->string('user_agent')->nullable();

        $table->timestamps(); // created_at = fecha del intento
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_accesos');
    }
};
