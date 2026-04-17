<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
class UserController extends Controller
{
    //
public function index()
{
    $usuarios = $this->usuariosDB();
    $roles = Role::all();

    return view('usuarios.index', compact('usuarios', 'roles'));
}

    private function usuariosDB(){
        $usuarios = User::with('roles')
                    ->where('status', 1)
                    ->get();
        return $usuarios;
    }

public function cambiarRol(Request $request, $id)
{
    $request->validate([
        'role_id' => 'required|exists:roles,id'
    ]);

    $user = User::findOrFail($id);

    // Quita roles anteriores y asigna el nuevo
    $user->roles()->sync([$request->role_id]);

    return back()->with('success', 'Rol actualizado correctamente');
}
  
}
