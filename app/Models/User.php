<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // ✅ IMPORTANTE
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;

class User extends Authenticatable implements MustVerifyEmail // ✅ AQUÍ
{
    use HasFactory, Notifiable;

    public const SUPERUSER_EMAILS = [
        '3523110586@uth.edu.mx',
        '3523110586@uth.edu.nx',
    ];

    protected $fillable = [
        'name',
        'apellido_paterno',
        'apellido_materno',
        'username',
        'email',
        'password',
        'status',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_expires_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
    ];

    // --------- ROLES ----------
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_has_role', 'user_id', 'role_id');
    }

    public function permissions()
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }

    public function hasPermission($permissionName)
    {
        if ($this->isSuperUser()) {
            return true;
        }

        return $this->permissions()->contains(function ($perm) use ($permissionName) {
            return $perm->ruta === $permissionName;
        });
    }

    public function isSuperUser(): bool
    {
        return in_array(
            strtolower(trim((string) $this->email)),
            self::SUPERUSER_EMAILS,
            true
        );
    }

    public function getFirstRoleAttribute()
    {
        return $this->roles->first()?->nombre ?? 'Sin rol';
    }

    public function rolPuesto()
    {
        return $this->belongsToMany(Role::class,'user_has_role');
    }

    public function materias()
    {
        return $this->hasMany(Materia::class, 'profesor_id');
    }

    public function carreraDirigida()
    {
        return $this->hasOne(Carrera::class, 'director_id');
    }

    public function carreraDirector()
    {
        return $this->hasOne(Carrera::class, 'director_id');
    }

    public function carreraProfesor()
    {
        return $this->belongsToMany(Carrera::class, 'carrera_has_profesor', 'docente_id', 'carrera_id');
    }

    public function materiasProfesor()
    {
        return $this->belongsToMany(Materia::class, 'profesor_has_materia', 'user_id', 'materia_id')
            ->withPivot('carrera_id');
    }
}
