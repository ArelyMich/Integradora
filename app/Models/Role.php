<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    //
    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'status',
        'fecha_creacion',
    ];

    public function permissions()
{
    return $this->belongsToMany(Permission::class, 'role_has_permission');
}

}
