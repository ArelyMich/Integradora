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
        Schema::table('secuencia_comentarios', function (Blueprint $table) {
            if (! Schema::hasColumn('secuencia_comentarios', 'coord_mode')) {
                $table->string('coord_mode', 20)->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('secuencia_comentarios', 'page')) {
                $table->unsignedInteger('page')->nullable()->after('coord_mode');
            }

            if (! Schema::hasColumn('secuencia_comentarios', 'x')) {
                $table->decimal('x', 8, 2)->nullable()->after('page');
            }

            if (! Schema::hasColumn('secuencia_comentarios', 'y')) {
                $table->decimal('y', 8, 2)->nullable()->after('x');
            }

            if (! Schema::hasColumn('secuencia_comentarios', 'width')) {
                $table->decimal('width', 8, 2)->nullable()->after('y');
            }

            if (! Schema::hasColumn('secuencia_comentarios', 'height')) {
                $table->decimal('height', 8, 2)->nullable()->after('width');
            }

            if (! Schema::hasColumn('secuencia_comentarios', 'titulo')) {
                $table->string('titulo', 120)->nullable()->after('comentario');
            }

            if (! Schema::hasColumn('secuencia_comentarios', 'info')) {
                $table->string('info', 300)->nullable()->after('titulo');
            }

            if (! Schema::hasColumn('secuencia_comentarios', 'texto_seleccionado')) {
                $table->text('texto_seleccionado')->nullable()->after('info');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('secuencia_comentarios', function (Blueprint $table) {
            $columns = [
                'coord_mode',
                'page',
                'x',
                'y',
                'width',
                'height',
                'titulo',
                'info',
                'texto_seleccionado',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('secuencia_comentarios', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
