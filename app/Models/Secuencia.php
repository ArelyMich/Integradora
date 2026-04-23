<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'fecha_entrega'

    ];

    public function caratula()
    {
        return $this->belongsTo(Caratula::class);
    }
    
    public function unidades()
    {
        return $this->hasMany(Unidad::class);
    }

}