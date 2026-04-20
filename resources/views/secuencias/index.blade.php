@extends('layouts.principal')

@section('content')


{{-- 
    -----------------------------------------------------------------------------------------------------------
    PANEL DE GESTIÓN DE SECUENCIAS ACADÉMICAS
    Reemplaza el dashboard principal con una vista de administración detallada.
    Colores principales: #2FA69A (Verde Primario), #F59E0B (Naranja Acento), #10B981 (Verde).
    -----------------------------------------------------------------------------------------------------------
--}}

{{-- Wrapper principal con Alpine.js para control de modales --}}
<div 
    x-data="{ openModal: false }"
    class="w-full p-6 space-y-6 min-h-screen bg-slate-50"
>

    {{-- TÍTULO Y BARRA DE ACCIONES SUPERIORES --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#2FA69A] to-[#23877E] flex items-center justify-center shadow-lg">
                <i class="fas fa-clipboard-list text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Gestión de Secuencias</h1>
                <p class="text-slate-500 text-sm">Administración centralizada de todas las secuencias académicas del sistema.</p>
            </div>
        </div>

        <div class="flex items-center gap-3 mt-4 sm:mt-0">
            
            <!-- Botón Gráficas -->
            <button class="px-4 py-2 rounded-lg bg-[#2FA69A] text-white shadow-sm hover:bg-[#23877E] transition flex items-center gap-2 text-sm font-semibold">
                <i class="fas fa-chart-bar"></i> Ver Estadísticas
            </button>

            <!-- Botón Imprimir -->
            <button class="px-4 py-2 rounded-xl bg-slate-400 text-white shadow hover:bg-slate-500 transition flex items-center gap-2 text-sm font-semibold">
                <i class="fas fa-print"></i> Imprimir
            </button>

            <!-- Botón Crear -->
            @if(Auth::user()->hasPermission('secuencias.createView'))
                <a href="{{ route('secuencias.createView') }}">
                    <button 
                        class="px-5 py-2.5 rounded-xl bg-[#F59E0B] text-white font-semibold shadow-lg hover:bg-[#e0900a] transition text-sm"
                    >
                        <i class="fas fa-plus mr-1"></i> Nueva Secuencia
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
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 border-t-4 border-[#2FA69A]">
            <div class="flex items-center gap-2 mb-4">
                <i class="fas fa-chart-pie text-[#2FA69A]"></i>
                <h2 class="text-lg font-bold text-slate-800">Resumen Rápido</h2>
            </div>
            <div class="space-y-3">
                
                <div class="flex justify-between items-center text-slate-800 pb-2 border-b border-slate-100">
                    <span class="font-medium flex items-center"><i class="fas fa-list-ul mr-2 text-[#2FA69A]"></i> Total Registradas</span>
                    <span class="text-2xl font-bold text-[#2FA69A]">158</span>
                </div>

                <div class="flex justify-between items-center text-slate-800 pb-2 border-b border-slate-100">
                    <span class="font-medium flex items-center"><i class="fas fa-check-circle mr-2 text-emerald-500"></i> Activas</span>
                    <span class="text-2xl font-bold text-emerald-600">145</span>
                </div>

                <div class="flex justify-between items-center text-slate-800">
                    <span class="font-medium flex items-center"><i class="fas fa-clock mr-2 text-amber-500"></i> Pendientes</span>
                    <span class="text-2xl font-bold text-amber-600">13</span>
                </div>
            </div>
        </div>

        {{-- WIDGET 2: FILTROS DE BÚSQUEDA AVANZADA --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 border-t-4 border-[#2FA69A]">
            <div class="flex items-center gap-2 mb-4">
                <i class="fas fa-filter text-[#2FA69A]"></i>
                <h2 class="text-lg font-bold text-slate-800">Filtros de Búsqueda</h2>
            </div>
            <div class="space-y-4">
                 
                 <!-- Buscador -->
                <div>
                    <label class="text-sm font-semibold text-slate-700">Buscar por nombre</label>
                    <div class="relative mt-1">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input 
                            type="text"
                            placeholder="Escribe el nombre o ID..."
                            class="w-full pl-10 pr-3 py-2 rounded-lg border border-slate-300 bg-white outline-none focus:ring-2 focus:ring-[#2FA69A] focus:border-[#2FA69A] transition"
                        >
                    </div>
                </div>

                <!-- Estado -->
                <div>
                    <label class="text-sm font-semibold text-slate-700">Filtrar por Estado</label>
                    <select class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white outline-none focus:ring-2 focus:ring-[#2FA69A] focus:border-[#2FA69A] transition">
                        <option value="">Mostrar todos los estados</option>
                        <option value="1">Activas (Producción)</option>
                        <option value="0">Inactivas (Borrador)</option>
                    </select>
                </div>
            </div>
        </div>
        
        {{-- WIDGET 3: ORDENAR Y APLICAR --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 border-t-4 border-[#2FA69A]">
            <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">Ordenamiento y Acciones</h2>
            <div class="space-y-4">
                
                <!-- Orden -->
                <div>
                    <label class="text-sm font-semibold text-gray-700">Criterio de Ordenamiento</label>
                    <select class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white outline-none focus:ring-2 focus:ring-[#2FA69A] focus:border-[#2FA69A] transition">
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
    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">

        <!-- Encabezado de la Tarjeta de Tabla -->
        <div class="flex items-center gap-4 p-6 border-b border-gray-100 bg-gray-50">
            <div class="w-12 h-12 rounded-full bg-[#2FA69A] flex items-center justify-center text-white text-xl shadow-sm">
                <i class="fas fa-clipboard-list"></i>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-gray-800">Listado General de Secuencias</h2>
                <p class="text-gray-500 text-sm">Organiza, edita y controla tus secuencias activas.</p>
            </div>
        </div>


        <!-- TABLA -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-[#2FA69A] text-white text-xs uppercase tracking-wider">
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
                                    ? 'bg-[#10B981]/20 text-[#2FA69A]' // Verde Acento
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
                                class="px-3 py-1.5 text-xs rounded-lg bg-[#2FA69A] text-white hover:bg-[#23877E] transition font-medium"
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
            class="bg-white border border-gray-200 w-full max-w-lg rounded-2xl shadow-lg p-8"
        >
            <h2 class="text-3xl font-bold text-[#2FA69A] mb-6 border-b pb-2">Crear Nueva Secuencia</h2>

            <form action="#" method="POST">
                @csrf

                <div class="space-y-4">
                    <div>
                        <h1 class="text-3xl font-black text-slate-900 md:text-5xl">{{ $isReviewer ? 'Panel de Revisión de Secuencias' : 'Secuencias' }}</h1>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @if (!$isReviewer && Auth::user()->hasPermission('secuencias.createView'))
                        <a href="{{ route('secuencias.createView') }}" class="rounded-2xl bg-[#0C4B54] px-5 py-3 text-sm font-black text-white shadow-lg transition hover:-translate-y-0.5">
                            Nueva secuencia
                        </a>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-3">
                    <div class="rounded-3xl bg-slate-900 p-5 text-white shadow-lg">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/60">Registradas</p>
                        <p class="mt-3 text-4xl font-black" x-text="secuencias.length"></p>
                    </div>
                    <div class="rounded-3xl bg-emerald-50 p-5 text-emerald-700">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-emerald-500">Activas</p>
                        <p class="mt-3 text-4xl font-black" x-text="activasCount"></p>
                    </div>
                    <div class="rounded-3xl bg-rose-50 p-5 text-rose-700">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-rose-500">Inactivas</p>
                        <p class="mt-3 text-4xl font-black" x-text="inactivasCount"></p>
                    </div>
                    <div class="rounded-3xl bg-amber-50 p-5 text-amber-700">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-amber-500">Pendientes</p>
                        <p class="mt-3 text-4xl font-black" x-text="pendientesCount"></p>
                    </div>
                </div>
            </div>
        </section>

        @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700 shadow-sm">
            {{ session('error') }}
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


        <section class="grid gap-6">
            <div class="rounded-[2rem] bg-white p-6 shadow-xl shadow-slate-200/60">
                <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Tabla maestra</p>
                        <h2 class="mt-1 text-2xl font-black text-slate-900">Listado de secuencias</h2>
                    </div>
                    <div class="grid gap-3 md:grid-cols-3">
                        <input
                            x-model="search"
                            type="text"
                            placeholder="Buscar materia, carrera o docente"
                            class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#0C4B54] focus:bg-white">
                        <select x-model="filterStatus" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#0C4B54]">
                            <option value="all">Activo e inactivo</option>
                            <option value="active">Solo activas</option>
                            <option value="inactive">Solo inactivas</option>
                        </select>
                        <select x-model="filterAcademicStatus" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#0C4B54]">
                            <option value="all">Todos los estatus</option>
                            <option value="elaboracion">Elaboración</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="revision">Revisión</option>
                            <option value="correcciones">Correcciones</option>
                            <option value="entregada">Entregada</option>
                            <option value="aprobada">Aprobada</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 overflow-hidden rounded-[1.75rem] border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead class="bg-slate-900 text-xs font-black uppercase tracking-[0.2em] text-white">
                                <tr>
                                    <th class="px-4 py-4">Materia y carrera</th>
                                    <th class="px-4 py-4">Docente</th>
                                    <th class="px-4 py-4">Periodo</th>
                                    <th class="px-4 py-4">Estatus</th>
                                    <th class="px-4 py-4">Estado</th>
                                    <th class="px-4 py-4 text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                <template x-for="secuencia in filteredSecuencias" :key="secuencia.id">
                                    <tr class="align-top transition hover:bg-slate-50/80">
                                        <td class="px-4 py-4">
                                            <p class="text-sm font-black text-slate-900" x-text="secuencia.materia"></p>
                                            <p class="mt-1 text-xs font-semibold text-slate-500" x-text="secuencia.carrera"></p>
                                        </td>
                                        <td class="px-4 py-4 text-sm font-semibold text-slate-700" x-text="secuencia.docente"></td>
                                        <td class="px-4 py-4 text-sm text-slate-600" x-text="secuencia.periodo"></td>
                                        <td class="px-4 py-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-black uppercase tracking-[0.2em]" :class="academicBadge(secuencia.estatus)" x-text="labelAcademicStatus(secuencia.estatus)"></span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-black uppercase tracking-[0.2em]" :class="secuencia.status ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'" x-text="secuencia.status ? 'Activa' : 'Inactiva'"></span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex flex-wrap justify-center gap-2">
                                                <button
                                                    @click="openDetailModal(secuencia)"
                                                    class="rounded-2xl border border-slate-200 px-3 py-2 text-xs font-black text-slate-700 transition hover:border-[#0C4B54] hover:text-[#0C4B54]">
                                                    Ver
                                                </button>
                                                <a
                                                    :href="`${statusBaseUrl}/${secuencia.id}`"
                                                    class="rounded-2xl border border-cyan-200 bg-cyan-50 px-3 py-2 text-xs font-black text-cyan-700 transition hover:border-cyan-300 hover:bg-cyan-100">
                                                    Panel
                                                </a>
                                                <template x-if="!canReview">
                                                    <button
                                                        @click="openStatusModal(secuencia)"
                                                        class="rounded-2xl px-3 py-2 text-xs font-black transition"
                                                        :class="secuencia.status ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                                                        x-text="secuencia.status ? 'Desactivar' : 'Reactivar'"></button>
                                                </template>
                                                <template x-if="canReview">
                                                    <button
                                                        @click="openAcademicStatusModal(secuencia)"
                                                        class="rounded-2xl bg-sky-50 px-3 py-2 text-xs font-black text-sky-700 transition hover:bg-sky-100">
                                                        Emitir dictamen
                                                    </button>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div x-show="filteredSecuencias.length === 0" class="px-6 py-12 text-center text-sm font-semibold text-slate-500" style="display: none;">
                        No hay secuencias que coincidan con los filtros actuales.
                    </div>
                </div>
            </div>

            <aside class="rounded-[2rem] bg-white p-6 shadow-xl shadow-slate-200/60">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Historial</p>
                        <h2 class="mt-1 text-2xl font-black text-slate-900">Cambios recientes</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-500">{{ $historialCambios->count() }}</span>
                </div>

                <div class="mt-5 space-y-4">
                    @forelse ($historialCambios as $cambio)
                    <article class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-black text-slate-900">{{ $cambio->registro_nombre }}</p>
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-black uppercase {{ $cambio->estado_nuevo ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                {{ $cambio->accion }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">{{ $cambio->motivo }}</p>
                        <div class="mt-3 flex items-center justify-between gap-3 text-xs font-semibold text-slate-400">
                            <span>{{ $cambio->usuario?->name ?? 'Sistema' }}</span>
                            <span>{{ optional($cambio->fecha_movimiento)->format('d/m/Y H:i') }}</span>
                        </div>
                    </article>
                    @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center text-sm text-slate-500">
                        Aún no hay movimientos registrados para secuencias.
                    </div>
                    @endforelse
                </div>
            </aside>
        </section>
    </div>

    <div x-show="modalDetailOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="closeDetailModal()"></div>
        <div class="relative z-10 w-full max-w-3xl rounded-[2rem] bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Detalle</p>
                    <h3 class="mt-1 text-2xl font-black text-slate-900" x-text="selectedSecuencia.materia || 'Secuencia'"></h3>
                    <p class="mt-2 text-sm text-slate-500" x-text="selectedSecuencia.carrera || ''"></p>
                </div>
                <button @click="closeDetailModal()" class="rounded-full bg-slate-100 px-3 py-2 text-slate-500 transition hover:bg-slate-200">×</button>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Docente</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800" x-text="selectedSecuencia.docente"></p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Periodo</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800" x-text="selectedSecuencia.periodo"></p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Director</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800" x-text="selectedSecuencia.director"></p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Revisor</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800" x-text="selectedSecuencia.revisor"></p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Horas programadas</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800" x-text="selectedSecuencia.horas_programadas"></p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Fecha entrega</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800" x-text="selectedSecuencia.fecha_entrega || 'Sin captura'"></p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Estatus académico</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800" x-text="labelAcademicStatus(selectedSecuencia.estatus)"></p>
                </div>
                <div class="rounded-3xl bg-slate-50 p-4">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Estado operativo</p>
                    <p class="mt-2 text-sm font-semibold text-slate-800" x-text="selectedSecuencia.status ? 'Activa' : 'Inactiva'"></p>
                </div>
            </div>
        </div>
    </div>

    <div x-show="modalStatusOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="closeStatusModal()"></div>
        <div class="relative z-10 w-full max-w-xl rounded-[2rem] bg-white p-6 shadow-2xl">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl" :class="selectedStatusValue === 0 ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'">
                    <span class="text-2xl font-black">!</span>
                </div>
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Confirmación</p>
                    <h3 class="mt-1 text-2xl font-black text-slate-900" x-text="selectedStatusValue === 0 ? 'Desactivar secuencia' : 'Reactivar secuencia'"></h3>
                    <p class="mt-2 text-sm text-slate-500">
                        El registro permanece en base de datos. Solo cambia su disponibilidad y se guarda el historial del movimiento.
                    </p>
                </div>
            </div>

            <form :action="selectedStatusAction" method="POST" class="mt-6 space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" :value="selectedStatusValue">

                <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">
                    <span class="font-black text-slate-900">Secuencia:</span>
                    <span x-text="selectedSecuencia.materia ? selectedSecuencia.materia + ' - ' + selectedSecuencia.carrera : ''"></span>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-600">Motivo del cambio</label>
                    <textarea name="motivo" rows="4" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#0C4B54]" placeholder="Ej. Se desactiva porque el periodo ya cerró y no debe recibir más movimientos"></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="closeStatusModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-600">Cancelar</button>
                    <button type="submit" class="rounded-2xl px-5 py-3 text-sm font-black text-white shadow-lg" :class="selectedStatusValue === 0 ? 'bg-rose-600' : 'bg-emerald-600'">
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="modalAcademicStatusOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="closeAcademicStatusModal()"></div>
        <div class="relative z-10 w-full max-w-3xl rounded-[2rem] bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Dictamen</p>
                    <h3 class="mt-1 text-2xl font-black text-slate-900">Revisión de secuencia</h3>
                    <p class="mt-2 text-sm text-slate-500" x-text="selectedSecuencia.materia ? selectedSecuencia.materia + ' - ' + selectedSecuencia.carrera : ''"></p>
                </div>
                <button @click="closeAcademicStatusModal()" class="rounded-full bg-slate-100 px-3 py-2 text-slate-500 transition hover:bg-slate-200">×</button>
            </div>

            <form :action="selectedAcademicStatusAction" method="POST" class="mt-6 space-y-5">
                @csrf
                @method('PUT')

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-bold text-slate-600">Nuevo estatus académico</label>
                        <select name="estatus" x-model="selectedAcademicStatusValue" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-[#0C4B54]">
                            <option value="revision">Revisión</option>
                            <option value="correcciones">Correcciones</option>
                            <option value="aprobada">Aprobada</option>
                        </select>
                    </div>
                    <div class="rounded-3xl bg-slate-50 p-4">
                        <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Estatus actual</p>
                        <p class="mt-2 text-sm font-semibold text-slate-800" x-text="labelAcademicStatus(selectedSecuencia.estatus)"></p>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-600">Motivo del dictamen</label>
                    <textarea name="motivo" rows="4" x-model="reviewNote" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#0C4B54]" placeholder="Describe observaciones, correcciones o aprobacion final"></textarea>
                </div>


                <div class="flex justify-end gap-3">
                    <button type="button" @click="closeAcademicStatusModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-600">Cancelar</button>
                    <button type="submit" class="rounded-2xl bg-[#0C4B54] px-5 py-3 text-sm font-black text-white shadow-lg transition hover:bg-[#083840]">
                        Guardar dictamen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="modalTemplateOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="closeTemplateModal()"></div>
        <div class="relative z-10 w-full max-w-3xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Vista rápida</p>
                    <p class="text-sm font-semibold text-slate-700">Formato SECUENCIA DIDÁCTICA UTH</p>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="templatePdfUrl" target="_blank" class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-200">Abrir en pestaña</a>
                    <button type="button" @click="closeTemplateModal()" class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-600 transition hover:bg-slate-200">Cerrar</button>
                </div>
            </div>
            <div class="p-6 text-center">
                <p class="text-sm font-semibold text-slate-700">La vista embebida del PDF fue desactivada para evitar aperturas no deseadas del panel de impresión.</p>
                <p class="mt-2 text-xs text-slate-500">Usa "Abrir en pestaña" para ver el documento completo sin incrustarlo en el panel.</p>
                <div class="mt-4 flex justify-center">
                    <a :href="templatePdfUrl" target="_blank" class="rounded-2xl bg-slate-900 px-5 py-2.5 text-xs font-black text-white transition hover:bg-slate-700">Abrir formato</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('secuenciasDashboard', (config) => ({
            secuencias: config.secuencias,
            statusBaseUrl: config.statusBaseUrl,
            academicStatusBaseUrl: config.academicStatusBaseUrl,
            ocrUrl: config.ocrUrl,
            templatePdfUrl: config.templatePdfUrl,
            canReview: config.canReview,
            search: '',
            filterStatus: 'all',
            filterAcademicStatus: 'all',
            modalDetailOpen: false,
            modalStatusOpen: false,
            modalAcademicStatusOpen: false,
            modalTemplateOpen: false,
            selectedSecuencia: {},
            selectedStatusValue: 0,
            selectedStatusAction: '',
            selectedAcademicStatusValue: 'revision',
            selectedAcademicStatusAction: '',
            reviewNote: '',
            ocrFile: null,
            ocrData: {},
            ocrError: '',
            ocrLoading: false,

            get activasCount() {
                return this.secuencias.filter(secuencia => secuencia.status === 1).length;
            },
            get inactivasCount() {
                return this.secuencias.filter(secuencia => secuencia.status === 0).length;
            },
            get pendientesCount() {
                return this.secuencias.filter(secuencia => secuencia.estatus === 'pendiente').length;
            },
            get filteredSecuencias() {
                return this.secuencias.filter(secuencia => {
                    const term = this.search.toLowerCase();
                    const matchesSearch = !term ||
                        secuencia.materia.toLowerCase().includes(term) ||
                        secuencia.carrera.toLowerCase().includes(term) ||
                        secuencia.docente.toLowerCase().includes(term) ||
                        String(secuencia.id).includes(term);

                    const matchesStatus = this.filterStatus === 'all' ||
                        (this.filterStatus === 'active' && secuencia.status === 1) ||
                        (this.filterStatus === 'inactive' && secuencia.status === 0);

                    const matchesAcademicStatus = this.filterAcademicStatus === 'all' ||
                        secuencia.estatus === this.filterAcademicStatus;

                    return matchesSearch && matchesStatus && matchesAcademicStatus;
                });
            },
            academicBadge(estatus) {
                const palette = {
                    elaboracion: 'bg-slate-200 text-slate-700',
                    pendiente: 'bg-amber-100 text-amber-700',
                    revision: 'bg-sky-100 text-sky-700',
                    correcciones: 'bg-orange-100 text-orange-700',
                    entregada: 'bg-indigo-100 text-indigo-700',
                    aprobada: 'bg-emerald-100 text-emerald-700',
                };
                return palette[estatus] || 'bg-slate-200 text-slate-700';
            },
            labelAcademicStatus(estatus) {
                const labels = {
                    elaboracion: 'Elaboración',
                    pendiente: 'Pendiente',
                    revision: 'Revisión',
                    correcciones: 'Correcciones',
                    entregada: 'Entregada',
                    aprobada: 'Aprobada',
                };
                return labels[estatus] || estatus;
            },
            openDetailModal(secuencia) {
                this.selectedSecuencia = secuencia;
                this.modalDetailOpen = true;
                document.body.style.overflow = 'hidden';
            },
            closeDetailModal() {
                this.modalDetailOpen = false;
                document.body.style.overflow = '';
            },
            openStatusModal(secuencia) {
                this.selectedSecuencia = secuencia;
                this.selectedStatusValue = secuencia.status === 1 ? 0 : 1;
                this.selectedStatusAction = `${this.statusBaseUrl}/${secuencia.id}/estado`;
                this.modalStatusOpen = true;
                document.body.style.overflow = 'hidden';
            },
            closeStatusModal() {
                this.modalStatusOpen = false;
                document.body.style.overflow = '';
            },
            openAcademicStatusModal(secuencia) {
                this.selectedSecuencia = secuencia;
                this.selectedAcademicStatusValue = secuencia.estatus === 'aprobada' ? 'aprobada' : 'correcciones';
                this.selectedAcademicStatusAction = `${this.academicStatusBaseUrl}/${secuencia.id}/estatus-academico`;
                this.reviewNote = '';
                this.ocrFile = null;
                this.ocrData = {};
                this.ocrError = '';
                this.modalAcademicStatusOpen = true;
                document.body.style.overflow = 'hidden';
            },
            closeAcademicStatusModal() {
                this.modalAcademicStatusOpen = false;
                this.ocrLoading = false;
                document.body.style.overflow = '';
            },
            openTemplateModal() {
                this.modalTemplateOpen = true;
                document.body.style.overflow = 'hidden';
            },
            closeTemplateModal() {
                this.modalTemplateOpen = false;
                document.body.style.overflow = '';
            },
            setOcrFile(event) {
                const files = event?.target?.files || [];
                this.ocrFile = files.length ? files[0] : null;
                this.ocrError = '';
            },
            async runOcrFromFile() {
                if (!this.ocrFile || this.ocrLoading) {
                    return;
                }

                this.ocrLoading = true;
                this.ocrError = '';

                const formData = new FormData();
                formData.append('caratula_file', this.ocrFile);

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const response = await fetch(this.ocrUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf || '',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        this.ocrError = data.message || 'No se pudo procesar el archivo OCR.';
                        return;
                    }

                    this.ocrData = data.extracted_data || {};

                    if (this.ocrData.competencia) {
                        const block = `\n\nOCR competencia detectada:\n${this.ocrData.competencia}`;
                        this.reviewNote = `${this.reviewNote}${block}`.trim();
                    }
                } catch (error) {
                    this.ocrError = 'Ocurrio un error al procesar OCR.';
                } finally {
                    this.ocrLoading = false;
                }
            },
        }));
    });
</script>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endsection