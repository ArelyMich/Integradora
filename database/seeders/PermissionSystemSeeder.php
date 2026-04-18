<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Route;

class PermissionSystemSeeder extends Seeder
{
    /**
     * Seed all named routes as permissions.
     */
    public function run(): void
    {
        $now = now();

        $routeNames = collect(Route::getRoutes()->getRoutesByName())
            ->keys()
            ->filter(fn ($name) => is_string($name) && $name !== '')
            ->unique()
            ->values();

        foreach ($routeNames as $routeName) {
            $permission = Permission::firstOrNew(['ruta' => $routeName]);

            if (! $permission->exists) {
                $permission->fecha_creacion = $now;
                $permission->status = 1;
            }

            // Keep sitio unique and predictable.
            $permission->sitio = $routeName;
            $permission->save();
        }

        // Give all permissions to admin role by default.
        $adminRole = Role::query()
            ->where('id', 1)
            ->orWhere('nombre', 'Administrador')
            ->first();

        if ($adminRole) {
            $permissionIds = Permission::query()->pluck('id')->all();
            $adminRole->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
