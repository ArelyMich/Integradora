<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecuenciaArchivoVersion extends Model
{
    protected $table = 'secuencia_archivo_versiones';

    protected $fillable = [
        'secuencia_id',
        'user_id',
        'archivo_path',
        'archivo_nombre_original',
        'archivo_mime',
        'archivo_size',
        'accion',
    ];

    public function secuencia(): BelongsTo
    {
        return $this->belongsTo(Secuencia::class, 'secuencia_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
