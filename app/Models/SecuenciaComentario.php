<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecuenciaComentario extends Model
{
    protected $table = 'secuencia_comentarios';

    protected $fillable = [
        'secuencia_id',
        'user_id',
        'coord_mode',
        'page',
        'x',
        'y',
        'width',
        'height',
        'titulo',
        'info',
        'texto_seleccionado',
        'comentario',
        'respuesta',
        'respuesta_user_id',
        'estatus',
    ];

    protected $casts = [
        'page' => 'integer',
        'x' => 'decimal:2',
        'y' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
    ];

    public function secuencia(): BelongsTo
    {
        return $this->belongsTo(Secuencia::class, 'secuencia_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function respuestaUsuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'respuesta_user_id');
    }
}
