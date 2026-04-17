@extends('layouts.principal')
@section('content')

{{-- 
    VISTA DE GESTIÓN DE MATERIAS (MEJORADA CON FILTROS, PAGINACIÓN Y ANIMACIONES AVANZADAS)
    *** DISEÑO DE CARDS MEJORADO: Estilo moderno con animaciones ***
--}}
                    
<div x-data="{ 
    openCreateModal: false,
    openAsignarModal: false, 
    openEditModal: false,
    selectedMateria: null,
    
    // FILTROS Y PAGINACIÓN
    activeCarrera: 'Todas',
    searchTerm: '',
    filterStatus: 'Todas',
    filterHours: 'Todas',
    itemsPerPage: 8, // Cantidad de cards por página
    currentPage: 1, // Página actual

    // DATOS REALES DESDE LARAVEL
    carreras: {{ Js::from($carreras) }},
    materiasData: {{ Js::from($materias) }}, 

    editMateria(materia) {
        this.selectedMateria = materia;
        this.openEditModal = true;
    },

    // 1. Lógica de filtrado combinada
    get filteredMaterias() {
        let filtered = this.materiasData;

        // FILTRO 1: Búsqueda por texto (Nombre o Código)
        if (this.searchTerm) {
            const term = this.searchTerm.toLowerCase();
            filtered = filtered.filter(m => 
                m.nombre.toLowerCase().includes(term) || 
                m.codigo.toLowerCase().includes(term)
            );
        }

        // FILTRO 2: Por Carrera (Relación N:M)
        if (this.activeCarrera !== 'Todas') {
            const carreraId = this.activeCarrera;
            filtered = filtered.filter(materia => {
                if (!materia.carreras || materia.carreras.length === 0) return false;
                return materia.carreras.some(c => c.id == carreraId);
            });
        }

        // FILTRO 3: Por Estado (Activa/Inactiva)
        if (this.filterStatus !== 'Todas') {
            const statusValue = this.filterStatus === 'Activa' ? 1 : 0;
            filtered = filtered.filter(m => m.status == statusValue);
        }

        // FILTRO 4: Por Carga Semanal (Ejemplo simple de rango)
        if (this.filterHours !== 'Todas') {
            const [min, max] = this.filterHours.split('-').map(Number);
            filtered = filtered.filter(m => m.horas_semanales >= min && m.horas_semanales <= max);
        }

        this.currentPage = 1; // Resetear página al filtrar
        return filtered;
    },

    // 2. Lógica de paginación
    get paginatedMaterias() {
        const start = (this.currentPage - 1) * this.itemsPerPage;
        const end = start + this.itemsPerPage;
        return this.filteredMaterias.slice(start, end);
    },

    // 3. Paginación: Cálculos auxiliares
    get totalPages() {
        return Math.ceil(this.filteredMaterias.length / this.itemsPerPage);
    },
    
    // Helper para obtener nombre de carrera por ID 
    getCarreraName(id) {
        let c = this.carreras.find(c => c.id == id);
        return c ? (c.nombre_carrera || c.nombre) : 'Sin asignar';
    }

}" class="p-8 min-h-screen bg-gray-100">

    {{-- ENCABEZADO --}}
    <div class="mb-8 flex flex-col md:flex-row items-start md:items-center justify-between">
        <div>
            <h1 class="text-4xl font-bold text-[#0C4B54] border-l-4 border-[#0C4B54] pl-3">Gestión de Materias</h1>
            <p class="text-gray-600 mt-1">Organiza y administra las asignaturas por carrera.</p>
        </div>
        
        @if(Auth::user()->hasPermission('materias.store'))
        <button 
            @click="openCreateModal = true"
            class="mt-4 md:mt-0 bg-[#0C4B54] text-white px-6 py-2 rounded-xl shadow-lg hover:bg-[#093D45] transition flex items-center gap-2 font-semibold text-base"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Nueva Materia
        </button>
        @endif
    </div>
    
    {{-- BARRA DE FILTROS ADICIONALES --}}
    <div class="bg-white p-4 rounded-xl shadow-md mb-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            <h3 class="text-sm font-semibold text-gray-500 uppercase flex-shrink-0">Filtros:</h3>
            
            {{-- Filtro de Búsqueda --}}
            <div class="relative flex-grow">
                <input 
                    type="text" 
                    x-model.debounce.300ms="searchTerm" 
                    placeholder="Buscar por Nombre o Código..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-xl focus:ring-[#0C4B54] focus:border-[#0C4B54]"
                >
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            {{-- Filtro por Estado --}}
            <select x-model="filterStatus" class="border border-gray-300 rounded-xl py-2 px-3 focus:ring-[#0C4B54] focus:border-[#0C4B54] md:w-auto w-full">
                <option value="Todas">Estado (Todas)</option>
                <option value="Activa">Activa</option>
                <option value="Inactiva">Inactiva</option>
            </select>
            
            {{-- Filtro por Horas Semanales --}}
            <select x-model="filterHours" class="border border-gray-300 rounded-xl py-2 px-3 focus:ring-[#0C4B54] focus:border-[#0C4B54] md:w-auto w-full">
                <option value="Todas">Horas (Todas)</option>
                <option value="1-4">1-4 h/sem</option>
                <option value="5-8">5-8 h/sem</option>
                <option value="9-100">9+ h/sem</option>
            </select>
        </div>

        {{-- FILTRO DE CARRERAS (TABS DINÁMICOS) --}}
        <div class="border-t pt-4">
            <h3 class="text-sm font-semibold text-gray-500 mb-3 uppercase">Filtrar por Carrera</h3>
            <div class="flex space-x-3 pb-2 overflow-x-auto">
                {{-- Tab 'Todas' --}}
                <button 
                    @click="activeCarrera = 'Todas'"
                    :class="activeCarrera === 'Todas' ? 'bg-[#0C4B54] text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                    class="flex-shrink-0 px-4 py-2 rounded-full font-medium transition duration-150 text-sm whitespace-nowrap"
                >
                    Todas (<span x-text="materiasData.length"></span>)
                </button>
                
                {{-- Tabs generados desde $carreras --}}
                <template x-for="carrera in carreras" :key="carrera.id">
                    <button 
                        @click="activeCarrera = carrera.id"
                        :class="activeCarrera === carrera.id ? 'bg-[#0C4B54] text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="flex-shrink-0 px-4 py-2 rounded-full font-medium transition duration-150 text-sm whitespace-nowrap"
                    >
                        <span x-text="carrera.nombre_carrera || carrera.nombre"></span>
                        <span 
                            class="text-xs opacity-75 ml-1" 
                            x-text="'(' + materiasData.filter(m => m.carreras && m.carreras.some(c => c.id == carrera.id)).length + ')'"
                        ></span>
                    </button>
                </template>
            </div>
        </div>
    </div>


    {{-- LISTA DE MATERIAS (Diseño y Animación Mejorada - Nombre Completo) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <template x-for="(materia, index) in paginatedMaterias" :key="materia.id">
            <div 
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4 scale-95"
                x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
                :style="`transition-delay: ${index * 60}ms;`" {{-- Animación de entrada escalonada --}}
                
                {{-- ESTILO DE CARD Y HOVER --}}
                class="bg-white rounded-2xl border border-gray-200 shadow-xl 
                       hover:shadow-2xl hover:scale-[1.02] hover:border-[#0C4B54] 
                       transition duration-300 flex flex-col justify-between overflow-hidden relative"
            >
                
                {{-- Código y Estado (Esquina Superior) --}}
                <div class="absolute top-0 right-0 flex items-center">
                    {{-- Código o identificador principal --}}
                    <span x-text="materia.codigo" class="text-xs font-mono text-white bg-[#0C4B54] px-3 py-1 rounded-bl-lg shadow-inner opacity-90"></span>
                    
                    {{-- Indicador de Estado --}}
                    <span 
                        x-text="materia.status == 1 ? 'Activa' : 'Inactiva'"
                        :class="materia.status == 1 ? 'bg-green-500' : 'bg-red-500'"
                        class="text-xxs text-white font-bold h-3 w-3 rounded-full ml-2 mr-3"
                    ></span>
                </div>
                
                {{-- Iconografía y contenido principal --}}
                <div class="p-6 pb-3">
                    <div class="flex items-start space-x-3 mb-4">
                        {{-- Ícono de la Materia --}}
                        <div class="p-3 bg-indigo-100 rounded-full text-indigo-600 flex-shrink-0 shadow-inner mt-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18s-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        
                        {{-- Nombre (Modificado para no truncar) --}}
                        <h4 x-text="materia.nombre" class="text-xl font-extrabold text-gray-800 leading-snug" :title="materia.nombre"></h4>
                    </div>
                    
                    {{-- Descripción ahora truncada a 3 líneas (para mantener el espacio de la card controlado) --}}
                    <p x-text="materia.descripcion" class="text-sm text-gray-600 line-clamp-3 mb-4 border-t pt-3"></p>
                    
                    {{-- Detalles Adicionales --}}
                    <div class="space-y-2 text-sm">
                        <p class="flex items-center gap-2 text-gray-700 font-medium">
                            <svg class="w-4 h-4 text-[#0C4B54] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span x-text="materia.carreras.length ? materia.carreras.map(c => c.nombre_carrera || c.nombre).join(', ') : 'Sin carrera asignada'" class="truncate"></span>
                         </p>
                        <p class="flex items-center gap-2 text-gray-700">
                            <svg class="w-4 h-4 text-[#0C4B54] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="font-bold text-[#0C4B54]" x-text="materia.horas_semanales + 'h '"></span> <span class="text-gray-500">/ Sem.</span> |
                            <span class="font-bold text-[#0C4B54]" x-text="materia.horas_totales + 'h '"></span> <span class="text-gray-500">/ Total</span>
                        </p>
                    </div>
                </div>

                {{-- Pie de Acción (Botones con mejor estilo y hover) --}}
                <div class="bg-gray-50 p-4 border-t flex justify-end gap-2">
                    
                    @if(Auth::user()->hasPermission('materias.asignarMateria'))
                        <button 
                            @click="selectedMateria = materia; openAsignarModal = true" {{-- 🎯 MODIFICADO: Guarda la materia antes de abrir el modal --}}
                            class="text-sm font-semibold text-[#0C4B54] bg-white border border-[#0C4B54] px-3 py-1.5 rounded-lg hover:bg-[#0C4B54] hover:text-white transition duration-200 shadow-sm"
                        >
                            Asignar Profesor
                        </button>
                    @endif
                    
                    <button @click="editMateria(materia)" class="p-2 text-gray-500 hover:text-white hover:bg-[#0C4B54] rounded-lg transition duration-200" title="Editar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </button>
                    
                    {{-- Formulario DELETE --}}
                    <form :action="'/materias/' + materia.id" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar esta materia?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-red-600 hover:text-white hover:bg-red-600 rounded-lg transition duration-200" title="Eliminar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </template>
        
        {{-- Mensaje si no hay resultados --}}
        <div x-show="filteredMaterias.length === 0" class="col-span-full text-center py-12 text-gray-500 transition duration-300">
            No se encontraron materias registradas con los filtros actuales.
        </div>
    </div>
    
    {{-- PAGINACIÓN --}}
    <div x-show="filteredMaterias.length > itemsPerPage" class="mt-8 flex justify-between items-center bg-white p-4 rounded-xl shadow-md">
        <span class="text-sm text-gray-700">
            Mostrando 
            <span class="font-semibold" x-text="Math.min((currentPage - 1) * itemsPerPage + 1, filteredMaterias.length)"></span> 
            a 
            <span class="font-semibold" x-text="Math.min(currentPage * itemsPerPage, filteredMaterias.length)"></span> 
            de 
            <span class="font-semibold" x-text="filteredMaterias.length"></span> materias
        </span>
        <div class="inline-flex space-x-2">
            <button 
                @click="currentPage = Math.max(1, currentPage - 1)" 
                :disabled="currentPage === 1"
                :class="currentPage === 1 ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-[#0C4B54] text-white hover:bg-[#093D45]'"
                class="px-3 py-1 rounded-lg font-semibold transition duration-150"
            >
                Anterior
            </button>
            
            {{-- Indicador de página --}}
            <span class="px-3 py-1 rounded-lg bg-gray-100 font-semibold text-gray-800">
                <span x-text="currentPage"></span> / <span x-text="totalPages"></span>
            </span>

            <button 
                @click="currentPage = Math.min(totalPages, currentPage + 1)" 
                :disabled="currentPage === totalPages"
                :class="currentPage === totalPages ? 'bg-gray-200 text-gray-500 cursor-not-allowed' : 'bg-[#0C4B54] text-white hover:bg-[#093D45]'"
                class="px-3 py-1 rounded-lg font-semibold transition duration-150"
            >
                Siguiente
            </button>
        </div>
    </div>


    {{-- ****************************************************** --}}
    {{-- MODAL CREAR MATERIA --}}
    {{-- ****************************************************** --}}
    <div 
        x-show="openCreateModal"
        style="display: none;"
        x-transition.opacity
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50 p-4"
    >
        <div 
            x-transition.scale
            @click.outside="openCreateModal = false"
            class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-lg relative max-h-[90vh] overflow-y-auto"
        >
            <h2 class="text-2xl font-bold text-[#0C4B54] mb-6 border-b pb-3">Crear Nueva Materia</h2>
            
            <form action="{{route('materias.store')}}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre de la Materia</label>
                    <input type="text" name="nombre" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:ring-[#0C4B54] focus:border-[#0C4B54] p-3">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código</label>
                    <input type="text" name="codigo" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:ring-[#0C4B54] focus:border-[#0C4B54] p-3">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Carrera</label>
                    <select name="carrera_id" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:ring-[#0C4B54] focus:border-[#0C4B54] p-3">
                        <option value="" selected disabled>Selecciona una Carrera</option>
                        <template x-for="c in carreras" :key="c.id">
                            <option :value="c.id" x-text="c.nombre_carrera || c.nombre"></option>
                        </template>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Carga Semanal</label>
                        <input type="number" name="horas_semanales" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:ring-[#0C4B54] focus:border-[#0C4B54] p-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Horas</label>
                        <input type="number" name="horas_totales" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:ring-[#0C4B54] focus:border-[#0C4B54] p-3">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descripción Breve</label>
                    <textarea id="descripcion" name="descripcion" rows="3" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:ring-[#0C4B54] focus:border-[#0C4B54] p-3"></textarea>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="openCreateModal = false" class="px-5 py-2 rounded-xl bg-gray-300 hover:bg-gray-400 transition text-gray-800">Cancelar</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-[#0C4B54] text-white font-bold hover:bg-[#093D45] transition shadow">Guardar Materia</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ****************************************************** --}}
    {{-- 🎯 MODAL ASIGNAR PROFESOR A MATERIA (MODIFICADO) --}}
    {{-- ****************************************************** --}}
    <div 
        x-show="openAsignarModal"
        style="display: none;"
        x-transition.opacity
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50 p-4"
    >
        <div 
            x-transition.scale
            @click.outside="openAsignarModal = false"
            class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-lg relative max-h-[90vh] overflow-y-auto"
        >
            <h2 class="text-2xl font-bold text-[#0C4B54] mb-6 border-b pb-3">Asignar Profesor a Materia</h2>
            
            <form action="{{route('materias.asignarMateria')}}" method="POST" class="space-y-4">
                @csrf
                
                {{-- 🚩 CAMPOS OCULTOS AGREGADOS: Envían el ID de la materia y la carrera al controlador --}}
                <input type="hidden" name="materia_id" :value="selectedMateria?.id">
                <input type="hidden" name="carrera_id" :value="selectedMateria?.carreras[0]?.id"> 

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Seleccione el Profesor</label>
                    
                    {{-- SELECT SENCILLO (Un solo valor) --}}
                    <select 
                        name="docente_id" {{-- Nombre cambiado de docentes[] a docente_id --}}
                        required 
                        class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm focus:ring-[#0C4B54] focus:border-[#0C4B54] p-3 bg-white"
                        {{-- Eliminados: multiple y size --}}
                    >
                        <option value="" selected disabled>Seleccione un Profesor...</option>
                        @foreach ($docentes as $docente)
                            <option value="{{ $docente->id }}" class="py-2 px-3 text-gray-800">
                                {{ $docente->name }} {{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="openAsignarModal = false" class="px-5 py-2 rounded-xl bg-gray-300 hover:bg-gray-400 transition text-gray-800">Cancelar</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-[#0C4B54] text-white font-bold hover:bg-[#093D45] transition shadow">Guardar Asignación</button>
                </div>
            
            </form>
        </div>
    </div>

    {{-- ****************************************************** --}}
    {{-- MODAL DE EDICIÓN (Se mantiene como estaba, se eliminó un formulario duplicado) --}}
    {{-- ****************************************************** --}}
    <div 
        x-show="openEditModal"
        style="display: none;"
        x-transition.opacity
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50 p-4"
    >
        <div 
            x-transition.scale
            @click.outside="openEditModal = false"
            class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-lg relative max-h-[90vh] overflow-y-auto"
        >
            <h2 class="text-2xl font-bold text-[#0C4B54] mb-6 border-b pb-3">Editar Materia</h2>
            
            <form :action="selectedMateria ? '/materias/' + selectedMateria.id : '#'" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nombre</label>
                    <input type="text" name="nombre" :value="selectedMateria?.nombre" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm p-3 focus:ring-[#0C4B54]">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Código</label>
                    <input type="text" name="codigo" :value="selectedMateria?.codigo" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm p-3 focus:ring-[#0C4B54]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Carrera</label>
                    <select name="carrera_id" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm p-3 focus:ring-[#0C4B54]">
                        <template x-for="c in carreras" :key="c.id">
                            <option 
                                :value="c.id" 
                                x-text="c.nombre_carrera || c.nombre" 
                                :selected="selectedMateria?.carreras.some(sc => sc.id == c.id)"
                            ></option>
                        </template>
                    </select>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Carga Semanal</label>
                        <input type="number" name="horas_semanales" :value="selectedMateria?.horas_semanales" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm p-3 focus:ring-[#0C4B54]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Total Horas</label>
                        <input type="number" name="horas_totales" :value="selectedMateria?.horas_totales" required class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm p-3 focus:ring-[#0C4B54]">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <select name="status" class="mt-1 block w-full rounded-xl border-gray-300 shadow-sm p-3 focus:ring-[#0C4B54]">
                        <option value="1" :selected="selectedMateria?.status == 1">Activa</option>
                        <option value="0" :selected="selectedMateria?.status == 0">Inactiva</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="openEditModal = false" class="px-5 py-2 rounded-xl bg-gray-300 hover:bg-gray-400 transition text-gray-800">Cancelar</button>
                    <button type="submit" class="px-6 py-2 rounded-xl bg-[#0C4B54] text-white font-bold hover:bg-[#093D45] transition shadow">Actualizar</button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection