@extends('layouts.principal')

@section('content')
<div
    x-data="accessManager({
        usuarios: {{ Js::from($usuarios->map(fn($usuario) => [
            'id' => $usuario->id,
            'nombre' => trim($usuario->name . ' ' . $usuario->apellido_paterno . ' ' . ($usuario->apellido_materno ?? '')),
            'email' => $usuario->email,
            'status' => (int) $usuario->status,
            'role_id' => $usuario->roles->first()?->id,
            'role_name' => $usuario->roles->first()?->nombre ?? 'Sin rol',
            'permission_ids' => $usuario->roles->first()?->permissions->pluck('id')->values() ?? [],
        ])) }},
        roles: {{ Js::from($roles->map(fn($rol) => [
            'id' => $rol->id,
            'nombre' => $rol->nombre,
            'permission_ids' => $rol->permissions->pluck('id')->values(),
            'permissions_count' => $rol->permissions->count(),
        ])) }},
        permisos: {{ Js::from($permisos->map(fn($permiso) => [
            'id' => $permiso->id,
            'sitio' => $permiso->sitio,
            'ruta' => $permiso->ruta,
        ])) }},
        accessBaseUrl: '{{ url('/usuarios') }}'
    })"
    class="min-h-screen bg-slate-100/70 p-4 md:p-8"
    x-cloak
