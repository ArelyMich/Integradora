@extends('layouts.principal')

@section('content')
<div class="p-4 md:p-8 max-w-7xl mx-auto">
    <!-- TÍTULO -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 gap-4">
        <div class="flex items-center gap-3">
            <div class="bg-[#2FA69A] p-2 rounded-lg">
                <i class="fas fa-user-shield text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Gestión de Roles</h1>
                <p class="text-gray-600 text-sm">Administra los roles y permisos del sistema</p>
            </div>
        </div>

        @if(Auth()->user()->hasPermission('roles.store'))
        <button onclick="openModal('modalCrear')"
            class="bg-[#2FA69A] hover:bg-[#23877E] text-white px-4 py-3 rounded-lg shadow-sm transition-all duration-300 hover:scale-105 flex items-center gap-2 w-full md:w-auto justify-center">
            <i class="fas fa-plus"></i>
            <span>Nuevo Rol</span>
        </button>
        @endif
    </div>

    <!-- MENSAJES -->
    @if(session('success'))
        <div class="bg-[#E5E7EB] border border-[#2FA69A] text-gray-700 px-4 py-3 rounded-lg mb-6 shadow-sm animate-fade-in flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 shadow-lg animate-fade-in flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-800 hover:text-red-900 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- TABLA -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-[#2FA69A]/20 animate-fade-in">
        <div class="overflow-x-auto">
            <table class="min-w-full text-gray-700">
                <thead class="bg-[#2FA69A] text-white">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-hashtag"></i>
                                <span>ID</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left font-semibold">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-tag"></i>
                                <span>Nombre</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center font-semibold">
                            <div class="flex items-center gap-2 justify-center">
                                <i class="fas fa-circle"></i>
                                <span>Status</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center font-semibold">
                            <div class="flex items-center gap-2 justify-center">
                                <i class="fas fa-cogs"></i>
                                <span>Acciones</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $r)
                    <tr class="border-t border-[#2FA69A]/10 transition-all duration-300 hover:bg-[#2FA69A]/5 hover:scale-[1.01]">
                        <td class="px-4 py-3 font-medium">{{ $r->id }}</td>
                        <td class="px-4 py-3 font-semibold">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-user-tag text-[#2FA69A]"></i>
                                <span>{{ $r->nombre }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($r->status)
                                <span class="px-3 py-1 bg-[#E5E7EB] text-gray-700 rounded-full text-sm font-bold inline-flex items-center gap-1 transition-all duration-300 hover:scale-105">
                                    <i class="fas fa-check-circle"></i>
                                    <span>Activo</span>
                                </span>
                            @else
                                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-bold inline-flex items-center gap-1 transition-all duration-300 hover:scale-105">
                                    <i class="fas fa-times-circle"></i>
                                    <span>Inactivo</span>
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center flex items-center justify-center gap-2 flex-wrap">
                            <!-- BOTÓN EDITAR -->
                            @if(auth()->user()->hasPermission('roles.update'))
                            <button
                                onclick="openEditModal({{ $r->id }}, '{{ $r->nombre }}', {{ $r->status }})"
                                class="px-3 py-2 bg-[#2FA69A] hover:bg-[#23877E] text-white rounded-lg shadow-sm transition-all duration-300 hover:scale-105 flex items-center gap-2 group">
                                <i class="fas fa-edit group-hover:rotate-12 transition-transform"></i>
                                <span class="hidden sm:inline">Editar</span>
                            </button>
                            @endif
                            <!-- BOTÓN DESACTIVAR -->
                             @if (auth()->user()->hasPermission('roles.desactivate'))
                             
                             
                            <form action="{{ url('/roles/delete/'.$r->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <button
                                    onclick="return confirmAction('¿Seguro que deseas desactivar este rol?')"
                                    class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg shadow transition-all duration-300 hover:scale-105 flex items-center gap-2 group">
                                    <i class="fas fa-ban group-hover:shake transition-transform"></i>
                                    <span class="hidden sm:inline">Desactivar</span>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center">
                            <div class="p-8 text-center">
                                <i class="fas fa-inbox text-[#2FA69A] text-5xl mb-4 animate-pulse"></i>
                                <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay roles registrados</h3>
                                <p class="text-gray-600 mb-4">Comienza creando tu primer rol en el sistema</p>
                                <button onclick="openModal('modalCrear')"
                                    class="bg-[#2FA69A] hover:bg-[#23877E] text-white px-4 py-2 rounded-lg shadow-sm transition-all duration-300 hover:scale-105 flex items-center gap-2 mx-auto animate-bounce">
                                    <i class="fas fa-plus"></i>
                                    <span>Crear primer rol</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- PAGINACIÓN - SOLO SI ES UN OBJETO PAGINABLE -->
    @if(method_exists($roles, 'links'))
    <div class="mt-6 flex justify-center animate-fade-in">
        <div class="bg-white rounded-lg shadow-md p-2 flex items-center gap-2">
            <!-- Botón Anterior -->
            @if($roles->onFirstPage())
            <span class="px-3 py-1 text-gray-400 rounded cursor-not-allowed">
                <i class="fas fa-chevron-left"></i>
            </span>
            @else
            <a href="{{ $roles->previousPageUrl() }}" class="px-3 py-1 bg-[#E5E7EB] text-gray-700 rounded hover:bg-[#2FA69A] hover:text-white transition-all duration-300 hover:scale-105">
                <i class="fas fa-chevron-left"></i>
            </a>
            @endif
            
            <!-- Números de Página -->
            @php
                $current = $roles->currentPage();
                $last = $roles->lastPage();
                $start = max(1, $current - 2);
                $end = min($last, $current + 2);
            @endphp
            
            @if($start > 1)
                <a href="{{ $roles->url(1) }}" class="px-3 py-1 text-gray-700 rounded hover:bg-[#2FA69A] hover:text-white transition-all duration-300 hover:scale-105">1</a>
                @if($start > 2)
                <span class="px-2 text-gray-400">...</span>
                @endif
            @endif
            
            @for ($i = $start; $i <= $end; $i++)
                @if($i == $current)
                <span class="px-3 py-1 bg-[#2FA69A] text-white rounded transition-all duration-300">{{ $i }}</span>
                @else
                <a href="{{ $roles->url($i) }}" class="px-3 py-1 text-gray-700 rounded hover:bg-[#2FA69A] hover:text-white transition-all duration-300 hover:scale-105">{{ $i }}</a>
                @endif
            @endfor
            
            @if($end < $last)
                @if($end < $last - 1)
                <span class="px-2 text-gray-400">...</span>
                @endif
                <a href="{{ $roles->url($last) }}" class="px-3 py-1 text-gray-700 rounded hover:bg-[#2FA69A] hover:text-white transition-all duration-300 hover:scale-105">{{ $last }}</a>
            @endif
            
            <!-- Botón Siguiente -->
            @if($roles->hasMorePages())
            <a href="{{ $roles->nextPageUrl() }}" class="px-3 py-1 bg-[#E5E7EB] text-gray-700 rounded hover:bg-[#2FA69A] hover:text-white transition-all duration-300 hover:scale-105">
                <i class="fas fa-chevron-right"></i>
            </a>
            @else
            <span class="px-3 py-1 text-gray-400 rounded cursor-not-allowed">
                <i class="fas fa-chevron-right"></i>
            </span>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- MODAL CREAR -->
<div id="modalCrear"
    class="hidden fixed inset-0 bg-[#000009]/70 flex items-center justify-center backdrop-blur-sm z-50 p-4 animate-fade-in">

    <div class="bg-white p-6 rounded-xl w-full max-w-md shadow-lg border border-[#2FA69A] animate-slide-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-plus-circle text-[#2FA69A]"></i>
                Crear Rol
            </h2>
            <button onclick="closeModal('modalCrear')"
                class="text-gray-500 hover:text-[#2FA69A] transition-colors duration-300 hover:scale-110">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form action="{{ route('roles.store') }}" method="POST">
            @csrf

            <label class="block mb-4">
                <span class="text-gray-700 font-medium mb-2 block">Nombre del Rol</span>
                <div class="relative">
                    <i class="fas fa-tag absolute left-3 top-1/2 transform -translate-y-1/2 text-[#2FA69A]"></i>
                    <input type="text" name="nombre"
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-3 focus:ring-2 focus:ring-[#2FA69A] focus:border-[#2FA69A] bg-white transition-all duration-300"
                        placeholder="Ingresa el nombre del rol" required>
                </div>
            </label>

            <div class="flex justify-end gap-2 mt-6">
                <button type="button"
                    onclick="closeModal('modalCrear')"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-all duration-300 hover:scale-105 flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>

                <button class="px-4 py-2 bg-[#2FA69A] hover:bg-[#23877E] text-white rounded-lg transition-all duration-300 hover:scale-105 flex items-center gap-2 animate-pulse">
                    <i class="fas fa-save"></i>
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR -->
<div id="modalEditar"
    class="hidden fixed inset-0 bg-[#000009]/70 flex items-center justify-center backdrop-blur-sm z-50 p-4 animate-fade-in">

    <div class="bg-white p-6 rounded-xl w-full max-w-md shadow-lg border border-[#2FA69A] animate-slide-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-edit text-[#2FA69A]"></i>
                Editar Rol
            </h2>
            <button onclick="closeModal('modalEditar')"
                class="text-gray-500 hover:text-[#2FA69A] transition-colors duration-300 hover:scale-110">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formEditar" method="POST">
            @csrf
            @method('PUT')

            <label class="block mb-4">
                <span class="text-gray-700 font-medium mb-2 block">Nombre del Rol</span>
                <div class="relative">
                    <i class="fas fa-tag absolute left-3 top-1/2 transform -translate-y-1/2 text-[#2FA69A]"></i>
                    <input id="editNombre" type="text" name="nombre"
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-3 focus:ring-2 focus:ring-[#2FA69A] focus:border-[#2FA69A] bg-white transition-all duration-300"
                        required>
                </div>
            </label>

            <label class="block mb-4">
                <span class="text-gray-700 font-medium mb-2 block">Status</span>
                <div class="relative">
                    <i class="fas fa-toggle-on absolute left-3 top-1/2 transform -translate-y-1/2 text-[#2FA69A]"></i>
                    <select id="editStatus" name="status"
                        class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-3 focus:ring-2 focus:ring-[#2FA69A] focus:border-[#2FA69A] bg-white appearance-none transition-all duration-300">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-[#2FA69A] pointer-events-none"></i>
                </div>
            </label>

            <div class="flex justify-end gap-2 mt-6">
                <button type="button"
                    onclick="closeModal('modalEditar')"
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-all duration-300 hover:scale-105 flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>

                <button class="px-4 py-2 bg-[#2FA69A] hover:bg-[#23877E] text-white rounded-lg transition-all duration-300 hover:scale-105 flex items-center gap-2">
                    <i class="fas fa-sync-alt"></i>
                    Actualizar
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .animate-fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    
    .animate-slide-up {
        animation: slideUp 0.3s ease-out;
    }
    
    .hover\:scale-105:hover {
        transform: scale(1.05);
    }
    
    .group:hover .group-hover\:rotate-12 {
        transform: rotate(12deg);
    }
    
    .group:hover .group-hover\:shake {
        animation: shake 0.5s ease-in-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from { 
            opacity: 0;
            transform: translateY(20px);
        }
        to { 
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-2px); }
        75% { transform: translateX(2px); }
    }
    
    @media (max-width: 768px) {
        .hidden.sm\:inline {
            display: none;
        }
    }
</style>

<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function openEditModal(id, nombre, status) {
        document.getElementById('editNombre').value = nombre;
        document.getElementById('editStatus').value = status;
        document.getElementById('formEditar').action = "/roles/update/" + id;
        openModal('modalEditar');
    }
    
    function confirmAction(message) {
        return confirm(message);
    }
    
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal('modalCrear');
            closeModal('modalEditar');
        }
    });
    
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('backdrop-blur-sm')) {
            event.target.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    });
    
    @if(session('success'))
        setTimeout(() => {
            const successAlert = document.querySelector('.bg-\\[\\#85B093\\]');
            if (successAlert) {
                successAlert.remove();
            }
        }, 5000);
    @endif
</script>
@endsection