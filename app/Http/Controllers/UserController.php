<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
