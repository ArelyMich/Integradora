<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function index()
    {
        $usuario = Auth::user()->load('roles'); // solo roles
        return view('perfil', compact('usuario'));
    }

    public function cambiarContrasena(Request $request)
    {
        // Validamos la contraseña actual y la nueva
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        // Verificar que la contraseña actual sea correcta
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }

        // Actualizar la contraseña
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Contraseña actualizada correctamente.');
    }
    public function actualizarUsername(Request $request)
{
    $request->validate([
        'username' => 'required|string|max:255|unique:users,username,' . Auth::id(),
    ]);

    $user = Auth::user();
    $user->username = $request->username;
    $user->save();

    return back()->with('success', 'Username actualizado correctamente.');
}

}
