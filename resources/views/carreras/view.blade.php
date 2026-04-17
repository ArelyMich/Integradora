@extends('layouts.principal')
@section('content')

<div 
    x-data="{ 
        openModal: false, 
        // Obtenemos los IDs de los docentes que YA están asignados para marcarlos en el modal
        selectedDocentes: @js($carrera->docentes->pluck('id')->toArray()) 
    }" 
    class="p-8 min-h-screen bg-gray-100"
>

    {{-- HEADER --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold text-[#0C4B54]">Detalle de Carrera</h1>
            <p class="text-gray-600">Gestión académica y claustro de profesores.</p>
        </div>
        <a href="{{ route('carreras.index') }}" 
           class="px-5 py-2 rounded-xl bg-gray-300 hover:bg-gray-400 transition font-semibold flex items-center gap-2 text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver
        </a>
    </div>

    {{-- ****************************************************** --}}
    {{-- 1. DETALLE DE LA CARRERA (INFO + DIRECTOR) --}}
    {{-- ****************************************************** --}}
    <div class="bg-white p-6 rounded-2xl shadow mb-8 border-l-4 border-[#0C4B54]">
        <h2 class="text-2xl font-semibold text-[#0C4B54] mb-4">Información General</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- Nombre Carrera --}}
            <div>
                <p class="text-xs font-bold uppercase text-gray-400 tracking-wider">Nombre Oficial</p>
                <p class="text-xl font-bold text-gray-800 mt-1">{{ $carrera->nombre_carrera ?? $carrera->nombre }}</p>
            </div>
            
            {{-- Director (AQUÍ SE MUESTRA EL DIRECTOR) --}}
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                <p class="text-xs font-bold uppercase text-gray-400 tracking-wider mb-1">Director Académico</p>
                <div class="flex items-center gap-3">
                    @if($carrera->director)
                        <div class="w-10 h-10 rounded-full bg-[#0C4B54] text-white flex items-center justify-center font-bold text-lg">
                            {{ strtoupper(substr($carrera->director->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">{{ $carrera->director->name }} {{ $carrera->director->apellido_paterno }}</p>
                            <p class="text-xs text-green-600 font-semibold">Asignado</p>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-orange-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <span class="text-sm font-bold">Sin Director Asignado</span>
                        </div>
                    @endif
                </div>
            </div>
            
            {{-- Descripción / Detalles --}}
            <div>
                <p class="text-xs font-bold uppercase text-gray-400 tracking-wider">Descripción</p>
                <p class="text-sm text-gray-600 mt-1">{{ $carrera->descripcion ?? 'Sin descripción disponible.' }}</p>
            </div>

        </div>
    </div>

    {{-- ****************************************************** --}}
    {{-- 2. TABLA DE DOCENTES ASIGNADOS --}}
    {{-- ****************************************************** --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-gray-700">Claustro Docente</h2>
                <span class="bg-gray-200 text-gray-700 text-xs font-bold px-2 py-1 rounded-full">{{ $carrera->docentes->count() }}</span>
            </div>
            
            {{-- BOTÓN PARA ABRIR MODAL DE ASIGNACIÓN --}}
            <button 
                @click="openModal = true"
                class="bg-[#0C4B54] text-white px-5 py-2 rounded-xl shadow hover:bg-[#093D45] transition flex items-center gap-2 text-sm font-bold"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Gestionar Profesores
            </button>
        </div>

        @if ($carrera->docentes->isEmpty())
            <div class="p-10 text-center text-gray-500">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900">Sin profesores asignados</h3>
                <p class="mt-1 text-sm text-gray-500">Esta carrera aún no cuenta con docentes vinculados.</p>
            </div>
        @else
            <table class="min-w-full text-left">
                <thead class="bg-gray-100 text-gray-600 border-b uppercase text-xs tracking-wider font-semibold">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Nombre del Docente</th>
                        <th class="px-6 py-3">Email / Contacto</th>
                        <th class="px-6 py-3">Fecha Asignación</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($carrera->docentes as $docente)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-mono text-gray-500 text-xs">#{{ $docente->id }}</td>
                        
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($docente->name, 0, 1)) }}
                                </div>
                                <span class="font-semibold text-gray-800">
                                    {{ $docente->name }} {{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-gray-600 text-sm">
                            {{ $docente->email }}
                        </td>
                        
                        <td class="px-6 py-4 text-gray-500 text-sm">
                            {{ $docente->pivot->created_at ? $docente->pivot->created_at->format('d/m/Y') : 'N/A' }} 
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    
    {{-- ****************************************************** --}}
    {{-- MODAL DE ASIGNACIÓN MASIVA (CHECKBOXES) --}}
    {{-- ****************************************************** --}}
    <div 
        x-show="openModal"
        style="display: none;"
        x-transition.opacity
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50 p-4">
        
        <div 
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            @click.outside="openModal = false"
            class="bg-white rounded-3xl shadow-2xl p-0 w-full max-w-2xl relative overflow-hidden max-h-[90vh] flex flex-col"
        >
            {{-- Header Modal --}}
            <div class="bg-[#0C4B54] p-6 text-white">
                <h2 class="text-2xl font-bold">Gestión de Claustro</h2>
                <p class="text-blue-100 text-sm mt-1">Marca los profesores que impartirán clases en esta carrera.</p>
            </div>
            
            <form action="{{ route('carreras.asignarProfesores') }}" method="POST" class="flex-1 flex flex-col overflow-hidden">
                @csrf
                <input type="hidden" name="carrera_id" value="{{ $carrera->id }}">
                
                {{-- Cuerpo Scrollable --}}
                <div class="p-6 overflow-y-auto custom-scrollbar bg-gray-50">
                    <div class="mb-3 flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-500 uppercase">Listado de Docentes</span>
                        <span class="text-xs bg-white border px-2 py-1 rounded-md text-gray-600" x-text="selectedDocentes.length + ' seleccionados'"></span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @forelse($todosLosDocentes as $docente)
                        <label 
                            class="flex items-start p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:border-[#0C4B54] hover:shadow-md transition group relative"
                            :class="selectedDocentes.includes({{ $docente->id }}) ? 'border-[#0C4B54] ring-1 ring-[#0C4B54] bg-blue-50/30' : ''"
                        >
                            <div class="flex items-center h-5 mt-1">
                                <input 
                                    type="checkbox" 
                                    name="profesores[]" 
                                    value="{{ $docente->id }}" 
                                    x-model="selectedDocentes"
                                    class="w-4 h-4 text-[#0C4B54] border-gray-300 rounded focus:ring-[#0C4B54] transition"
                                >
                            </div>
                            <div class="ml-3">
                                <span class="block text-sm font-bold text-gray-800 group-hover:text-[#0C4B54] transition">
                                    {{ $docente->name }} {{ $docente->apellido_paterno }}
                                </span>
                                <span class="block text-xs text-gray-500">{{ $docente->email }}</span>
                            </div>
                            
                            {{-- Check visual (icono) --}}
                            <div x-show="selectedDocentes.includes({{ $docente->id }})" class="absolute top-2 right-2 text-[#0C4B54]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </label>
                        @empty
                        <div class="col-span-2 text-center py-8 text-gray-500">
                            No hay docentes registrados en el sistema.
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Footer con Botones --}}
                <div class="p-6 bg-white border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" 
                        @click="openModal = false"
                        class="px-6 py-2.5 rounded-xl bg-gray-100 text-gray-700 font-bold hover:bg-gray-200 transition">
                        Cancelar
                    </button>

                    <button type="submit"
                        class="px-8 py-2.5 rounded-xl bg-[#0C4B54] text-white font-bold hover:bg-[#093D45] shadow-lg shadow-[#0C4B54]/20 transition hover:-translate-y-0.5">
                        Guardar Asignaciones
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<style>
    /* Scrollbar fino */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

@endsection