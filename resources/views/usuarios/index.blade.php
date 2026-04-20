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

<div 
    x-data="{
        search: '',
        filtroRol: '',
        filtroEstado: ''}" 
    class="p-6 min-h-screen bg-slate-50">

    <!-- TÍTULO -->
    <div class="mb-6 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#2FA69A] to-[#23877E] flex items-center justify-center shadow-lg">
            <i class="fas fa-users text-white text-lg"></i>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Usuarios</h1>
            <p class="text-slate-500 text-sm">Administración de usuarios, roles y estado</p>
        </div>
    </div>

    <div x-show="modalOpen" class="fixed inset-0 z-[80] flex items-center justify-center px-4" style="display: none;">
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

    <!-- BARRA DE FILTROS -->
    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-wrap items-end gap-4">

        <!-- BUSCADOR -->
        <div class="flex-1 min-w-[200px]">
            <label class="text-sm font-semibold text-slate-700">Buscar usuario</label>
            <div class="relative mt-1">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <i class="fas fa-search"></i>
                </span>
                <input 
                    x-model="search"
                    type="text"
                    placeholder="Nombre o correo..."
                    class="w-full pl-10 pr-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#2FA69A] focus:border-[#2FA69A]"
                >
            </div>
        </div>

        <!-- FILTRO POR ROL -->
        <div>
            <label class="text-sm font-semibold text-slate-700">Rol</label>
            <select 
                x-model="filtroRol"
                class="mt-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#2FA69A] focus:border-[#2FA69A]"
            >
                <option value="">Todos</option>
                @foreach($roles as $rol)
                    <option value="{{ strtolower($rol->nombre) }}">{{ $rol->nombre }}</option>
                @endforeach
            </select>
        </div>

        <!-- FILTRO POR ESTADO -->
        <div>
            <label class="text-sm font-semibold text-slate-700">Estado</label>
            <select 
                x-model="filtroEstado"
                class="mt-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-[#2FA69A] focus:border-[#2FA69A]"
            >
                <option value="">Todos</option>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>

    </div>
@if(session('success'))
    <div class="mb-4 p-4 rounded-lg bg-emerald-100 text-emerald-800 font-semibold border border-emerald-200">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
