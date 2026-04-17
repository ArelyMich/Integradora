<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario super admin
        $user = User::create([
            'name' => 'Administrador',
            'apellido_paterno' => 'Sistemas',
            'apellido_materno' => '1',
            'username' => 'adminSistemas',
            'email' => 'adminSistemas@uth.edu.mx',
            'password' => Hash::make('administracion2025'),
            'status' => 1,
        ]);

        // Crear rol Super Admin
        $role = Role::create([
            'nombre' => 'AdministradorSistemas',
            'status' => 1,
            'fecha_creacion' => now(),
        ]);

        // Asignar rol al usuario
        $user->roles()->attach($role->id);
    }
}
