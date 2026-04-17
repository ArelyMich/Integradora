<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    //
    protected $table = 'materias';
    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo',
        'horas_semanales',
        'horas_totales',
    ];


    public function carreras() {
    return $this->belongsToMany(Carrera::class, 'carrera_materia', 'materia_id', 'carrera_id');
    }


    public function profesor(){
        return $this->belongsTo(User::class, 'profesor_id');
    }


    public function profesores()
    {
        return $this->belongsToMany(User::class, 'profesor_has_materia', 'materia_id', 'user_id')
            ->withPivot('carrera_id');
    }

    



}
