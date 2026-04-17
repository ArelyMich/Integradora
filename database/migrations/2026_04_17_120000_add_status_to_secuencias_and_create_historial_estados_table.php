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
            $table->integer('status')->default(1)->after('estatus');
        });

        Schema::create('historial_estados', function (Blueprint $table) {
            $table->id();
            $table->string('modulo', 50);
            $table->unsignedBigInteger('registro_id');
            $table->string('registro_nombre')->nullable();
            $table->string('accion', 30);
            $table->integer('estado_anterior')->nullable();
            $table->integer('estado_nuevo');
            $table->text('motivo')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_movimiento')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_estados');

        Schema::table('secuencias', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
