<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Secuencia extends Model
{
    //
    protected $table = 'secuencias';

    protected $fillable = [
        'docente_id',
        'materia_id',
        'carrera_id',
        'periodo_id',
        'estatus',
        'status',
        'revisor_id',
        'director_id',
        'horas_programadas',
        'fecha_entrega',
        'archivo_path',
        'archivo_nombre_original',
        'archivo_mime',
    ];

    protected $casts = [
        'status' => 'boolean',
        'fecha_entrega' => 'datetime',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class, 'periodo_id');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisor_id');
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(SecuenciaComentario::class, 'secuencia_id')->latest();
    }

    public function archivoVersiones(): HasMany
    {
        return $this->hasMany(SecuenciaArchivoVersion::class, 'secuencia_id')->latest();
    }
}
