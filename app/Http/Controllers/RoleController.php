<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    // MOSTRAR LISTA
    public function index() {
        $roles = Role::orderBy('id','desc')->get();
        return view('roles.index', compact('roles'));
    }

    // GUARDAR ROL NUEVO
    public function store(Request $request) {
        $request->validate([
            'nombre' => 'required|string|max:100'
        ]);

        Role::create([
            'nombre' => $request->nombre,
            'status' => 1,
            'fecha_creacion' => now(),
        ]);

        return back()->with('success', 'Rol creado correctamente.');
    }

    // ACTUALIZAR ROL
    public function update(Request $request, $id) {
        $request->validate([
            'nombre' => 'required|string|max:100'
        ]);

        $role = Role::findOrFail($id);

        $role->update([
            'nombre' => $request->nombre,
            'status' => $request->status
        ]);

        return back()->with('success', 'Rol actualizado correctamente.');
    }

    // DESACTIVAR/ELIMINAR (status = 0)
    public function destroy($id) {
        $role = Role::findOrFail($id);
        $role->update(['status' => 0]);

        return back()->with('success', 'El rol ahora esta inactivo.');
    }
}
