@extends('layouts.principal')

@section('content')
<div
    x-data="carrerasDashboard({
        carreras: {{ Js::from($carreras->map(fn($c) => [
            'id' => $c->id,
            'nombre' => $c->nombre_carrera,
            'descripcion' => $c->descripcion ?: 'Sin descripción disponible.',
            'status' => (int) $c->status,
            'director_id' => $c->director?->id,
            'director_nombre' => $c->director ? trim($c->director->name . ' ' . $c->director->apellido_paterno) : 'Sin director asignado',
            'docentes_count' => (int) ($c->docentes_count ?? $c->docentes->count()),
            'docentes_ids' => $c->docentes->pluck('id')->values(),
            'fecha_creacion' => optional($c->fecha_creacion ?? $c->created_at)->format('d/m/Y'),
            'created_at' => optional($c->created_at)->format('d/m/Y H:i'),
        ])) }},
        directores: {{ Js::from($directores->map(fn($director) => [
            'id' => $director->id,
            'nombre' => trim($director->name . ' ' . $director->apellido_paterno . ' ' . ($director->apellido_materno ?? '')),
        ])) }},
        docentes: {{ Js::from($docentes->map(fn($docente) => [
            'id' => $docente->id,
            'nombre' => trim($docente->name . ' ' . $docente->apellido_paterno . ' ' . ($docente->apellido_materno ?? '')),
            'email' => $docente->email,
        ])) }},
        statusBaseUrl: '{{ url('/carreras') }}'
    })"
                class="min-h-screen bg-gray-50 relative font-sans text-gray-700 pb-20 selection:bg-[#E6F4F2] selection:text-[#2FA69A]"
                x-cloak
                >
                {{-- FONDO DECORATIVO SUTIL --}}
                <div class="absolute inset-0 z-0 opacity-[0.03] pointer-events-none"
                    style="background-image: radial-gradient(#2FA69A 1px, transparent 1px); background-size: 24px 24px;">
                </div>

                {{-- HEADER PRINCIPAL --}}
                <div class="relative z-10 max-w-7xl mx-auto pt-6 pb-6 px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6 border-b border-slate-200 pb-6">

                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#2FA69A] to-[#23877E] flex items-center justify-center shadow-lg">
                                    <i class="fas fa-graduation-cap text-white text-lg"></i>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-md bg-[#2FA69A]/10 text-[#2FA69A] text-xs font-bold uppercase tracking-wider">Administración</span>
                            </div>
                            <h1 class="text-3xl font-bold text-slate-800">
                                Gestión de Carreras
                            </h1>
                            <p class="text-slate-500 mt-2 text-base">Administra la oferta académica, supervisa asignaciones y controla el flujo directivo.</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <button class="group px-4 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-semibold text-sm flex items-center gap-2 hover:border-[#2FA69A] hover:text-[#2FA69A] transition-all shadow-sm">
                                <i class="fas fa-chart-bar text-slate-400 group-hover:text-[#2FA69A]"></i>
                                <span>Estadísticas</span>
                            </button>
                            @if (Auth::User()->hasPermission('carreras.store'))
                            <button @click="openCreateModal()" class="px-6 py-2.5 bg-[#2FA69A] text-white rounded-xl font-bold text-sm flex items-center gap-2 hover:bg-[#23877E] transition shadow-lg shadow-[#2FA69A]/20 hover:-translate-y-0.5">
                                <i class="fas fa-plus"></i>
                                Nueva Carrera
                            </button>
                            @endif
                        </div>
        </section>

        @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
            {{ session('success') }}
        </div>
        @endif

            {{-- PANEL DE CONTROL (STATS & FILTERS) --}}
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                    <div class="lg:col-span-4 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-center relative overflow-hidden group">
                        <div class="absolute right-0 top-0 w-32 h-32 bg-[#2FA69A]/5 rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>

                        <div class="flex items-center gap-2 mb-4 relative z-10">
                            <i class="fas fa-chart-pie text-[#2FA69A]"></i>
                            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Resumen General</h3>
                        </div>
                        <div class="space-y-4 relative z-10">
                            <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-[#2FA69A]/10 text-[#2FA69A] flex items-center justify-center">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <span class="font-medium text-slate-600">Total Carreras</span>
                                </div>
                                <span class="text-2xl font-bold text-slate-800" x-text="carreras.length"></span>
                            </div>

                            <div class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-50 transition">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <span class="font-medium text-slate-600">Asignadas</span>
                                </div>
                                <span class="text-2xl font-bold text-slate-800" x-text="carreras.filter(c => c.has_director).length"></span>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-8 bg-white rounded-2xl shadow-sm border border-slate-200 p-6 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-4">
                                <i class="fas fa-filter text-[#2FA69A]"></i>
                                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider">Filtrado Inteligente</h3>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="relative group">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-[#2FA69A] transition">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    </span>
                                    <input
                                        x-model="search"
                                        type="text"
                                        placeholder="Buscar carrera, director..."
                                        class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2FA69A] focus:border-transparent focus:bg-white transition">
                                </div>

                                <div class="relative">
                                    <select
                                        x-model="filterStatus"
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2FA69A] focus:border-transparent focus:bg-white transition cursor-pointer appearance-none">
                                        <option value="all">Ver todas las carreras</option>
                                        <option value="assigned">Con Director Asignado</option>
                                        <option value="pending">Pendiente de Asignación</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-100 flex justify-between items-center">
                            <p class="text-xs text-gray-400">Mostrando resultados en tiempo real</p>
                            <button
                                @click="search = ''; filterStatus = 'all'"
                                class="text-sm text-[#2FA69A] font-bold hover:underline decoration-2 underline-offset-4">
                                Limpiar Filtros
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                {{-- GRID DE CARDS  --}}
                <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <template x-for="item in filteredCarreras" :key="item.id">

                            {{-- CARD INDIVIDUAL --}}
                            <div class="group relative bg-white rounded-2xl shadow-sm hover:shadow-lg hover:shadow-gray-200/50 transition-all duration-300 hover:-translate-y-1 overflow-hidden border border-gray-100 flex flex-col">

                                <div class="h-24 w-full relative overflow-hidden" :class="item.has_director ? 'bg-[#2FA69A]' : 'bg-orange-500'">
                                    <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                                    <div class="absolute -bottom-1 left-0 right-0 h-6 bg-white rounded-t-[50%] scale-150"></div>
                                </div>
                                <div class="grid gap-3 md:grid-cols-3">
                                    <input
                                        x-model="search"
                                        type="text"
                                        placeholder="Buscar carrera o director"
                                        class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#0C4B54] focus:bg-white">
                                    <select x-model="filterStatus" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#0C4B54]">
                                        <option value="all">Todos los estados</option>
                                        <option value="active">Solo activas</option>
                                        <option value="inactive">Solo inactivas</option>
                                    </select>
                                    <select x-model="filterDirector" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#0C4B54]">
                                        <option value="all">Con y sin director</option>
                                        <option value="assigned">Con director</option>
                                        <option value="pending">Sin director</option>
                                    </select>
                                </div>
                            </div>

                            <div class="px-6 relative flex-1 flex flex-col">

                                <div class="relative -mt-12 mb-4 self-center">
                                    <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl font-black shadow-lg border-4 border-white transition-transform group-hover:scale-105"
                                        :class="item.has_director ? 'bg-[#E6F4F2] text-[#2FA69A]' : 'bg-white text-gray-300'">
                                        <span x-text="item.nombre.charAt(0)"></span>
                                    </div>
                                    <div class="absolute -bottom-2 -right-2 w-8 h-8 rounded-full border-4 border-white flex items-center justify-center shadow-sm"
                                        :class="item.has_director ? 'bg-emerald-500' : 'bg-red-500'">
                                        <svg x-show="item.has_director" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path d="M5 13l4 4L19 7" />
                                        </svg>
                                        <svg x-show="!item.has_director" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path d="M12 9v2m0 4h.01" />
                                        </svg>
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
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-black uppercase tracking-[0.2em]"
                                            :class="carrera.status ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                                            x-text="carrera.status ? 'Activa' : 'Inactiva'"></span>
                                </div>

                                <p class="mt-4 line-clamp-3 min-h-[4.5rem] text-sm text-slate-600" x-text="carrera.descripcion"></p>

                                <dl class="mt-5 space-y-3 rounded-2xl bg-white p-4 shadow-inner shadow-slate-100">
                                    <div class="flex items-center justify-between gap-3">
                                        <dt class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Director</dt>
                                        <dd class="text-sm font-semibold text-slate-700" x-text="carrera.director_nombre"></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <dt class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Docentes</dt>
                                        <dd class="text-sm font-semibold text-slate-700" x-text="carrera.docentes_count"></dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <dt class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Alta</dt>
                                        <dd class="text-sm font-semibold text-slate-700" x-text="carrera.fecha_creacion || 'Sin fecha'"></dd>
                                    </div>
                                </dl>

                                <div class="mt-auto pb-6 grid grid-cols-2 gap-3">
                                    @if (Auth::User()->hasPermission('carreras.asignarDirector'))
                                    <button
                                        @click="openAssignModal(item)"
                                        class="py-2.5 rounded-xl font-bold text-xs transition-all duration-300 flex items-center justify-center gap-1.5 shadow-sm hover:shadow-md border"
                                        :class="item.has_director 
                                        ? 'bg-white border-gray-200 text-gray-600 hover:border-[#2FA69A] hover:text-[#2FA69A]' 
                                        : 'bg-[#2FA69A] border-[#2FA69A] text-white hover:bg-[#23877E]'">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <span x-text="item.has_director ? 'Director' : 'Asignar'"></span>
                                    </button>
                                    @endif

                                    <button
                                        @click="openProfesoresModal(carrera)"
                                        class="rounded-2xl bg-slate-900 px-3 py-3 text-sm font-black text-white transition hover:bg-[#0C4B54]">
                                        Docentes
                                    </button>
                                </div>

                                <button
                                    @click="openStatusModal(carrera)"
                                    class="mt-3 w-full rounded-2xl px-4 py-3 text-sm font-black transition"
                                    :class="carrera.status ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                                    x-text="carrera.status ? 'Desactivar carrera' : 'Reactivar carrera'"></button>
                                </article>
                        </template>
                    </div>

                    <div x-show="filteredCarreras.length === 0" class="rounded-[1.75rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center text-slate-500" style="display: none;">
                        No hay carreras que coincidan con los filtros actuales.
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-900">No hay carreras visibles</h3>
                <p class="text-gray-500 mt-2 max-w-sm mx-auto">No pudimos encontrar coincidencias con los filtros actuales. Intenta una búsqueda diferente.</p>
                <button @click="search = ''; filterStatus = 'all'" class="mt-6 text-[#2FA69A] font-bold hover:underline">Restablecer todo</button>
            </div>
    </div>

    {{-- MODAL ASIGNAR DIRECTOR (TEAL) --}}
    <div x-show="modalAssignOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div x-show="modalAssignOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeAssignModal()"></div>

        <div x-show="modalAssignOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-8 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="bg-white rounded-2xl shadow-lg w-full max-w-md overflow-hidden relative z-10">
            <div class="bg-gradient-to-r from-[#2FA69A] to-[#2FA69A] p-6 text-white relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="font-bold text-xl">Asignar Director</h3>
                    <p class="text-blue-100 text-sm mt-1" x-text="selectedCarreraNombre"></p>
                </div>
                <button @click="closeAssignModal()" class="rounded-full bg-slate-100 px-3 py-2 text-slate-500 transition hover:bg-slate-200">×</button>
            </div>

            <form action="{{ route('carreras.asignarDirector') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                <input type="hidden" name="carrera_id" :value="selectedCarreraId">

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Director Académico</label>
                            <select name="director_id" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2FA69A] outline-none transition font-medium text-gray-700">
                                <option value="" disabled selected>Seleccione un profesional...</option>
                                <template x-for="director in directores" :key="director.id">
                                    <option :value="director.id" x-text="director.nombre" :selected="director.id === selectedDirectorId"></option>
                                </template>
                            </select>
                        </div>

                            <div class="flex gap-3">
                                <button type="button" @click="closeAssignModal()" class="flex-1 py-3 bg-white border border-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-50 transition">Cancelar</button>
                                <button type="submit" class="flex-1 py-3 bg-[#E6F4F2] text-[#2FA69A] font-bold rounded-xl hover:bg-[#dce940] shadow-md transition">Guardar</button>
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
                            class="bg-white rounded-2xl shadow-lg w-full max-w-lg overflow-hidden relative z-10">
                            <div class="bg-gradient-to-r from-[#2FA69A] to-[#23877E] p-6 text-white relative overflow-hidden">
                                <div class="relative z-10">
                                    <h3 class="font-bold text-xl">Claustro de Profesores</h3>
                                    <p class="text-indigo-100 text-sm mt-1" x-text="selectedCarreraNombre"></p>
                                </div>
                                <button @click="closeProfesoresModal()" class="rounded-full bg-slate-100 px-3 py-2 text-slate-500 transition hover:bg-slate-200">×</button>
                            </div>

                            <form action="{{ route('carreras.asignarProfesores') }}" method="POST" class="mt-6">
                                @csrf
                                <input type="hidden" name="carrera_id" :value="selectedCarreraId">

                                <div class="grid max-h-[22rem] gap-3 overflow-y-auto pr-2 md:grid-cols-2">
                                    <template x-for="docente in docentes" :key="docente.id">
                                        <label class="flex cursor-pointer items-start gap-3 rounded-3xl border border-slate-200 bg-slate-50 p-4 transition hover:border-[#0C4B54] hover:bg-white">
                                            <input type="checkbox" name="profesores[]" :value="docente.id" x-model="selectedDocentes" class="mt-1 h-4 w-4 rounded border-slate-300 text-[#0C4B54] focus:ring-[#0C4B54]">
                                            <span class="block">
                                                <span class="block text-sm font-black text-slate-800" x-text="docente.nombre"></span>
                                                <span class="mt-1 block text-xs text-slate-500" x-text="docente.email"></span>
                                            </span>
                                        </label>
                                    </template>
                                </div>

                                <div class="mt-6 flex justify-end gap-3">
                                    <button type="button" @click="closeProfesoresModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-600">Cancelar</button>
                                    <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white shadow-lg">Guardar docentes</button>
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
                                            class="bg-white rounded-2xl shadow-lg w-full max-w-lg overflow-hidden relative z-10">
                                            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-full bg-[#E6F4F2] flex items-center justify-center text-[#2FA69A]">
                                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                        </svg>
                                                    </div>
                                                    <h3 class="text-xl font-bold text-gray-800">Nueva Carrera</h3>
                                                </div>
                                                <button @click="closeCreateModal()" class="rounded-full bg-slate-100 px-3 py-2 text-slate-500 transition hover:bg-slate-200">×</button>
                                            </div>

                                                        <div class="p-8">
                                                            <form action="{{ route('carreras.store') }}" method="POST" class="space-y-5">
                                                                @csrf
                                                                <div>
                                                                    <label class="block text-sm font-bold text-gray-700 mb-1">Nombre Oficial</label>
                                                                    <input type="text" name="nombre_carrera" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2FA69A] focus:bg-white transition" placeholder="Ej. Licenciatura en Derecho">
                                                                </div>

                                                                <div>
                                                                    <label class="block text-sm font-bold text-gray-700 mb-1">Descripción</label>
                                                                    <textarea name="descripcion" rows="3" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#2FA69A] focus:bg-white transition" placeholder="Detalles del programa..."></textarea>
                                                                </div>

                                                                <button type="submit" class="w-full py-4 bg-[#2FA69A] text-white font-bold rounded-xl shadow-lg hover:bg-[#23877E] transition flex items-center justify-center gap-2 group">
                                                                    <span>Registrar Carrera</span>
                                                                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                                                    </svg>
                                                                </button>
                                                        </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <script>
                                    document.addEventListener('alpine:init', () => {
                                        Alpine.data('carrerasDashboard', (config) => ({
                                            carreras: config.carreras,
                                            directores: config.directores,
                                            docentes: config.docentes,
                                            statusBaseUrl: config.statusBaseUrl,
                                            search: '',
                                            filterStatus: 'all',
                                            filterDirector: 'all',
                                            modalAssignOpen: false,
                                            modalProfesoresOpen: false,
                                            modalCreateOpen: false,
                                            modalStatusOpen: false,
                                            selectedCarreraId: null,
                                            selectedCarreraNombre: '',
                                            selectedDirectorId: null,
                                            selectedDocentes: [],
                                            selectedStatusValue: 0,
                                            selectedStatusAction: '',

                                            get activasCount() {
                                                return this.carreras.filter(carrera => carrera.status === 1).length;
                                            },
                                            get inactivasCount() {
                                                return this.carreras.filter(carrera => carrera.status === 0).length;
                                            },
                                            get conDirectorCount() {
                                                return this.carreras.filter(carrera => !!carrera.director_id).length;
                                            },
                                            get filteredCarreras() {
                                                return this.carreras.filter(carrera => {
                                                    const term = this.search.toLowerCase();
                                                    const matchesSearch = !term ||
                                                        carrera.nombre.toLowerCase().includes(term) ||
                                                        carrera.director_nombre.toLowerCase().includes(term);

                                                    const matchesStatus = this.filterStatus === 'all' ||
                                                        (this.filterStatus === 'active' && carrera.status === 1) ||
                                                        (this.filterStatus === 'inactive' && carrera.status === 0);

                                                    const matchesDirector = this.filterDirector === 'all' ||
                                                        (this.filterDirector === 'assigned' && !!carrera.director_id) ||
                                                        (this.filterDirector === 'pending' && !carrera.director_id);

                                                    return matchesSearch && matchesStatus && matchesDirector;
                                                });
                                            },
                                            openAssignModal(carrera) {
                                                this.selectedCarreraId = carrera.id;
                                                this.selectedCarreraNombre = carrera.nombre;
                                                this.selectedDirectorId = carrera.director_id;
                                                this.modalAssignOpen = true;
                                                document.body.style.overflow = 'hidden';
                                            },
                                            closeAssignModal() {
                                                this.modalAssignOpen = false;
                                                document.body.style.overflow = '';
                                            },
                                            openProfesoresModal(carrera) {
                                                this.selectedCarreraId = carrera.id;
                                                this.selectedCarreraNombre = carrera.nombre;
                                                this.selectedDocentes = [...carrera.docentes_ids];
                                                this.modalProfesoresOpen = true;
                                                document.body.style.overflow = 'hidden';
                                            },
                                            closeProfesoresModal() {
                                                this.modalProfesoresOpen = false;
                                                document.body.style.overflow = '';
                                                this.selectedDocentes = [];
                                            },
                                            openCreateModal() {
                                                this.modalCreateOpen = true;
                                                document.body.style.overflow = 'hidden';
                                            },
                                            closeCreateModal() {
                                                this.modalCreateOpen = false;
                                                document.body.style.overflow = '';
                                            },
                                            openStatusModal(carrera) {
                                                this.selectedCarreraId = carrera.id;
                                                this.selectedCarreraNombre = carrera.nombre;
                                                this.selectedStatusValue = carrera.status === 1 ? 0 : 1;
                                                this.selectedStatusAction = `${this.statusBaseUrl}/${carrera.id}/estado`;
                                                this.modalStatusOpen = true;
                                                document.body.style.overflow = 'hidden';
                                            },
                                            closeStatusModal() {
                                                this.modalStatusOpen = false;
                                                document.body.style.overflow = '';
                                            },
                                        }));
                                    });
                                </script>

                                <style>
                                    [x-cloak] {
                                        display: none !important;
                                    }
                                </style>