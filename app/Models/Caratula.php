<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caratula extends Model
{

    protected $table = 'caratulas';

    protected $fillable = [

        'carrera',
        'docente',
        'cuatrimestre',
        'periodo_escolar',
        'asignatura',
        'grupo',
        'competencia',
        'tipo_competencia',
        'creditos',
        'modalidad',
        'horas_saber',
        'horas_saber_hacer',
        'horas_totales',
        'horas_semana',
        'json_data',
        'proposito'

    ];
}