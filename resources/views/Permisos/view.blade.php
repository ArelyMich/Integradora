@extends('layouts.principal')
@section('content')

<div 
    x-data="{ 
        openModal: false, // Para la asignación masiva (checkboxes)
        openDeleteModal: false, // NUEVO: Para la confirmación de eliminación individual
        roleToDeleteId: null, // NUEVO: ID del rol a desasignar
        roleToDeleteName: '', // NUEVO: Nombre del rol
        // Almacena los IDs de los roles actualmente seleccionados (asignados)
        selectedRoles: @js($permiso->roles->pluck('id')->toArray()) 
    }" 
    class="p-8 min-h-screen bg-gray-100"
>

    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold text-[#0C4B54]">Detalle del Permiso: {{ $permiso->sitio }}</h1>
            <p class="text-gray-600">Información básica y roles asignados a esta funcionalidad.</p>
        </div>
        <a href="{{ route('permisos.index') }}" 
           class="px-5 py-2 rounded-xl bg-white-300 hover:bg-gray-400 transition font-semibold flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver a la Lista
        </a>
    </div>

    {{-- ****************************************************** --}}
    {{-- 1. DETALLE DEL PERMISO --}}
    {{-- ****************************************************** --}}
    <div class="bg-white p-6 rounded-2xl shadow mb-8 border-l-4 border-[#0C4B54]">
        <h2 class="text-2xl font-semibold text-[#0C4B54] mb-4">Información del Permiso</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            {{-- ID --}}
            <div>
                <p class="text-sm font-medium text-gray-500">ID</p>
                <p class="text-lg font-semibold text-gray-800">{{ $permiso->id }}</p>
            </div>
            
            {{-- Nombre (Sitio) --}}
            <div>
                <p class="text-sm font-medium text-gray-500">Nombre</p>
                <p class="text-lg font-semibold text-gray-800">{{ $permiso->sitio }}</p>
            </div>
            
            {{-- Ruta --}}
            <div>
                <p class="text-sm font-medium text-gray-500">Ruta / Key</p>
                <p class="text-lg font-mono text-gray-800 bg-gray-100 p-2 rounded-lg">{{ $permiso->ruta }}</p>
            </div>
            
            {{-- Estado --}}
            <div>
                <p class="text-sm font-medium text-gray-500">Estado</p>
                @if ($permiso->status == 1)
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Activo
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        Inactivo
                    </span>
                @endif
            </div>

        </div>
    </div>

    {{-- ****************************************************** --}}
    {{-- 2. ROLES ASIGNADOS --}}
    {{-- ****************************************************** --}}
    <div class="bg-white rounded-2xl shadow overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-700">Roles con este Permiso ({{ $permiso->roles->count() }})</h2>
            
            {{-- BOTÓN PARA ABRIR MODAL --}}
            <button 
                @click="openModal = true"
                class="bg-gradient-to-r 
from-[#326D6C] 
to-[#568F7C] text-white px-5 py-2 rounded-xl shadow hover:bg-[#093D45] transition flex items-center gap-2 text-sm font-medium"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <path d="M14 2v6h6"></path>
                    <path d="M12 18v-6"></path>
                    <path d="M9 15h6"></path>
                </svg>
                Asignar/Desasignar Roles
            </button>
        </div>

        @if ($permiso->roles->isEmpty())
            <div class="p-6 text-center text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Sin Roles Asignados</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Actualmente, ningún rol está vinculado a este permiso. Usa el botón superior para asignar roles.
                </p>
            </div>
        @else
            <table class="min-w-full text-left">
                <thead class="bg-gradient-to-r 

from-[#326D6C] 
to-[#568F7C]

text-white">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Nombre del Rol</th>
                        <th class="px-6 py-3">Estado del Rol</th>
                        <th class="px-6 py-3">Fecha de Asignación (Pivote)</th>
                        <!-- <th class="px-6 py-3">Acciones</th> -->
                    </tr>
                </thead>
                <tbody>
                    @foreach($permiso->roles as $role)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium text-gray-600">{{ $role->id }}</td>
                        <td class="px-6 py-4 font-semibold text-gray-800">
                            {{ $role->nombre ?? $role->name ?? 'N/A' }} 
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                             @if ($role->status == 1)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Activo
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-sm">
                            {{ $role->pivot->created_at ?? 'N/A' }} 
                        </td>
                        {{-- BOTÓN DE ELIMINAR --}}
                        <!-- <td class="px-6 py-4">
                            <button 
                                @click="openDeleteModal = true; roleToDeleteId = {{ $role->id }}; roleToDeleteName = '{{ $role->nombre ?? $role->name }}';"
                                class="text-red-600 hover:text-white transition font-medium text-sm p-1 rounded-md bg-red-100 hover:bg-red-500"
                                title="Eliminar Permiso de este Rol"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td> -->
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    
    {{-- ****************************************************** --}}
    {{-- MODAL DE ASIGNACIÓN DE ROLES (EXISTENTE) --}}
    {{-- ****************************************************** --}}
    <div 
        x-show="openModal"
        x-transition.opacity
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50 p-4">
        <div 
            x-transition.scale
            @click.outside="openModal = false"
            class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-xl relative max-h-[90vh] overflow-y-auto">

            <h2 class="text-3xl font-bold text-[#0C4B54] mb-6 border-b pb-3">Asignar Roles al Permiso</h2>
            <p class="text-gray-600 mb-4">Selecciona los roles que deben tener acceso a la funcionalidad: <strong>{{ $permiso->sitio }}</strong>.</p>
            
            
            <form action="{{ route('permisos.assignRoles', $permiso->id) }}" method="POST" id="roleAssignmentForm">
                @csrf
                <input type="hidden" name="permiso_id" value="{{ $permiso->id }}">
                
                <div class="space-y-4 max-h-80 overflow-y-auto pr-2 border rounded-xl p-3 bg-gray-50">
                    
                    @forelse($roles as $role)
                    <div class="flex items-center justify-between p-3 bg-white rounded-lg shadow-sm hover:bg-blue-50 transition">
                        <label for="role_{{ $role->id }}" class="flex-1 text-base font-semibold text-gray-800 cursor-pointer">
                            {{ $role->nombre ?? $role->name ?? 'Rol sin nombre' }}
                            <span class="block text-sm font-normal text-gray-500">{{ $role->description ?? $role->ruta ?? '' }}</span>
                        </label>
                        
                        <input 
                            type="checkbox" 
                            name="roles[]" 
                            id="role_{{ $role->id }}" 
                            value="{{ $role->id }}" 
                            class="h-5 w-5 text-[#0C4B54] border-gray-300 rounded focus:ring-[#0C4B54]"
                            x-bind:checked="selectedRoles.includes({{ $role->id }})"
                        >
                    </div>
                    @empty
                    <div class="text-center py-5 text-gray-500">
                        No hay roles disponibles para asignar.
                    </div>
                    @endforelse

                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" 
                        @click="openModal = false"
                        class="px-5 py-2 rounded-xl bg-gray-300 hover:bg-gray-400 transition">
                        Cancelar
                    </button>

                    <button type="submit"
                        class="px-6 py-2 rounded-xl bg-gradient-to-r 
from-[#326D6C] 
to-[#568F7C]  text-white font-semibold hover:bg-green-700 transition shadow">
                        Guardar Cambios
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- ****************************************************** --}}
    {{-- NUEVO: MODAL DE CONFIRMACIÓN DE ELIMINACIÓN INDIVIDUAL --}}
    {{-- ****************************************************** --}}
    <div 
        x-show="openDeleteModal"
        x-transition.opacity
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50 p-4"
    >
        <div 
            x-transition.scale
            @click.outside="openDeleteModal = false"
            class="bg-white rounded-3xl shadow-2xl p-6 w-full max-w-sm relative"
        >

            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <h3 class="mt-2 text-xl font-semibold text-gray-900">Confirmar Desasignación</h3>
                <p class="mt-2 text-sm text-gray-500">
                    ¿Estás seguro de que deseas eliminar el permiso <strong>{{ $permiso->sitio }}</strong> del rol 
                    <strong x-text="roleToDeleteName"></strong>?
                </p>
            </div>
            
            {{-- FORMULARIO DE DESASIGNACIÓN --}}
            <form method="POST" :action="#" class="mt-4">
                @csrf
                @method('DELETE')
                
                <div class="flex justify-center gap-3">
                    <button type="button" 
                        @click="openDeleteModal = false"
                        class="px-5 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-100 transition text-gray-700">
                        Cancelar
                    </button>

                    <button type="submit"
                        class="px-6 py-2 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition shadow-md">
                        Sí, Desasignar
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection