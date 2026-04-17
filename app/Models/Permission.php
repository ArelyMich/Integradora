<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    //
    protected $table = 'permission';

    protected $fillable = [
        'sitio',
        'ruta',
        'fecha_creacion',
        'status',
    ];


    // En App\Models\Permission.php
public function roles()
{
 
    return $this->belongsToMany(Role::class, 'role_has_permission', 'permission_id', 'role_id');
}
}
