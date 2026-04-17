<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstado extends Model
{
    protected $table = 'historial_estados';

    protected $fillable = [
        'modulo',
        'registro_id',
        'registro_nombre',
        'accion',
        'estado_anterior',
        'estado_nuevo',
        'motivo',
        'user_id',
        'fecha_movimiento',
    ];

    protected $casts = [
        'fecha_movimiento' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
