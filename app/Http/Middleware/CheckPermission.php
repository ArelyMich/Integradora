<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
// Asegúrate de que esta importación apunte a tu modelo de Permiso real
use App\Models\Permission; 
use App\Models\User;

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

        if (!($user instanceof User)) {
            return redirect()->route('login');
        }

        $userRoleIds = $user->roles()->pluck('roles.id')->all();
        $isAdmin = in_array(1, $userRoleIds, true);

        // Roles academicos con acceso base al panel operativo aunque no exista mapeo en permisos.
        $baseAcademicPermissions = [
            'secuencias.index',
            'secuencias.createView',
            'secuencias.store',
            'secuencias.show',
            'secuencias.verArchivo',
            'secuencias.verArchivoVersion',
            'secuencias.editor',
            'secuencias.ocrArchivo',
            'secuencias.actualizarArchivo',
            'secuencias.anotarArchivo',
            'secuencias.comentarios.guardar',
            'secuencias.comentarios.responder',
            'secuencias.actualizarEstatusAcademico',
            'materias.index',
            'carreras.index',
        ];

        $isAcademicRole = in_array(2, $userRoleIds, true)
            || in_array(3, $userRoleIds, true)
            || in_array(4, $userRoleIds, true);

        if ($isAdmin || ($isAcademicRole && in_array($requiredPermission, $baseAcademicPermissions, true))) {
            return $next($request);
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