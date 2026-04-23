@extends('layouts.principal')

@section('content')

<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4">

    {{-- ENCABEZADO --}}
    <div class="max-w-7xl mx-auto">
        <div class="mb-8 flex flex-col md:flex-row items-start md:items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="p-4 bg-[#0C4B54] rounded-xl shadow-lg">
                    <i class="fas fa-file-alt text-white text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-4xl font-extrabold text-gray-900">Secuencias Didácticas</h1>
                    <p class="text-gray-600 mt-1">Gestiona tus secuencias de forma eficiente</p>
                </div>
            </div>

            <a href="{{ route('secuencias.create') }}" class="bg-[#F59E0B] text-white px-6 py-3 rounded-xl shadow-lg hover:bg-[#e0900a] transition-all duration-300 flex items-center gap-2 font-semibold text-base mt-4 md:mt-0">
                <i class="fas fa-plus"></i> Nueva Secuencia
            </a>
        </div>

        {{-- RESUMEN (KPIs) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- Total --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 font-medium">Total de Secuencias</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $total }}</p>
                    </div>
                    <i class="fas fa-chart-bar text-blue-500 text-4xl opacity-20"></i>
                </div>
            </div>

            {{-- Activas --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 font-medium">Activas</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $activas }}</p>
                    </div>
                    <i class="fas fa-check-circle text-green-500 text-4xl opacity-20"></i>
                </div>
            </div>

            {{-- Borradores --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 font-medium">Borradores</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $inactivas }}</p>
                    </div>
                    <i class="fas fa-file-alt text-yellow-500 text-4xl opacity-20"></i>
                </div>
            </div>
        </div>

        {{-- FILTROS Y BÚSQUEDA --}}
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <form action="{{ route('secuencias.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                {{-- Búsqueda --}}
                <div class="flex-1">
                    <input type="text" name="buscar" placeholder="Buscar por asignatura..." value="{{ request('buscar') }}"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-[#0C4B54] focus:ring-2 focus:ring-[#0C4B54] focus:ring-opacity-20 outline-none transition">
                </div>

                {{-- Filtro Estado --}}
                <div class="w-full md:w-48">
                    <select name="estado" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-[#0C4B54] focus:ring-2 focus:ring-[#0C4B54] focus:ring-opacity-20 outline-none transition">
                        <option value="">-- Todos los Estados --</option>
                        <option value="activo" @selected(request('estado') === 'activo')>Activas</option>
                        <option value="borrador" @selected(request('estado') === 'borrador')>Borradores</option>
                    </select>
                </div>

                {{-- Ordenamiento --}}
                <div class="w-full md:w-48">
                    <select name="orden" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-[#0C4B54] focus:ring-2 focus:ring-[#0C4B54] focus:ring-opacity-20 outline-none transition">
                        <option value="">-- Ordenar Por --</option>
                        <option value="nombre_asc" @selected(request('orden') === 'nombre_asc')>Nombre (A-Z)</option>
                        <option value="nombre_desc" @selected(request('orden') === 'nombre_desc')>Nombre (Z-A)</option>
                        <option value="activo" @selected(request('orden') === 'activo')>Estado</option>
                    </select>
                </div>

                {{-- Botón Buscar --}}
                <button type="submit" class="px-6 py-3 bg-[#0C4B54] text-white rounded-xl hover:bg-[#093D45] transition-all duration-300 font-semibold flex items-center gap-2">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </form>
        </div>

        {{-- TABLA DE SECUENCIAS --}}
        @if($secuencias->count() > 0)
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-[#0C4B54] to-[#0a3d48]">
                        <tr>
                            <th class="px-6 py-4 text-left text-white font-semibold">Asignatura</th>
                            <th class="px-6 py-4 text-left text-white font-semibold">Carrera</th>
                            <th class="px-6 py-4 text-left text-white font-semibold">Estado</th>
                            <th class="px-6 py-4 text-left text-white font-semibold">Fecha Creación</th>
                            <th class="px-6 py-4 text-center text-white font-semibold">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($secuencias as $secuencia)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            {{-- Asignatura --}}
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $secuencia->caratula->asignatura ?? 'N/A' }}</div>
                                <p class="text-sm text-gray-600">{{ $secuencia->caratula->docente ?? 'Sin Docente' }}</p>
                            </td>

                            {{-- Carrera --}}
                            <td class="px-6 py-4">
                                <span class="text-gray-700">{{ $secuencia->caratula->carrera ?? 'N/A' }}</span>
                            </td>

                            {{-- Estado --}}
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold
                                    @if($secuencia->estatus === 'activo')
                                        bg-green-100 text-green-800
                                    @elseif($secuencia->estatus === 'borrador')
                                        bg-yellow-100 text-yellow-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif
                                ">
                                    {{ ucfirst($secuencia->estatus) }}
                                </span>
                            </td>

                            {{-- Fecha --}}
                            <td class="px-6 py-4">
                                <span class="text-gray-700">{{ $secuencia->created_at->format('d/m/Y') }}</span>
                            </td>

                            {{-- Acciones --}}
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-3">
                                    <a href="{{ route('secuencias.edit', $secuencia->id) }}"
                                        class="px-3 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 transition-colors text-sm font-semibold flex items-center gap-1"
                                        title="Editar">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>

                                    <a href="{{ route('secuencias.show', $secuencia->id) }}"
                                        class="px-3 py-2 rounded-lg bg-slate-700 text-white hover:bg-slate-800 transition-colors text-sm font-semibold flex items-center gap-1"
                                        title="Panel">
                                        <i class="fas fa-columns"></i> Panel
                                    </a>

                                    <a href="{{ route('secuencias.editor', $secuencia->id) }}"
                                        class="px-3 py-2 rounded-lg bg-indigo-500 text-white hover:bg-indigo-600 transition-colors text-sm font-semibold flex items-center gap-1"
                                        title="Editor OCR">
                                        <i class="fas fa-pen-to-square"></i> Editor OCR
                                    </a>

                                    <a href="{{ route('secuencias.exportWord', $secuencia->id) }}"
                                        class="px-3 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors text-sm font-semibold flex items-center gap-1"
                                        title="Exportar Word">
                                        <i class="fas fa-file-word"></i> Word
                                    </a>

                                    <a href="{{ route('secuencias.show', $secuencia->id) }}#dictamen"
                                        class="px-3 py-2 rounded-lg bg-sky-600 text-white hover:bg-sky-700 transition-colors text-sm font-semibold flex items-center gap-1"
                                        title="Emitir dictamen">
                                        <i class="fas fa-file-signature"></i> Dictamen
                                    </a>

                                    <a href="{{ route('secuencias.show', $secuencia->id) }}"
                                        class="px-3 py-2 rounded-lg bg-gray-500 text-white hover:bg-gray-600 transition-colors text-sm font-semibold flex items-center gap-1"
                                        title="Ver">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>

                                    @if($secuencia->estatus === 'borrador')
                                    <form action="{{ route('secuencias.updateStatus', $secuencia->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <input type="hidden" name="estatus" value="activo">
                                        <button type="submit" class="px-3 py-2 rounded-lg bg-green-500 text-white hover:bg-green-600 transition-colors text-sm font-semibold"
                                            title="Activar">
                                            <i class="fas fa-check"></i> Activar
                                        </button>
                                    </form>
                                    @else
                                    <form action="{{ route('secuencias.cambiarEstado', $secuencia->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="0">
                                        <input type="hidden" name="motivo" value="Desactivado desde listado">
                                        <button type="submit" class="px-3 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600 transition-colors text-sm font-semibold"
                                            title="Desactivar">
                                            <i class="fas fa-ban"></i> Desactivar
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="mt-8">
            {{ $secuencias->links() }}
        </div>

        @else
        {{-- SIN RESULTADOS --}}
        <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">No hay secuencias</h3>
            <p class="text-gray-600 mb-6">Comienza creando tu primera secuencia didáctica.</p>
            <a href="{{ route('secuencias.create') }}" class="inline-block px-6 py-3 bg-[#0C4B54] text-white rounded-xl hover:bg-[#093D45] transition-all duration-300 font-semibold">
                <i class="fas fa-plus"></i> Crear Primera Secuencia
            </a>
        </div>
        @endif

    </div>

</div>

@endsection
