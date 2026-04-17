<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
// Asegúrate de que esta importación apunte a tu modelo de Permiso real
use App\Models\Permission; 

class CheckPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $requiredPermission = $request->route()->getName();
        
        if (is_null($requiredPermission)) {
             return $next($request);
        }

        $user = Auth::user();

        if (!$user) {
            
            return redirect()
                ->route('login') 
                ->with('error', 'Debes iniciar sesión para acceder a esta sección.');
        }

        if (!$user->hasPermission($requiredPermission)) {
            abort(403, 'Acceso Denegado. Permiso requerido ');
        }
        
        $permissionRecord = Permission::where('ruta', $requiredPermission)
                                      ->first();

        if ($permissionRecord && $permissionRecord->status == 0) {
            
            abort(403, 'Acceso Denegado. El permiso (' . $requiredPermission . ') ha sido DESACTIVADO por el administrador.');
        }
        
        return $next($request);
    }
}