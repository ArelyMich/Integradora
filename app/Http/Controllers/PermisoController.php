<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permission;
use Carbon\Carbon;
use App\Models\Role;

class PermisoController extends Controller
{
    //
    public function index(){
 
        $permisos = $this->permisosDB();
        return view('Permisos.index',compact('permisos'));
    }

    public function view($id) {

        $roles = $this->getRoles();
        $permiso = Permission::findOrFail($id);
        
        return view('permisos.view', compact('permiso', 'roles'));
    }

    //********** Fin de las vistas ********** */

    public function store(Request $request){
        $permiso = new Permission();
        $permiso->sitio = $request->sitio;
        $permiso->ruta = $request->ruta;
        $permiso->fecha_creacion = now();
        $permiso->status = $request->status;
        $permiso->save();
        return redirect()->route('permisos.index'); 


    }

    public function update(){
        
    }

    public function desactivarPermiso(Request $request)
    {
        $permisoId = $request->input('permiso_id');
        $newStatus = (int) $request->input('new_status'); 

        try {
          
            $permiso = Permission::findOrFail($permisoId);

            $permiso->status = $newStatus;
            $permiso->save();

           
            $mensaje = $newStatus === 1 ? 'El permiso se ha activado correctamente.' : 'El permiso se ha desactivado correctamente.';
            
            return redirect()
                ->route('permisos.index')
                ->with('success', $mensaje);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Manejo de error si el permiso no se encuentra
            return redirect()
                ->route('permisos.index')
                ->with('error', 'Error: El permiso especificado no fue encontrado.');
        } catch (\Exception $e) {
            // Manejo de otros errores
            return redirect()
                ->route('permisos.index')
                ->with('error', 'Ocurrió un error al intentar modificar el estado del permiso.');
        }
    }

    public function assignRoles(Request $request, Permission $permiso)
    {
        // 1. Validar el request
        $request->validate([
            'roles' => ['nullable', 'array'], 
            'roles.*' => ['exists:roles,id'], 
        ]);

        $roleIds = $request->input('roles', []);
        
        try {
            $permiso->roles()->sync($roleIds);

            return redirect()
                ->route('permisos.view', $permiso->id)
                ->with('success', 'Roles asignados correctamente al permiso ' . $permiso->sitio);

        } catch (\Exception $e) {
            // Manejo de errores
            Log::error('Error al asignar roles al permiso ' . $permiso->id . ': ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al asignar los roles. Inténtalo de nuevo.');
        }
    }
    private function permisosDB(){
        $permisos = Permission::all();
        return $permisos;
    }

    public function getRoles(){
       
        $roles = Role::all();
        return $roles;
    }
}
