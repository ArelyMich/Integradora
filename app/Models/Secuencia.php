<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Secuencia extends Model
{

    protected $table = 'secuencias';

    protected $fillable = [

        'ruta_pdf',
        'caratula_id',
        'docente_id',
        'materia_id',
        'carrera_id',
        'periodo_id',
        'revisor_id',
        'director_id',
        'tutor_id',
        'estatus',
        'status',
        'fecha_entrega',
        'archivo_path',
        'archivo_nombre_original',
        'archivo_mime',
        'horas_programadas'

    ];

    public function caratula()
    {
        return $this->belongsTo(Caratula::class);
    }
    
    public function unidades()
    {
        return $this->hasMany(Unidad::class);
    }

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

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisor_id');
    }

    public function archivoVersiones(): HasMany
    {
        return $this->hasMany(SecuenciaArchivoVersion::class, 'secuencia_id');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(SecuenciaComentario::class, 'secuencia_id');
    }

}
