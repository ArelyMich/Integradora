<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carrera extends Model
{
    //
    protected $table = 'carreras';

    protected $fillable = [
        'director_id',
        'nombre_carrera',
        'descripcion',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'fecha_creacion' => 'datetime',
    ];

    public function director(){
        return $this->belongsTo(User::class, 'director_id');
    }

    public function materias(){
        return $this->belongsToMany(Materia::class, 'carrera_materia');
    }

    public function docentes()
    {
        return $this->belongsToMany(User::class, 'carrera_has_profesor', 'carrera_id', 'docente_id')
            ->withTimestamps();
    }

    public function profesores()
    {
        return $this->docentes();
    }

    public function secuencias()
    {
        return $this->hasMany(Secuencia::class, 'carrera_id');
    }

}
