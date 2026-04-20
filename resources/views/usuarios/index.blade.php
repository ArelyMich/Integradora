@extends('layouts.principal')
@section('content')

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

                </tr>

                @endforeach

            </tbody>

        </table>
    </div>

</div>

@endsection