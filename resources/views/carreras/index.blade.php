@extends('layouts.principal')
@section('content')

{{-- 
    VISTA DE GESTIÓN DE CARRERAS (FUSIÓN PREMIUM - FULL)
    ---------------------------------------------
    Concepto: Estructura Enterprise + UI Moderna.
    Features: Cards Pro, Modales independientes, Checkboxes visuales para docentes.
--}}

<div 
    x-data="carrerasApp({
        carreras: {{ Js::from($carreras->map(fn($c) => [
            'id' => $c->id,
            'nombre' => $c->nombre_carrera ?? $c->nombre ?? 'Sin Nombre',
            'descripcion' => $c->descripcion ?? 'Sin descripción disponible.',
            'director_nombre' => $c->director ? ($c->director->name . ' ' . $c->director->apellido_paterno) : 'Sin Asignar',
            'director_avatar' => $c->director ? strtoupper(substr($c->director->name, 0, 1)) : '?',
            'director_id' => $c->director ? $c->director->id : null,
            'has_director' => !empty($c->director),
            'created_at' => $c->created_at ? $c->created_at->format('d M, Y') : 'N/A'
        ])) }},
        directores: {{ Js::from($directores) }},
        docentes: {{ Js::from($docentes) }} 
    })"
    class="min-h-screen bg-gray-50 relative font-sans text-gray-700 pb-20 selection:bg-[#E8F549] selection:text-[#0C4B54]"
    x-cloak