@endif


    <!-- TABLA -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="min-w-full text-left">
            
            <thead class="bg-gradient-to-r from-[#2FA69A] to-[#23877E] text-white">
                <tr>
                    <th class="px-6 py-3">Nombre</th>
                    <th class="px-6 py-3">Correo</th>
                    <th class="px-6 py-3">Rol</th>
                    <th class="px-6 py-3">Estado</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>

            <tbody>

                @foreach($usuarios as $usuario)

                <tr 
                    class="border-b border-slate-100 hover:bg-[#2FA69A]/5 transition"
                    x-show="
                        ( '{{ strtolower($usuario->name) }}'.includes(search.toLowerCase()) ||
                          '{{ strtolower($usuario->email) }}'.includes(search.toLowerCase()) ) &&

                        ( filtroRol === '' || 
                        filtroRol === '{{ strtolower($usuario->first_role) }}') &&

                        ( filtroEstado === '' ||
                          filtroEstado === '{{ $usuario->activo ? 'activo' : 'inactivo' }}' )
                    "
                >

                    <td class="px-6 py-4 font-semibold text-slate-800">
                        {{ $usuario->name }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}
                    </td>

                    <td class="px-6 py-4 text-slate-600">{{ $usuario->email }}</td>

                    <td class="px-6 py-4">
                        @if(auth()->user()->hasPermission('usuarios.cambiarRol'))
    <form action="{{ route('usuarios.cambiarRol', $usuario->id) }}" method="POST">
        @csrf
        <select 
            name="role_id"
            onchange="this.form.submit()"
            class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-sm font-medium cursor-pointer focus:ring-2 focus:ring-[#2FA69A] focus:border-[#2FA69A]"
        >
            @foreach($roles as $rol)
                <option 
                    value="{{ $rol->id }}"
                    @if($usuario->roles->first()?->id == $rol->id) selected @endif
                >
                    {{ $rol->nombre }}
                </option>
            @endforeach
        </select>
    </form>
    @endif
</td>


                    <!-- ESTADO -->
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            {{ $usuario->activo ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-red-100 text-red-700 border border-red-200' }}">

                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>

                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="#" class="text-[#2FA69A] hover:text-[#23877E] font-medium inline-flex items-center gap-1">
                            <i class="fas fa-edit"></i> Editar
                        </a>

                        <form action="#" method="POST" class="inline">
                            @csrf
                            @method('DELETE')

                            <button 
                                class="text-red-500 hover:text-red-700 font-medium inline-flex items-center gap-1"
                                onclick="return confirm('¿Eliminar usuario?')"
                            >
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </form>
                    </td>

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

    <div x-show="createModalOpen" class="fixed inset-0 z-[80] flex items-center justify-center px-4" style="display: none;">
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="closeCreateModal()"></div>
        <div class="relative z-10 w-full max-w-3xl rounded-[2rem] bg-white p-6 shadow-2xl">
            <div class="flex flex-col gap-3 border-b border-slate-100 pb-5 md:flex-row md:items-start md:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Nuevo usuario</p>
                    <h3 class="mt-1 text-3xl font-black text-slate-900">Crear cuenta desde panel</h3>
                    <p class="mt-2 text-sm text-slate-500">Completa todos los campos obligatorios y asigna un rol activo.</p>
                </div>
                <button type="button" @click="closeCreateModal()" class="self-start rounded-full bg-slate-100 px-3 py-2 text-slate-500 transition hover:bg-slate-200">×</button>
            </div>

            <form action="{{ route('usuarios.store') }}" method="POST" class="mt-6 space-y-5">
                @csrf

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-bold text-slate-600">Nombre</label>
                        <input
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            minlength="2"
                            maxlength="255"
                            pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#173C4C] focus:bg-white"
                        >
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-600">Apellido paterno</label>
                        <input
                            type="text"
                            name="apellido_paterno"
                            value="{{ old('apellido_paterno') }}"
                            required
                            minlength="2"
                            maxlength="255"
                            pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#173C4C] focus:bg-white"
                        >
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-bold text-slate-600">Apellido materno</label>
                        <input
                            type="text"
                            name="apellido_materno"
                            value="{{ old('apellido_materno') }}"
                            required
                            minlength="2"
                            maxlength="255"
                            pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#173C4C] focus:bg-white"
                        >
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-600">Username</label>
                        <input
                            type="text"
                            name="username"
                            value="{{ old('username') }}"
                            required
                            minlength="3"
                            maxlength="30"
                            pattern="^[A-Za-z0-9_.-]+$"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#173C4C] focus:bg-white"
                        >
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-bold text-slate-600">Correo electrónico</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#173C4C] focus:bg-white"
                        >
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-600">Rol</label>
                        <select
                            name="role_id"
                            required
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#173C4C] focus:bg-white"
                        >
                            <option value="">Seleccione un rol</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}" @selected(old('role_id') == $rol->id)>{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label class="text-sm font-bold text-slate-600">Contraseña</label>
                        <input
                            type="password"
                            name="password"
                            required
                            minlength="8"
                            pattern="^(?=.*[0-9])(?=.*[@$!%*#?&]).{8,}$"
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#173C4C] focus:bg-white"
                        >
                        <p class="mt-1 text-xs font-semibold text-slate-500">Minimo 8 caracteres, al menos un numero y un caracter especial.</p>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-600">Estado</label>
                        <select
                            name="status"
                            required
                            class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#173C4C] focus:bg-white"
                        >
                            <option value="1" @selected(old('status', '1') == '1')>Activo</option>
                            <option value="0" @selected(old('status') == '0')>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-600">Confirmar contraseña</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        required
                        minlength="8"
                        class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#173C4C] focus:bg-white"
                    >
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="button" @click="closeCreateModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-600">
                        Cancelar
                    </button>
                    <button type="submit" class="rounded-2xl bg-gradient-to-r from-[#173C4C] via-[#326D6C] to-[#568F7C] px-5 py-3 text-sm font-black text-white shadow-lg transition hover:-translate-y-0.5">
                        Crear usuario
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
            createModalOpen: false,
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
            openCreateModal() {
                this.createModalOpen = true;
                document.body.style.overflow = 'hidden';
            },
            closeCreateModal() {
                this.createModalOpen = false;
                document.body.style.overflow = '';
            },
            applyRolePermissions() {
                const role = this.roles.find(item => String(item.id) === String(this.selectedRoleId));
                this.selectedPermissionIds = role ? role.permission_ids.map(id => String(id)) : [];
            },
        }));
    });
</script>

@if ($errors->has('name') || $errors->has('apellido_paterno') || $errors->has('apellido_materno') || $errors->has('username') || $errors->has('email') || $errors->has('password') || $errors->has('password_confirmation') || $errors->has('role_id') || $errors->has('status'))
    <script>
        (function openCreateModalWithErrors() {
            const tryOpen = () => {
                const root = document.querySelector('[x-data^="accessManager"]');

                if (!root || !root.__x) {
                    requestAnimationFrame(tryOpen);
                    return;
                }

                root.__x.$data.createModalOpen = true;
                document.body.style.overflow = 'hidden';
            };

            if (window.Alpine) {
                requestAnimationFrame(tryOpen);
            } else {
                document.addEventListener('alpine:init', () => requestAnimationFrame(tryOpen), { once: true });
            }
        })();
    </script>
@endif

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection
