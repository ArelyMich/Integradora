<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('secuencia_comentarios') || ! Schema::hasColumn('secuencia_comentarios', 'estatus')) {
            return;
        }

        // Normaliza estados antiguos antes de redefinir el enum.
        DB::table('secuencia_comentarios')
            ->where('estatus', 'cerrado')
            ->update(['estatus' => 'resuelto']);

        DB::table('secuencia_comentarios')
            ->where('estatus', 'respondido')
            ->update(['estatus' => 'reabierto']);

        DB::statement("ALTER TABLE secuencia_comentarios MODIFY COLUMN estatus ENUM('pendiente', 'reabierto', 'resuelto') NOT NULL DEFAULT 'pendiente'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('secuencia_comentarios') || ! Schema::hasColumn('secuencia_comentarios', 'estatus')) {
            return;
        }

        DB::table('secuencia_comentarios')
            ->where('estatus', 'resuelto')
            ->update(['estatus' => 'cerrado']);

        DB::table('secuencia_comentarios')
            ->where('estatus', 'reabierto')
            ->update(['estatus' => 'respondido']);

        DB::statement("ALTER TABLE secuencia_comentarios MODIFY COLUMN estatus ENUM('pendiente', 'respondido', 'cerrado') NOT NULL DEFAULT 'pendiente'");
    }
};