>
    <div class="mx-auto max-w-7xl space-y-8">
        <section class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#07142B] via-[#173C4C] to-[#568F7C] text-white shadow-2xl shadow-slate-300/40">
            <div class="grid gap-8 px-6 py-8 md:grid-cols-[1.3fr_.9fr] md:px-10 md:py-10">
                <div class="space-y-4">
                    <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-black uppercase tracking-[0.3em] text-white/90">Accesos</span>
                    <div>
                        <h1 class="text-3xl font-black tracking-tight md:text-5xl">Usuarios, roles y permisos en un solo panel.</h1>
                        <p class="mt-3 max-w-2xl text-sm text-white/85 md:text-base">
                            Desde aquí puedes abrir un solo modal para cambiar el rol de cualquier usuario y marcar todos los permisos del rol seleccionado sin saltar entre pantallas.
                        </p>
                    </div>
                    <div class="rounded-3xl border border-white/15 bg-white/10 p-4 text-sm text-white/85">
                        Los permisos siguen siendo administrados por rol. Si cambias permisos dentro del modal, también impactas a los demás usuarios que usen ese mismo rol.
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-3xl bg-white/12 p-5 backdrop-blur-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/60">Usuarios</p>
                        <p class="mt-3 text-4xl font-black" x-text="usuarios.length"></p>
                    </div>
                    <div class="rounded-3xl bg-white/12 p-5 backdrop-blur-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/60">Activos</p>
                        <p class="mt-3 text-4xl font-black" x-text="usuariosActivos"></p>
                    </div>
                    <div class="rounded-3xl bg-white/12 p-5 backdrop-blur-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/60">Roles</p>
                        <p class="mt-3 text-4xl font-black" x-text="roles.length"></p>
                    </div>
                    <div class="rounded-3xl bg-white/12 p-5 backdrop-blur-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/60">Permisos</p>
                        <p class="mt-3 text-4xl font-black" x-text="permisos.length"></p>
                    </div>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-white px-5 py-4 shadow-sm">
                <p class="text-sm font-black text-rose-700">Hay validaciones pendientes:</p>
                <ul class="mt-2 space-y-1 text-sm text-slate-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-[2rem] bg-white p-6 shadow-xl shadow-slate-200/60">
            <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Directorio</p>
                    <h2 class="mt-1 text-2xl font-black text-slate-900">Gestión centralizada de accesos</h2>
                </div>
                <div class="grid gap-3 md:grid-cols-3">
                    <input
                        x-model="search"
                        type="text"
                        placeholder="Buscar usuario o correo"
                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#173C4C] focus:bg-white"
                    >
                    <select x-model="filterRole" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#173C4C]">
                        <option value="">Todos los roles</option>
                        <template x-for="rol in roles" :key="rol.id">
                            <option :value="rol.nombre" x-text="rol.nombre"></option>
                        </template>
                    </select>
                    <select x-model="filterStatus" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#173C4C]">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 overflow-hidden rounded-[1.75rem] border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="bg-slate-900 text-xs font-black uppercase tracking-[0.2em] text-white">
                            <tr>
                                <th class="px-5 py-4">Usuario</th>
                                <th class="px-5 py-4">Correo</th>
                                <th class="px-5 py-4">Rol</th>
                                <th class="px-5 py-4">Permisos</th>
                                <th class="px-5 py-4">Estado</th>
                                <th class="px-5 py-4 text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            <template x-for="usuario in filteredUsuarios" :key="usuario.id">
                                <tr class="transition hover:bg-slate-50/80">
                                    <td class="px-5 py-4">
                                        <p class="text-sm font-black text-slate-900" x-text="usuario.nombre"></p>
                                        <p class="mt-1 text-xs font-semibold text-slate-400" x-text="'ID #' + usuario.id"></p>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-700" x-text="usuario.email"></td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700" x-text="usuario.role_name"></span>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-600" x-text="usuario.permission_ids.length + ' permisos'"></td>
                                    <td class="px-5 py-4">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-black uppercase tracking-[0.2em]"
                                            :class="usuario.status ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                                            x-text="usuario.status ? 'Activo' : 'Inactivo'"
                                        ></span>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if(auth()->user()->hasPermission('usuarios.cambiarRol'))
                                            <button
                                                @click="openAccessModal(usuario)"
                                                class="rounded-2xl bg-[#173C4C] px-4 py-2.5 text-sm font-black text-white shadow-lg transition hover:-translate-y-0.5 hover:bg-[#07142B]"
                                            >
                                                Gestionar accesos
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div x-show="filteredUsuarios.length === 0" class="px-6 py-12 text-center text-sm font-semibold text-slate-500" style="display: none;">
                    No hay usuarios que coincidan con los filtros actuales.
                </div>
            </div>
        </section>
    </div>

    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="closeModal()"></div>
        <div class="relative z-10 w-full max-w-5xl rounded-[2rem] bg-white p-6 shadow-2xl">
            <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Modal único</p>
                    <h3 class="mt-1 text-3xl font-black text-slate-900">Asignación de rol y permisos</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Usuario:
                        <span class="font-black text-slate-800" x-text="selectedUser.nombre || 'Sin selección'"></span>
                    </p>
                </div>
                <button @click="closeModal()" class="self-start rounded-full bg-slate-100 px-3 py-2 text-slate-500 transition hover:bg-slate-200">×</button>
            </div>

            <form :action="formAction" method="POST" class="mt-6 space-y-6">
                @csrf

                <div class="grid gap-6 lg:grid-cols-[0.95fr_1.45fr]">
                    <section class="rounded-[1.75rem] border border-slate-200 bg-slate-50 p-5">
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Rol</p>
                        <h4 class="mt-1 text-xl font-black text-slate-900">Configuración principal</h4>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="text-sm font-bold text-slate-600">Rol del usuario</label>
                                <select
                                    name="role_id"
                                    x-model="selectedRoleId"
                                    @change="applyRolePermissions()"
                                    class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-[#173C4C]"
                                >
                                    <option value="">Seleccione un rol</option>
                                    <template x-for="rol in roles" :key="rol.id">
                                        <option :value="rol.id" x-text="rol.nombre"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="rounded-2xl bg-white p-4 shadow-sm">
                                <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Resumen del rol</p>
                                <p class="mt-2 text-sm font-semibold text-slate-700">
                                    Permisos marcados:
                                    <span class="font-black text-slate-900" x-text="selectedPermissionIds.length"></span>
                                </p>
                            </div>

                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                Al guardar, este usuario recibirá el rol seleccionado y la lista de permisos se sincronizará sobre ese rol.
                            </div>
                        </div>
                    </section>

                    <section class="rounded-[1.75rem] border border-slate-200 bg-white p-5">
                        <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Permisos</p>
                                <h4 class="mt-1 text-xl font-black text-slate-900">Lista completa</h4>
                            </div>
                            <input
                                x-model="permissionsSearch"
                                type="text"
                                placeholder="Filtrar permisos"
                                class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#173C4C] focus:bg-white"
                            >
                        </div>

                        <div class="mt-5 grid max-h-[24rem] gap-3 overflow-y-auto pr-2 md:grid-cols-2">
                            <template x-for="permiso in filteredPermissions" :key="permiso.id">
                                <label class="flex cursor-pointer items-start gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 transition hover:border-[#173C4C] hover:bg-white">
                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        :value="permiso.id"
                                        x-model="selectedPermissionIds"
                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-[#173C4C] focus:ring-[#173C4C]"
                                    >
                                    <span class="block">
                                        <span class="block text-sm font-black text-slate-800" x-text="permiso.sitio"></span>
                                        <span class="mt-1 block text-xs font-semibold text-slate-500" x-text="permiso.ruta"></span>
                                    </span>
                                </label>
                            </template>
                        </div>
                    </section>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="button" @click="closeModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-600">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-2xl bg-[#173C4C] px-5 py-3 text-sm font-black text-white shadow-lg transition hover:bg-[#07142B]">
                        Guardar accesos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('accessManager', (config) => ({
            usuarios: config.usuarios,
            roles: config.roles,
            permisos: config.permisos,
            accessBaseUrl: config.accessBaseUrl,
            search: '',
            filterRole: '',
            filterStatus: '',
            modalOpen: false,
            selectedUser: {},
            selectedRoleId: '',
            selectedPermissionIds: [],
            permissionsSearch: '',
            formAction: '',

            get usuariosActivos() {
                return this.usuarios.filter(usuario => usuario.status === 1).length;
            },
            get filteredUsuarios() {
                return this.usuarios.filter(usuario => {
                    const term = this.search.toLowerCase();
                    const matchesSearch = !term
                        || usuario.nombre.toLowerCase().includes(term)
                        || usuario.email.toLowerCase().includes(term);

                    const matchesRole = this.filterRole === ''
                        || usuario.role_name === this.filterRole;

                    const matchesStatus = this.filterStatus === ''
                        || (this.filterStatus === 'activo' && usuario.status === 1)
                        || (this.filterStatus === 'inactivo' && usuario.status === 0);

                    return matchesSearch && matchesRole && matchesStatus;
                });
            },
            get filteredPermissions() {
                const term = this.permissionsSearch.toLowerCase();

                return this.permisos.filter(permiso => {
                    return !term
                        || permiso.sitio.toLowerCase().includes(term)
                        || permiso.ruta.toLowerCase().includes(term);
                });
            },
            openAccessModal(usuario) {
                this.selectedUser = usuario;
                this.selectedRoleId = usuario.role_id ? String(usuario.role_id) : '';
                this.selectedPermissionIds = usuario.permission_ids.map(id => String(id));
                this.formAction = `${this.accessBaseUrl}/${usuario.id}/accesos`;
                this.permissionsSearch = '';
                this.modalOpen = true;
                document.body.style.overflow = 'hidden';
            },
            closeModal() {
                this.modalOpen = false;
                document.body.style.overflow = '';
                this.selectedUser = {};
                this.selectedRoleId = '';
                this.selectedPermissionIds = [];
                this.permissionsSearch = '';
                this.formAction = '';
            },
            applyRolePermissions() {
                const role = this.roles.find(item => String(item.id) === String(this.selectedRoleId));
                this.selectedPermissionIds = role ? role.permission_ids.map(id => String(id)) : [];
            },
        }));
    });
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
