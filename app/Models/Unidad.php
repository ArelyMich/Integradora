<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{

    protected $table = 'unidades';

    protected $fillable = [

        'secuencia_id',
        'nombre',
        'numero',
        'horas'

    ];

    public function secuencia()
    {
        return $this->belongsTo(Secuencia::class);
    }

}