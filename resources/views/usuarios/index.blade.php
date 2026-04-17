@extends('layouts.principal')
@section('content')

<div 
    x-data="{
        search: '',
        filtroRol: '',
        filtroEstado: ''}" 
    class="p-8 min-h-screen bg-gray-100">

    <!-- TÍTULO -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-[#0C4B54]">Usuarios</h1>
        <p class="text-gray-600">Administración de usuarios, roles y estado</p>
    </div>


    <!-- BARRA DE FILTROS -->
    <div class="bg-white p-6 rounded-2xl shadow mb-6 flex flex-wrap items-center gap-6">

        <!-- BUSCADOR -->
        <div class="flex-1">
            <label class="text-sm font-semibold text-gray-700">Buscar usuario</label>
            <input 
                x-model="search"
                type="text"
                placeholder="Nombre o correo..."
                class="w-full mt-1 px-4 py-2 border rounded-xl shadow-sm focus:ring-2 focus:ring-[#0C4B54]"
            >
        </div>

        <!-- FILTRO POR ROL -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Rol</label>
            <select 
                x-model="filtroRol"
                class="mt-1 px-4 py-2 border rounded-xl shadow-sm focus:ring-2 focus:ring-[#0C4B54]"
            >
                <option value="">Todos</option>
                @foreach($roles as $rol)
                    <option value="{{ strtolower($rol->nombre) }}">{{ $rol->nombre }}</option>
                @endforeach
            </select>
        </div>

        <!-- FILTRO POR ESTADO -->
        <div>
            <label class="text-sm font-semibold text-gray-700">Estado</label>
            <select 
                x-model="filtroEstado"
                class="mt-1 px-4 py-2 border rounded-xl shadow-sm focus:ring-2 focus:ring-[#0C4B54]"
            >
                <option value="">Todos</option>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>


        <div class="ml-auto">
         
        </div>

    </div>
@if(session('success'))
    <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800 font-semibold shadow">
        {{ session('success') }}
    </div>
@endif


    <!-- TABLA -->
    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <table class="min-w-full text-left">
            
            <thead class="bg-[#0C4B54] text-white">
                <tr>
                    <!-- Se quita temporalmente el id del usuario  -->
                    <!-- <th class="px-6 py-3">ID</th> -->
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
                    class="border-b hover:bg-gray-50 transition"
                    x-show="
                        ( '{{ strtolower($usuario->name) }}'.includes(search.toLowerCase()) ||
                          '{{ strtolower($usuario->email) }}'.includes(search.toLowerCase()) ) &&

                        ( filtroRol === '' || 
                        filtroRol === '{{ strtolower($usuario->first_role) }}') &&

                        ( filtroEstado === '' ||
                          filtroEstado === '{{ $usuario->activo ? 'activo' : 'inactivo' }}' )
                    "
                >

                    <!-- <td class="px-6 py-4">{{ $usuario->id }}</td> -->

                    <td class="px-6 py-4 font-semibold text-gray-800">
                        {{ $usuario->name }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}
                    </td>

                    <td class="px-6 py-4">{{ $usuario->email }}</td>

                    <td class="px-6 py-4">
                        @if(auth()->user()->hasPermission('usuarios.cambiarRol'))
    <form action="{{ route('usuarios.cambiarRol', $usuario->id) }}" method="POST">
        @csrf
        <select 
            name="role_id"
            onchange="this.form.submit()"
            class="px-3 py-1 rounded-lg border bg-white text-sm font-semibold cursor-pointer"
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
                        <span class="px-3 py-1 rounded-full text-sm font-semibold
                            {{ $usuario->activo ? 'bg-green-200 text-green-700' : 'bg-red-200 text-red-700' }}">

                            {{ $usuario->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>

                    <td class="px-6 py-4 text-right space-x-3">
                        <a href="#" class="text-blue-600 hover:underline font-semibold">Editar</a>

                        <form action="#" method="POST" class="inline">
                            @csrf
                            @method('DELETE')

                            <button 
                                class="text-red-600 hover:underline font-semibold"
                                onclick="return confirm('¿Eliminar usuario?')"
                            >
                                Eliminar
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