>
    {{-- FONDO DECORATIVO SUTIL --}}
    <div class="absolute inset-0 z-0 opacity-[0.03] pointer-events-none" 
         style="background-image: radial-gradient(#0C4B54 1px, transparent 1px); background-size: 24px 24px;">
    </div>

    {{-- HEADER PRINCIPAL --}}
    <div class="relative z-10 max-w-7xl mx-auto pt-10 pb-8 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6 border-b border-gray-200 pb-6">
            
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wider">Administración</span>
                </div>
                <h1 class="text-4xl font-black text-[#0C4B54] tracking-tight">
                    Gestión de Carreras
                    <span class="text-[#E8F549] inline-block transform translate-y-1">.</span>
                </h1>
                <p class="text-gray-500 mt-2 text-lg max-w-2xl">Administra la oferta académica, supervisa asignaciones y controla el flujo directivo.</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button class="group px-4 py-2.5 bg-white border border-gray-200 text-gray-600 rounded-xl font-semibold text-sm flex items-center gap-2 hover:border-[#0C4B54] hover:text-[#0C4B54] transition-all shadow-sm">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-[#0C4B54]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                    <span>Estadísticas</span>
                </button>
                @if (Auth::User()->hasPermission('carreras.store'))
                    <button @click="openCreateModal()" class="px-6 py-2.5 bg-[#0C4B54] text-white rounded-xl font-bold text-sm flex items-center gap-2 hover:bg-[#0a3f47] transition shadow-lg shadow-[#0C4B54]/20 hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Nueva Carrera
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- PANEL DE CONTROL (STATS & FILTERS) --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <div class="lg:col-span-4 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-center relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-32 h-32 bg-gray-50 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
                
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-6 relative z-10">Resumen General</h3>
                <div class="space-y-5 relative z-10">
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <span class="font-medium text-gray-600">Total Carreras</span>
                        </div>
                        <span class="text-2xl font-black text-gray-800" x-text="carreras.length"></span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="font-medium text-gray-600">Asignadas</span>
                        </div>
                        <span class="text-2xl font-black text-gray-800" x-text="carreras.filter(c => c.has_director).length"></span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-8 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4">Filtrado Inteligente</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative group">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#0C4B54] transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </span>
                            <input 
                                x-model="search"
                                type="text" 
                                placeholder="Buscar carrera, director..." 
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0C4B54] focus:border-transparent focus:bg-white transition"
                            >
                        </div>
                        
                        <div class="relative">
                            <select 
                                x-model="filterStatus"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0C4B54] focus:border-transparent focus:bg-white transition cursor-pointer appearance-none"
                            >
                                <option value="all">Ver todas las carreras</option>
                                <option value="assigned">Con Director Asignado</option>
                                <option value="pending">Pendiente de Asignación</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between items-center">
                    <p class="text-xs text-gray-400">Mostrando resultados en tiempo real</p>
                    <button 
                        @click="search = ''; filterStatus = 'all'"
                        class="text-sm text-[#0C4B54] font-bold hover:underline decoration-2 underline-offset-4"
                    >
                        Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- GRID DE CARDS  --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <template x-for="item in filteredCarreras" :key="item.id">
                
                {{-- CARD INDIVIDUAL --}}
                <div class="group relative bg-white rounded-2xl shadow-sm hover:shadow-2xl hover:shadow-gray-200/50 transition-all duration-300 hover:-translate-y-1 overflow-hidden border border-gray-100 flex flex-col">
                    
                    <div class="h-24 w-full relative overflow-hidden" :class="item.has_director ? 'bg-[#0C4B54]' : 'bg-orange-500'">
                        <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                        <div class="absolute -bottom-1 left-0 right-0 h-6 bg-white rounded-t-[50%] scale-150"></div>
                    </div>

                    <div class="px-6 relative flex-1 flex flex-col">
                        
                        <div class="relative -mt-12 mb-4 self-center">
                            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl font-black shadow-lg border-4 border-white transition-transform group-hover:scale-105"
                                 :class="item.has_director ? 'bg-[#E8F549] text-[#0C4B54]' : 'bg-white text-gray-300'">
                                <span x-text="item.nombre.charAt(0)"></span>
                            </div>
                            <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full border-4 border-white flex items-center justify-center shadow-sm"
                                 :class="item.has_director ? 'bg-emerald-500' : 'bg-red-500'">
                                <svg x-show="item.has_director" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                                <svg x-show="!item.has_director" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 9v2m0 4h.01"/></svg>
                            </div>
                        </div>

                        <div class="text-center mb-6">
                            <h3 class="text-lg font-bold text-gray-900 leading-tight mb-1" x-text="item.nombre"></h3>
                            <p class="text-xs text-gray-400 font-mono" x-text="'ID REFERENCIA: #' + item.id"></p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100 relative group-hover:bg-[#F3F4F6] transition-colors">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 text-center">Director Académico</p>
                            
                            <template x-if="item.has_director">
                                <div class="flex flex-col items-center">
                                    <span class="text-sm font-bold text-gray-800 text-center" x-text="item.director_nombre"></span>
                                    <span class="text-[10px] text-emerald-600 font-semibold bg-emerald-100 px-2 py-0.5 rounded-full mt-1">Activo actualmente</span>
                                </div>
                            </template>

                            <template x-if="!item.has_director">
                                <div class="flex flex-col items-center py-1">
                                    <span class="text-sm font-medium text-gray-400 italic">Posición Vacante</span>
                                    <span class="text-[10px] text-orange-600 font-semibold bg-orange-100 px-2 py-0.5 rounded-full mt-1">Requiere acción</span>
                                </div>
                            </template>
                        </div>

                        <div class="mt-auto pb-6 grid grid-cols-2 gap-3">
                            @if (Auth::User()->hasPermission('carreras.asignarDirector'))
                                <button 
                                    @click="openAssignModal(item)"
                                    class="py-2.5 rounded-xl font-bold text-xs transition-all duration-300 flex items-center justify-center gap-1.5 shadow-sm hover:shadow-md border"
                                    :class="item.has_director 
                                        ? 'bg-white border-gray-200 text-gray-600 hover:border-[#0C4B54] hover:text-[#0C4B54]' 
                                        : 'bg-[#0C4B54] border-[#0C4B54] text-white hover:bg-[#093D45]'"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <span x-text="item.has_director ? 'Director' : 'Asignar'"></span>
                                </button>
                            @endif

                            <button 
                                @click="openProfesoresModal(item)" 
                                class="py-2.5 rounded-xl font-bold text-xs transition-all duration-300 flex items-center justify-center gap-1.5 bg-indigo-50 text-indigo-700 border border-transparent hover:bg-white hover:border-indigo-300 hover:shadow-md group/prof"
                            >
                                <svg class="w-4 h-4 text-indigo-400 group-hover/prof:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                <span>Profesores</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="filteredCarreras.length === 0" class="text-center py-20" style="display: none;">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-6 animate-pulse">
                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">No hay carreras visibles</h3>
            <p class="text-gray-500 mt-2 max-w-sm mx-auto">No pudimos encontrar coincidencias con los filtros actuales. Intenta una búsqueda diferente.</p>
            <button @click="search = ''; filterStatus = 'all'" class="mt-6 text-[#0C4B54] font-bold hover:underline">Restablecer todo</button>
        </div>
    </div>

    {{-- MODAL ASIGNAR DIRECTOR (TEAL) --}}
    <div x-show="modalAssignOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div x-show="modalAssignOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeAssignModal()"></div>
        
        <div x-show="modalAssignOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden relative z-10"
        >
            <div class="bg-gradient-to-r from-[#0C4B54] to-[#0F5E69] p-6 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="font-bold text-xl">Asignar Director</h3>
                    <p class="text-blue-100 text-sm mt-1" x-text="selectedCarreraNombre"></p>
                </div>
                <div class="absolute right-0 top-0 w-24 h-24 bg-white opacity-10 rounded-full -mr-10 -mt-10"></div>
            </div>

            <form action="{{ route('carreras.asignarDirector') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="carrera_id" :value="selectedCarreraId">
                
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Director Académico</label>
                    <select name="director_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0C4B54] outline-none transition font-medium text-gray-700">
                        <option value="" disabled selected>Seleccione un profesional...</option>
                        <template x-for="director in directores" :key="director.id">
                            <option :value="director.id" x-text="`${director.name} ${director.apellido_paterno} ${director.apellido_materno || ''}`"></option>
                        </template>
                    </select>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="closeAssignModal()" class="flex-1 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancelar</button>
                    <button type="submit" class="flex-1 py-3 bg-[#E8F549] text-[#0C4B54] font-bold rounded-xl hover:bg-[#dce940] shadow-md transition">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL ASIGNAR PROFESORES (INDIGO - CON CHECKBOXES VISUALES) --}}
    <div x-show="modalProfesoresOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div x-show="modalProfesoresOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeProfesoresModal()"></div>
        
        <div x-show="modalProfesoresOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden relative z-10"
        >
            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-6 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="font-bold text-xl">Claustro de Profesores</h3>
                    <p class="text-indigo-100 text-sm mt-1" x-text="selectedCarreraNombre"></p>
                </div>
                <div class="absolute left-0 bottom-0 w-24 h-24 bg-white opacity-10 rounded-full -ml-10 -mb-10"></div>
            </div>

            <form action="{{ route('carreras.asignarProfesores') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="carrera_id" :value="selectedCarreraId">
                
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Seleccionar Docentes</label>
                    <p class="text-xs text-gray-400 mb-3">Selecciona los profesores que formarán parte de esta carrera.</p>
                    
                    {{-- LISTA DE PROFESORES CON CHECKBOXES TIPO CARD --}}
                    <div class="max-h-60 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                        <template x-for="docente in docentes" :key="docente.id">
                            <label class="flex items-center p-3 rounded-xl border border-gray-200 hover:bg-indigo-50 hover:border-indigo-200 cursor-pointer transition-all group bg-white">
                                <div class="relative flex items-center">
                                    <input type="checkbox" name="profesores[]" :value="docente.id" 
                                           class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 border-gray-300 transition-shadow">
                                </div>
                                <div class="ml-3 flex-1">
                                    <span class="block text-sm font-bold text-gray-700 group-hover:text-indigo-800" 
                                          x-text="`${docente.name} ${docente.apellido_paterno} ${docente.apellido_materno || ''}`"></span>
                                    <span class="block text-[10px] text-gray-400 group-hover:text-indigo-500 mt-0.5" 
                                          x-text="docente.email || 'Docente Registrado'"></span>
                                </div>
                                <div class="text-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </label>
                        </template>
                        
                        {{-- Fallback si no hay docentes --}}
                        <div x-show="docentes.length === 0" class="text-center py-4 text-gray-400 text-sm italic">
                            No hay docentes registrados en el sistema.
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-2 border-t border-gray-50">
                    <button type="button" @click="closeProfesoresModal()" class="flex-1 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancelar</button>
                    <button type="submit" class="flex-1 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 shadow-md transition">Asignar Docentes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL CREAR CARRERA --}}
    <div x-show="modalCreateOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div x-show="modalCreateOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeCreateModal()"></div>
        
        <div x-show="modalCreateOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-8 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden relative z-10"
        >
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#E8F549] flex items-center justify-center text-[#0C4B54]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Nueva Carrera</h3>
                </div>
                <button @click="closeCreateModal()" class="text-gray-400 hover:text-red-500 transition"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>

            <div class="p-8">
                <form action="{{ route('carreras.store') }}" method="POST" class="space-y-5"> 
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nombre Oficial</label>
                        <input type="text" name="nombre_carrera" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0C4B54] focus:bg-white transition" placeholder="Ej. Licenciatura en Derecho">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Descripción</label>
                        <textarea name="descripcion" rows="3" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0C4B54] focus:bg-white transition" placeholder="Detalles del programa..."></textarea>
                    </div>
                    
                    <button type="submit" class="w-full py-4 bg-[#0C4B54] text-white font-bold rounded-xl shadow-lg hover:bg-[#093D45] transition flex items-center justify-center gap-2 group">
                        <span>Registrar Carrera</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('carrerasApp', (config) => ({
            carreras: config.carreras,
            directores: config.directores,
            docentes: config.docentes, // Nueva variable conectada
            
            search: '',
            filterStatus: 'all',
            
            // Estados de Modales
            modalAssignOpen: false,
            modalCreateOpen: false,
            modalProfesoresOpen: false,
            
            selectedCarreraId: null,
            selectedCarreraNombre: '',

            get filteredCarreras() {
                return this.carreras.filter(item => {
                    const searchLower = this.search.toLowerCase();
                    const directorName = item.director_nombre ? item.director_nombre.toLowerCase() : '';
                    const matchesSearch = item.nombre.toLowerCase().includes(searchLower) || directorName.includes(searchLower);
                    
                    let matchesStatus = true;
                    if (this.filterStatus === 'assigned') matchesStatus = item.has_director;
                    if (this.filterStatus === 'pending') matchesStatus = !item.has_director;

                    return matchesSearch && matchesStatus;
                });
            },

            // Modal Director
            openAssignModal(carrera) {
                this.selectedCarreraId = carrera.id;
                this.selectedCarreraNombre = carrera.nombre;
                this.modalAssignOpen = true;
                document.body.style.overflow = 'hidden';
            },
            closeAssignModal() {
                this.modalAssignOpen = false;
                document.body.style.overflow = '';
                setTimeout(() => { this.resetSelection() }, 300);
            },

            // Modal Profesores
            openProfesoresModal(carrera) {
                this.selectedCarreraId = carrera.id;
                this.selectedCarreraNombre = carrera.nombre;
                this.modalProfesoresOpen = true;
                document.body.style.overflow = 'hidden';
            },
            closeProfesoresModal() {
                this.modalProfesoresOpen = false;
                document.body.style.overflow = '';
                setTimeout(() => { this.resetSelection() }, 300);
            },

            // Modal Crear
            openCreateModal() {
                this.modalCreateOpen = true;
                document.body.style.overflow = 'hidden';
            },
            closeCreateModal() {
                this.modalCreateOpen = false;
                document.body.style.overflow = '';
            },

            resetSelection() {
                this.selectedCarreraId = null;
                this.selectedCarreraNombre = '';
            }
        }));
    });
</script>

<style>
    [x-cloak] { display: none !important; }
    
    /* Scrollbar fino y elegante para la lista de docentes */
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1; 
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8; 
    }
</style>

@endsection