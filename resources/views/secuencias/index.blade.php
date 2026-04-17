@extends('layouts.principal')

@section('content')

{{-- 
    -----------------------------------------------------------------------------------------------------------
    PANEL DE GESTIÓN DE SECUENCIAS ACADÉMICAS
    Reemplaza el dashboard principal con una vista de administración detallada.
    Colores principales: #0C4B54 (Azul Primario), #F59E0B (Naranja Acento), #10B981 (Verde).
    -----------------------------------------------------------------------------------------------------------
--}}

{{-- Wrapper principal con Alpine.js para control de modales --}}
<div 
    x-data="{ openModal: false }"
    class="w-full p-8 space-y-8 min-h-screen bg-gray-50"
>

    {{-- TÍTULO Y BARRA DE ACCIONES SUPERIORES --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <div>
            <h1 class="text-4xl font-extrabold text-[#0C4B54] border-l-4 border-[#0C4B54] pl-3">
                Gestión de Secuencias
            </h1>
            <p class="text-gray-500 mt-1 text-base">Administración centralizada de todas las secuencias académicas del sistema.</p>
        </div>

        <div class="flex items-center gap-3 mt-4 sm:mt-0">
            
            <!-- Botón Gráficas -->
            <button class="px-4 py-2 rounded-xl bg-[#0C4B54] text-white shadow hover:bg-[#103a42] transition flex items-center gap-2 text-sm font-semibold">
                <i class="fas fa-chart-bar"></i> Ver Estadísticas
            </button>

            <!-- Botón Imprimir -->
            <button class="px-4 py-2 rounded-xl bg-gray-400 text-white shadow hover:bg-gray-500 transition flex items-center gap-2 text-sm font-semibold">
                <i class="fas fa-print"></i> Imprimir Reporte
            </button>

            <!-- Botón Crear -->
            @if(Auth::user()->hasPermission('secuencias.createView'))
                <a href="{{ route('secuencias.createView') }}">
                    <button 
                        class="px-5 py-2.5 rounded-xl bg-[#F59E0B] text-white font-semibold shadow-lg hover:bg-[#e0900a] transition text-sm"
                    >
                        <i class="fas fa-plus-circle mr-1"></i> Nueva Secuencia
                    </button>
                </a>
            @endif
        </div>
    </div>


    {{-- ****************************************************** --}}
    {{-- WIDGETS DE RESUMEN Y FILTROS EN 3 COLUMNAS --}}
    {{-- ****************************************************** --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- WIDGET 1: ESTADO DE SECUENCIAS --}}
        <div class="bg-white p-6 rounded-2xl shadow-xl border-t-4 border-[#0C4B54]">
            <h2 class="text-xl font-bold text-gray-700 mb-4 border-b pb-2">Resumen Rápido</h2>
            <div class="space-y-3">
                
                <div class="flex justify-between items-center text-gray-800 border-b pb-2">
                    <span class="font-semibold flex items-center"><i class="fas fa-list-ul mr-2 text-[#0C4B54]"></i> Total Registradas</span>
                    <span class="text-2xl font-extrabold text-[#0C4B54]">158</span>
                </div>

                <div class="flex justify-between items-center text-gray-800 border-b pb-2">
                    <span class="font-semibold flex items-center"><i class="fas fa-toggle-on mr-2 text-[#10B981]"></i> Secuencias Activas</span>
                    <span class="text-2xl font-extrabold text-[#10B981]">145</span>
                </div>

                <div class="flex justify-between items-center text-gray-800">
                    <span class="font-semibold flex items-center"><i class="fas fa-toggle-off mr-2 text-red-500"></i> Secuencias Inactivas</span>
                    <span class="text-2xl font-extrabold text-red-500">13</span>
                </div>
            </div>
        </div>

        {{-- WIDGET 2: FILTROS DE BÚSQUEDA AVANZADA --}}
        <div class="bg-white p-6 rounded-2xl shadow-xl border-t-4 border-[#F59E0B]">
            <h2 class="text-xl font-bold text-gray-700 mb-4 border-b pb-2">Filtros de Búsqueda</h2>
            <div class="space-y-4">
                 
                 <!-- Buscador -->
                <div>
                    <label class="text-sm font-semibold text-gray-700">Buscar por nombre</label>
                    <input 
                        type="text"
                        placeholder="Escribe el nombre o ID..."
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 outline-none focus:ring-2 focus:ring-[#F59E0B] transition"
                    >
                </div>

                <!-- Estado -->
                <div>
                    <label class="text-sm font-semibold text-gray-700">Filtrar por Estado</label>
                    <select class="w-full px-3 py-2 rounded-lg border border-gray-300 outline-none focus:ring-2 focus:ring-[#F59E0B] transition">
                        <option value="">Mostrar todos los estados</option>
                        <option value="1">Activas (Producción)</option>
                        <option value="0">Inactivas (Borrador)</option>
                    </select>
                </div>
            </div>
        </div>
        
        {{-- WIDGET 3: ORDENAR Y APLICAR --}}
        <div class="bg-white p-6 rounded-2xl shadow-xl border-t-4 border-[#10B981]">
            <h2 class="text-xl font-bold text-gray-700 mb-4 border-b pb-2">Ordenamiento y Acciones</h2>
            <div class="space-y-4">
                
                <!-- Orden -->
                <div>
                    <label class="text-sm font-semibold text-gray-700">Criterio de Ordenamiento</label>
                    <select class="w-full px-3 py-2 rounded-lg border border-gray-300 outline-none focus:ring-2 focus:ring-[#10B981] transition">
                        <option value="pred">Más reciente</option>
                        <option value="nombre_asc">Nombre A-Z</option>
                        <option value="nombre_desc">Nombre Z-A</option>
                        <option value="activo">Activas primero</option>
                    </select>
                </div>

                <!-- Botón Aplicar -->
                <div class="flex items-end pt-2">
                    <button class="w-full py-3 rounded-xl bg-[#10B981] text-white font-bold shadow-md hover:bg-[#0e9e70] transition">
                        <i class="fas fa-filter mr-2"></i> Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    
    {{-- ****************************************************** --}}
    {{-- TABLA PRINCIPAL DE SECUENCIAS --}}
    {{-- ****************************************************** --}}
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">

        <!-- Encabezado de la Tarjeta de Tabla -->
        <div class="flex items-center gap-4 p-6 border-b border-gray-100 bg-gray-50">
            <div class="w-12 h-12 rounded-full bg-[#0C4B54] flex items-center justify-center text-white text-xl shadow-md">
                <i class="fas fa-clipboard-list"></i>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-[#0C4B54]">Listado General de Secuencias</h2>
                <p class="text-gray-500 text-sm">Organiza, edita y controla tus secuencias activas.</p>
            </div>
        </div>


        <!-- TABLA -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-[#0C4B54] text-white text-xs uppercase tracking-wider">
                        <th class="p-3 text-left w-[5%]">ID</th>
                        <th class="p-3 text-left w-[40%]">Nombre de la Secuencia</th>
                        <th class="p-3 text-left w-[15%]">Fecha Creación</th>
                        <th class="p-3 text-left w-[15%]">Estado</th>
                        <th class="p-3 text-center w-[25%]">Acciones</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">
                    {{-- Simulando datos de $secuencias --}}
                    @php
                        // Datos de prueba simulados
                        $secuencias = [
                            (object)['id' => 101, 'nombre' => 'Matemáticas Fundamentales II - Álgebra Lineal', 'activo' => true, 'created_at' => '2024-08-15'],
                            (object)['id' => 102, 'nombre' => 'Introducción a la Programación Orientada a Objetos', 'activo' => true, 'created_at' => '2024-08-20'],
                            (object)['id' => 103, 'nombre' => 'Historia del Arte Clásico y Barroco', 'activo' => false, 'created_at' => '2024-09-01'],
                            (object)['id' => 104, 'nombre' => 'Laboratorio de Química Orgánica Avanzada', 'activo' => true, 'created_at' => '2024-09-10'],
                        ];
                    @endphp
                    
                    @forelse($secuencias as $item)
                    
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="p-3 font-medium text-gray-500">{{ $item->id }}</td>

                        <td class="p-3 font-semibold text-gray-800">{{ $item->nombre }}</td>
                        
                        <td class="p-3 text-sm text-gray-600">{{ $item->created_at }}</td>


                        <!-- Estado -->
                        <td class="p-3">
                            <span class="px-3 py-1 text-xs font-bold rounded-full
                                {{ $item->activo 
                                    ? 'bg-[#10B981]/20 text-[#0C4B54]' // Verde Acento
                                    : 'bg-red-500/20 text-red-700' }}">
                                {{ $item->activo ? 'ACTIVO' : 'BORRADOR' }}
                            </span>
                        </td>

                        <td class="p-3 flex gap-2 justify-center">

                            <!-- Ver Detalle (Simulado) -->
                            <button
                                onclick="alert('Visualizando detalles de la Secuencia {{ $item->id }}')"
                                class="px-3 py-1.5 text-xs rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 transition font-medium"
                            >
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            <!-- Editar -->
                            <a 
                                href="#"
                                class="px-3 py-1.5 text-xs rounded-lg bg-[#0C4B54] text-white hover:bg-[#103a42] transition font-medium"
                            >
                                Editar
                            </a>

                            <!-- Borrar -->
                            <form 
                                action="#" 
                                method="POST"
                                onsubmit="return confirm('¿Estás seguro de ELIMINAR la secuencia ID {{ $item->id }}? Esta acción es irreversible.')"
                            >
                                @csrf
                                @method('DELETE')

                                <button 
                                    type="submit"
                                    class="px-3 py-1.5 text-xs rounded-lg bg-red-600 text-white hover:bg-red-700 transition font-medium"
                                >
                                    <i class="fas fa-trash-alt"></i> Borrar
                                </button>
                            </form>

                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-600 font-semibold bg-gray-50">
                            <i class="fas fa-exclamation-triangle text-xl text-yellow-500 mr-2"></i> No hay secuencias registradas que coincidan con los filtros.
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
        
        {{-- Footer de la Tabla/Paginación Simulado --}}
        <div class="p-4 flex justify-between items-center text-sm text-gray-600 border-t bg-white">
            <span>Mostrando 1 a 4 de 158 secuencias totales.</span>
            <div class="flex gap-2">
                <button class="px-3 py-1 border rounded-md hover:bg-gray-100 disabled:opacity-50" disabled>&larr; Anterior</button>
                <button class="px-3 py-1 border rounded-md hover:bg-gray-100">Siguiente &rarr;</button>
            </div>
        </div>

    </div>


    {{-- ****************************************************** --}}
    {{-- MODAL DE CREACIÓN DE SECUENCIA --}}
    {{-- ****************************************************** --}}
    <div 
        x-show="openModal"
        x-transition.opacity
        class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    >
        <div 
            @click.outside="openModal = false"
            x-transition
            class="bg-white border border-gray-200 w-full max-w-lg rounded-2xl shadow-2xl p-8"
        >
            <h2 class="text-3xl font-bold text-[#0C4B54] mb-6 border-b pb-2">Crear Nueva Secuencia</h2>

            <form action="#" method="POST">
                @csrf

                <div class="space-y-4">
                    <label class="block text-sm font-semibold text-gray-700">Nombre de la Secuencia</label>
                    <input 
                        type="text" 
                        name="nombre"
                        required
                        placeholder="Ej: Secuencia de Biología Molecular"
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#F59E0B] outline-none transition"
                    >

                    <label class="block text-sm font-semibold text-gray-700 pt-2">Descripción Breve (Opcional)</label>
                    <textarea
                        name="descripcion"
                        rows="3"
                        placeholder="Describe el objetivo y el alcance de esta secuencia."
                        class="w-full px-4 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-[#F59E0B] outline-none transition resize-none"
                    ></textarea>
                </div>

                <div class="flex justify-end gap-3 mt-8">
                    <button 
                        type="button"
                        @click="openModal = false"
                        class="px-6 py-2 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold transition shadow-md"
                    >
                        Cancelar
                    </button>

                    <button 
                        type="submit"
                        class="px-6 py-2 rounded-xl bg-[#F59E0B] hover:bg-[#e0900a] text-white font-semibold transition shadow-md"
                    >
                        <i class="fas fa-save mr-1"></i> Guardar Secuencia
                    </button>
                </div>

            </form>
        </div>
    </div>
    
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js" crossorigin="anonymous"></script>
{{-- Nota: Se asume que Alpine.js está cargado en layouts.principal o globalmente --}}

@endsection