@extends('layouts.principal')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 px-4 md:px-8 py-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <!-- TÍTULO -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-[#111827]">Gestión de Roles</h1>
                <p class="text-[#111827] text-sm opacity-80">Administra los roles y permisos del sistema</p>
            </div>
        </div>

        @if(Auth()->user()->hasPermission('roles.store'))
        <button onclick="openModal('modalCrear')"
            class="bg-[#0F766E] hover:bg-[#14B8A6] text-white px-4 py-3 rounded-xl shadow-md transition-all duration-300 hover:scale-105 flex items-center gap-2 w-full md:w-auto justify-center">
            <i class="fas fa-plus"></i>
            <span>Nuevo Rol</span>
        </button>
        @endif
    </div>

    <!-- MENSAJES -->
    @if(session('success'))
        <div class="bg-[#DCF7F3] border border-[#0F766E]/20 text-[#111827] px-4 py-3 rounded-xl mb-6 shadow-sm animate-fade-in flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-check-circle text-[#0F766E]"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-[#0F766E] hover:text-[#0F766E]/80 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded-xl mb-6 shadow-sm animate-fade-in flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-exclamation-circle text-red-600"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-red-800 hover:text-red-900 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- FILTROS -->
    <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-[#0F766E]">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-lg bg-[#F3F4F6] flex items-center justify-center">
                <svg class="w-5 h-5 text-[#0F766E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-[#111827]">Buscar y filtrar roles</h3>
        </div>

        <div class="flex flex-col md:flex-row md:items-center gap-4 mb-5">
            <div class="relative flex-grow">
                <input type="text" placeholder="Buscar por nombre de rol..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#14B8A6] focus:border-[#14B8A6] bg-[#F3F4F6] text-sm transition"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <select class="border border-gray-200 rounded-xl py-2.5 px-3 bg-[#F3F4F6] focus:ring-2 focus:ring-[#14B8A6] focus:border-[#14B8A6] text-sm md:w-auto w-full transition">
                <option value="Todas">Estado (Todas)</option>
                <option value="Activa">Activa</option>
                <option value="Inactiva">Inactiva</option>
            </select>

            <button class="w-full md:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#0F766E] hover:bg-[#14B8A6] text-white font-semibold shadow-md transition-all duration-200">
                <i class="fas fa-filter"></i>
                Filtrar
            </button>
        </div>
    </div>

    <!-- GRID DE ROLES -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse ($roles as $r)
        <div class="bg-white rounded-xl border border-gray-100 shadow-md overflow-hidden transition-all duration-300 hover:shadow-xl border-t-4 border-[#0F766E]">
            <div class="p-6 space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-[#0F766E] font-semibold">Rol #{{ $r->id }}</p>
                        <h2 class="text-xl font-bold text-[#111827]">{{ $r->nombre }}</h2>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-[#F3F4F6] flex items-center justify-center">
                        <i class="fas fa-user-shield text-[#0F766E] text-lg"></i>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    @if ($r->status)
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-[#DCF7F3] text-[#0F766E] rounded-full text-sm font-semibold">
                            <i class="fas fa-check-circle"></i>
                            Activo
                        </span>
                    @else
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-100 text-red-700 rounded-full text-sm font-semibold">
                            <i class="fas fa-times-circle"></i>
                            Inactivo
                        </span>
                    @endif
                </div>

                <div class="text-sm text-[#475569]">
                    <p class="font-medium">Descripción</p>
                    <p class="mt-1">{{ $r->nombre }} es un rol del sistema.</p>
                </div>
            </div>

            <div class="bg-[#F3F4F6] px-5 py-4 border-t border-gray-100 flex flex-wrap items-center gap-2 justify-end">
                @if(auth()->user()->hasPermission('roles.update'))
                <button
                    onclick="openEditModal({{ $r->id }}, '{{ $r->nombre }}', {{ $r->status }})"
                    class="px-4 py-2 bg-[#0F766E] hover:bg-[#14B8A6] text-white rounded-xl shadow-sm transition-all duration-200 flex items-center gap-2">
                    <i class="fas fa-edit"></i>
                    Editar
                </button>
                @endif

                @if (auth()->user()->hasPermission('roles.desactivate'))
                <form action="{{ url('/roles/delete/'.$r->id) }}" method="POST" class="inline">
                    @csrf
                    @method('PUT')
                    <button
                        onclick="return confirmAction('¿Seguro que deseas desactivar este rol?')"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl shadow-sm transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-ban"></i>
                        Desactivar
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-xl border border-gray-100 shadow-md p-8 text-center border-t-4 border-[#0F766E]">
            <i class="fas fa-inbox text-[#0F766E] text-5xl mb-4 animate-pulse"></i>
            <h3 class="text-xl font-semibold text-[#111827] mb-2">No hay roles registrados</h3>
            <p class="text-[#475569] mb-4">Comienza creando tu primer rol en el sistema</p>
            <button onclick="openModal('modalCrear')"
                class="bg-[#0F766E] hover:bg-[#14B8A6] text-white px-5 py-3 rounded-xl shadow-md transition-all duration-300 hover:scale-105 inline-flex items-center gap-2 mx-auto">
                <i class="fas fa-plus"></i>
                Crear primer rol
            </button>
        </div>
        @endforelse
    </div>
    
    <!-- PAGINACIÓN - SOLO SI ES UN OBJETO PAGINABLE -->
    @if(method_exists($roles, 'links'))
    <div class="mt-6 flex justify-center animate-fade-in">
        <div class="bg-white rounded-xl shadow-md p-4 flex items-center gap-2 border border-[#E2E8F0]">
            <!-- Botón Anterior -->
            @if($roles->onFirstPage())
            <span class="px-3 py-1 text-gray-400 rounded cursor-not-allowed">
                <i class="fas fa-chevron-left"></i>
            </span>
            @else
            <a href="{{ $roles->previousPageUrl() }}" class="px-3 py-1 bg-[#0F766E] text-white rounded hover:bg-[#14B8A6] transition-all duration-300 hover:scale-105">
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
                <a href="{{ $roles->url(1) }}" class="px-3 py-1 text-[#111827] rounded hover:bg-[#DCF7F3] transition-all duration-300 hover:scale-105">1</a>
                @if($start > 2)
                <span class="px-2 text-gray-400">...</span>
                @endif
            @endif
            
            @for ($i = $start; $i <= $end; $i++)
                @if($i == $current)
                <span class="px-3 py-1 bg-[#0F766E] text-white rounded transition-all duration-300">{{ $i }}</span>
                @else
                <a href="{{ $roles->url($i) }}" class="px-3 py-1 text-[#111827] rounded hover:bg-[#DCF7F3] transition-all duration-300 hover:scale-105">{{ $i }}</a>
                @endif
            @endfor
            
            @if($end < $last)
                @if($end < $last - 1)
                <span class="px-2 text-gray-400">...</span>
                @endif
                <a href="{{ $roles->url($last) }}" class="px-3 py-1 text-[#111827] rounded hover:bg-[#DCF7F3] transition-all duration-300 hover:scale-105">{{ $last }}</a>
            @endif
            
            <!-- Botón Siguiente -->
            @if($roles->hasMorePages())
            <a href="{{ $roles->nextPageUrl() }}" class="px-3 py-1 bg-[#0F766E] text-white rounded hover:bg-[#14B8A6] transition-all duration-300 hover:scale-105">
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
    class="hidden fixed inset-0 bg-[#000009]/70 flex items-center justify-center backdrop-blur-sm z-[100] p-4 animate-fade-in">

    <div class="bg-white p-6 rounded-2xl w-full max-w-md shadow-2xl border border-[#0F766E]/20 animate-slide-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-[#111827] flex items-center gap-2">
                <i class="fas fa-plus-circle text-[#0F766E]"></i>
                Crear Rol
            </h2>
            <button onclick="closeModal('modalCrear')"
                class="text-gray-500 hover:text-[#173C4C] transition-colors duration-300 hover:scale-110">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form action="{{ route('roles.store') }}" method="POST">
            @csrf

            <label class="block mb-4">
                <span class="text-[#111827] font-medium mb-2 block">Nombre del Rol</span>
                <div class="relative">
                    <i class="fas fa-tag absolute left-3 top-1/2 transform -translate-y-1/2 text-[#0F766E]"></i>
                    <input type="text" name="nombre"
                        class="w-full border border-[#0F766E]/20 rounded-xl pl-10 pr-3 py-3 focus:ring-2 focus:ring-[#14B8A6] focus:border-[#14B8A6] bg-white transition-all duration-300"
                        placeholder="Ingresa el nombre del rol" required>
                </div>
            </label>

            <div class="flex justify-end gap-2 mt-6">
                <button type="button"
                    onclick="closeModal('modalCrear')"
                    class="px-4 py-2 bg-[#F3F4F6] hover:bg-[#E1F5F2] text-[#111827] rounded-xl transition-all duration-300 hover:scale-105 flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>

                <button class="px-4 py-2 bg-[#0F766E] hover:bg-[#14B8A6] text-white rounded-xl transition-all duration-300 hover:scale-105 flex items-center gap-2 animate-pulse">
                    <i class="fas fa-save"></i>
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR -->
<div id="modalEditar"
    class="hidden fixed inset-0 bg-[#000009]/70 flex items-center justify-center backdrop-blur-sm z-[100] p-4 animate-fade-in">

    <div class="bg-white p-6 rounded-2xl w-full max-w-md shadow-2xl border border-[#0F766E]/20 animate-slide-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-[#111827] flex items-center gap-2">
                <i class="fas fa-edit text-[#0F766E]"></i>
                Editar Rol
            </h2>
            <button onclick="closeModal('modalEditar')"
                class="text-gray-500 hover:text-[#173C4C] transition-colors duration-300 hover:scale-110">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="formEditar" method="POST">
            @csrf
            @method('PUT')

            <label class="block mb-4">
                <span class="text-[#111827] font-medium mb-2 block">Nombre del Rol</span>
                <div class="relative">
                    <i class="fas fa-tag absolute left-3 top-1/2 transform -translate-y-1/2 text-[#0F766E]"></i>
                    <input id="editNombre" type="text" name="nombre"
                        class="w-full border border-[#0F766E]/20 rounded-xl pl-10 pr-3 py-3 focus:ring-2 focus:ring-[#14B8A6] focus:border-[#14B8A6] bg-white transition-all duration-300"
                        required>
                </div>
            </label>

            <label class="block mb-4">
                <span class="text-[#111827] font-medium mb-2 block">Status</span>
                <div class="relative">
                    <i class="fas fa-toggle-on absolute left-3 top-1/2 transform -translate-y-1/2 text-[#0F766E]"></i>
                    <select id="editStatus" name="status"
                        class="w-full border border-[#0F766E]/20 rounded-xl pl-10 pr-3 py-3 focus:ring-2 focus:ring-[#14B8A6] focus:border-[#14B8A6] bg-white appearance-none transition-all duration-300">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-[#0F766E] pointer-events-none"></i>
                </div>
            </label>

            <div class="flex justify-end gap-2 mt-6">
                <button type="button"
                    onclick="closeModal('modalEditar')"
                    class="px-4 py-2 bg-[#F3F4F6] hover:bg-[#E1F5F2] text-[#111827] rounded-xl transition-all duration-300 hover:scale-105 flex items-center gap-2">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>

                <button class="px-4 py-2 bg-[#0F766E] hover:bg-[#14B8A6] text-white rounded-xl transition-all duration-300 hover:scale-105 flex items-center gap-2">
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