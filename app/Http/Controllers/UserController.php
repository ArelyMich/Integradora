<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $usuarios = $this->usuariosDB();
        $roles = Role::with(['permissions' => function ($query) {
                $query->orderBy('sitio');
            }])
            ->where('status', 1)
            ->orderBy('nombre')
            ->get();
        $permisos = Permission::where('status', 1)
            ->orderBy('sitio')
            ->get();

        return view('usuarios.index', compact('usuarios', 'roles', 'permisos'));
    }

    private function usuariosDB()
    {
        return User::with(['roles.permissions'])
            ->orderByDesc('status')
            ->orderBy('name')
            ->get();
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[\pL\s]+$/u',
            'apellido_paterno' => 'required|string|max:255|regex:/^[\pL\s]+$/u',
            'apellido_materno' => 'required|string|max:255|regex:/^[\pL\s]+$/u',
            'username' => 'required|string|min:3|max:30|regex:/^[A-Za-z0-9_.-]+$/|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:0,1',
        ], [
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'apellido_paterno.regex' => 'El apellido paterno solo puede contener letras y espacios.',
            'apellido_materno.regex' => 'El apellido materno solo puede contener letras y espacios.',
            'username.regex' => 'El username solo puede contener letras, numeros, punto, guion y guion bajo.',
            'password.regex' => 'La contrasena debe incluir al menos un numero y un caracter especial.',
        ]);

        $role = Role::where('status', 1)->findOrFail($validated['role_id']);

        $user = User::create([
            'name' => $validated['name'],
            'apellido_paterno' => $validated['apellido_paterno'],
            'apellido_materno' => $validated['apellido_materno'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => (int) $validated['status'],
            'two_factor_enabled' => false,
            'email_verified_at' => null,
        ]);

        $user->roles()->sync([$role->id]);

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function cambiarRol(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($id);
        $user->roles()->sync([$request->role_id]);

        return back()->with('success', 'Rol actualizado correctamente.');
    }

    public function actualizarAccesos(Request $request, $id): RedirectResponse
    {
        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permission,id',
        ]);

        $user = User::findOrFail($id);
        $role = Role::where('status', 1)->findOrFail($validated['role_id']);

        $permissionIds = Permission::where('status', 1)
            ->whereIn('id', $validated['permissions'] ?? [])
            ->pluck('id')
            ->all();

        $user->roles()->sync([$role->id]);
        $role->permissions()->sync($permissionIds);

        return back()->with(
            'success',
            'Se actualizaron el rol del usuario y los permisos del rol ' . $role->nombre . '.'
        );
    }
}
