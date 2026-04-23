<link rel="stylesheet" 
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@extends('layouts.principal')
@section('content')
    {{-- 
    SOLUCIÓN DE LÓGICA:
    1. Mapeamos los datos aquí mismo para incluir la URL generada por Laravel (route).
       Así no dependemos de construir strings en JS y el enlace nunca se rompe.
    2. Pasamos el permiso de 'view' como variable global.
--}}
    <div x-data="permissionsTable({
        users: {{ Js::from(
            $permisos->map(
                fn($p) => [
                    'id' => $p->id,
                    'sitio' => $p->sitio,
                    'ruta' => $p->ruta,
                    'status' => $p->status,
                    // AQUÍ ESTÁ EL ARREGLO: Generamos la ruta exacta desde PHP
                    'url_gestionar' => route('permisos.view', ['id' => $p->id]),
                ],
            ),
        ) }},
        csrf: '{{ csrf_token() }}',
        // Pasamos tu validación de seguridad a JS
        canView: {{ Auth()->user()->hasPermission('permisos.view') ? 'true' : 'false' }}
    })" class="p-8 min-h-screen bg-gray-100 font-sans" x-cloak>

        <div class="flex items-center gap-4">

            <div class="p-3 

rounded-xl 

bg-gradient-to-r 
from-[#326D6C] 
to-[#568F7C]

text-white shadow-md">

                <i class="fa-solid fa-shield-halved text-xl"></i>

            </div>

            <div>

                <h1 class="text-3xl font-bold text-[#173C4C]">

                    Permisos del Sistema

                </h1>

                <p class="text-gray-500 text-sm">

                    Administración centralizada de accesos

                </p>

            </div>

        </div>

        <div class="bg-white p-6 rounded-2xl shadow mb-6 flex flex-wrap items-center gap-6">

<div class="flex-1 relative">

<i class="fa-solid fa-magnifying-glass 

absolute 

left-3 

top-1/2 

-translate-y-1/2 

text-gray-400">

</i>

<input 

x-model="search"

@input="page = 1"

type="text"

placeholder="Buscar permiso..."

class="w-full 

pl-10 pr-4 py-2 

border 

rounded-xl 

shadow-sm 

bg-gray-50

focus:bg-white

focus:ring-2 

focus:ring-[#326D6C]">

</div>

            <div>
                <label class="text-sm font-semibold text-gray-700">Estado</label>
                <select x-model="filtroEstado" @change="page = 1"
                    class="mt-1 px-4 py-2 border rounded-xl shadow-sm focus:ring-2 focus:ring-[#326D6C]">
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>
            </div>

            {{-- Este botón se mantiene con Blade porque es estático --}}
            @if (Auth()->user()->hasPermission('permisos.store'))
                <div class="ml-auto">
                    <button @click="openModal = true"
                        class="bg-gradient-to-r 
from-[#326D6C] 
to-[#568F7C] text-white px-5 py-2 rounded-xl shadow hover:bg-[#093D45] transition flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Permiso
                    </button>
                </div>
            @endif

        </div>

        <div class="bg-white rounded-2xl shadow overflow-hidden">

            <table class="min-w-full text-left">
                <thead class="bg-[#0C4B54] text-white">
                    <tr>
                        <th class="px-6 py-3">ID</th>
                        <th class="px-6 py-3">Nombre</th>
                        <th class="px-6 py-3">Ruta</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white text-sm">
                    <template x-for="permiso in paginatedData" :key="permiso.id">
                        <tr class="border-b hover:bg-[#ECFDF5] transition"
                            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100">
                            <td class="px-6 py-4" x-text="permiso.id"></td>
                            <td class="px-6 py-4 font-semibold text-gray-800" x-text="permiso.sitio"></td>
                            <td class="px-6 py-4" x-text="permiso.ruta"></td>

                            <td class="px-6 py-4">
                                <form action="{{ route('permisos.desactivar') }}" method="POST">
                                    <input type="hidden" name="_token" :value="csrf">
                                    <input type="hidden" name="permiso_id" :value="permiso.id">
                                    <input type="hidden" name="new_status" :value="permiso.status == 1 ? 0 : 1">

                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" :checked="permiso.status == 1"
                                            @change="$el.closest('form').submit()">
                                        <div
                                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-[#0C4B54]/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#326D6C]">
                                        </div>
                                        <span class="ml-3 

px-3 py-1 

rounded-full 

text-xs font-semibold"
                                            :class="permiso.status == 1
                                            
                                                ?
                                                'bg-emerald-100 text-emerald-700'
                                            
                                                :
                                                'bg-red-100 text-red-700'"
                                            x-text="permiso.status == 1 

? 'Activo' 

: 'Inactivo'">

                                        </span>
                                    </label>
                                </form>
                            </td>

                            <td class="px-6 py-4 text-right flex gap-2 justify-end">
                                <template x-if="canView">
                                   <a :href="permiso.url_gestionar"

class="inline-flex 

items-center 

justify-center 

gap-2

px-4 py-2 

text-sm 

font-medium 

rounded-xl 

text-white 

bg-gradient-to-r 
from-[#326D6C] 
to-[#568F7C]

shadow-md 

transition 

hover:shadow-lg

hover:scale-[1.02]

active:scale-[0.98]">

<i class="fa-solid fa-gear"></i>

Gestionar

</a>

                                </template>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="paginatedData.length === 0">
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                            No se encontraron resultados.
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>

        <div x-show="filteredData.length > 0"
            class="bg-gray-50 px-6 py-4 rounded-b-2xl border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Mostrando <span class="font-bold text-gray-900" x-text="startItem"></span> - <span
                    class="font-bold text-gray-900" x-text="endItem"></span> de <span class="font-bold text-gray-900"
                    x-text="filteredData.length"></span>
            </div>

            <div class="flex gap-2">
                <button @click="prevPage" :disabled="page === 1"
                    class="px-3 py-1 rounded-lg border bg-white disabled:opacity-50 hover:bg-gray-100 transition text-sm">Anterior</button>
                <button @click="nextPage" :disabled="page === totalPages"
                    class="px-3 py-1 rounded-lg border bg-white disabled:opacity-50 hover:bg-gray-100 transition text-sm">Siguiente</button>
            </div>
        </div>

        <div x-show="openModal" style="display: none;" x-transition.opacity
            class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center z-50 p-4">
            <div x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0" @click.outside="openModal = false"
               class="bg-white rounded-3xl shadow-[0_30px_80px_rgba(0,0,0,0.15)] border border-gray-200 p-8 w-full max-w-lg relative">

                <h2 class="text-3xl font-bold text-[#0C4B54] mb-6">Crear Permiso</h2>

                <form action="{{ route('permisos.store') }}" method="POST">
                    @csrf
                    <label class="block font-semibold text-gray-700 mb-1">Nombre del Permiso</label>
                    <input type="text" name="sitio"
                        class="w-full px-4 py-3 mb-5 rounded-xl border bg-gray-50 focus:ring-2 focus:ring-[#0C4B54]">

                    <label class="block font-semibold text-gray-700 mb-1">Ruta del permiso</label>
                    <input type="text" name="ruta"
                        class="w-full px-4 py-3 mb-5 rounded-xl border bg-gray-50 focus:ring-2 focus:ring-[#0C4B54]">

                    <label class="block font-semibold text-gray-700 mb-1">Estado</label>
                    <select name="status"
                        class="w-full px-4 py-3 mb-5 rounded-xl border bg-gray-50 focus:ring-2 focus:ring-[#0C4B54]">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>

                    <div class="flex justify-end gap-3">
                        <button type="button" @click="openModal = false"
                            class="px-5 py-2 rounded-xl bg-gray-300 hover:bg-gray-400 transition">Cancelar</button>
                        <button type="submit"
                            class="px-6 py-2 rounded-xl bg-[#0C4B54] text-white font-semibold hover:bg-[#093D45] transition shadow">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('permissionsTable', (config) => ({
                items: config.users,
                csrf: config.csrf,
                canView: config.canView, // Variable de seguridad

                search: '',
                filtroEstado: '',
                openModal: false,

                // Paginación
                page: 1,
                pageSize: 10,

                get filteredData() {
                    return this.items.filter(item => {
                        const matchesSearch = item.sitio.toLowerCase().includes(this.search
                                .toLowerCase()) ||
                            item.ruta.toLowerCase().includes(this.search.toLowerCase());
                        const matchesStatus = this.filtroEstado === '' ||
                            (this.filtroEstado === 'activo' && item.status == 1) ||
                            (this.filtroEstado === 'inactivo' && item.status == 0);
                        return matchesSearch && matchesStatus;
                    });
                },

                get paginatedData() {
                    const start = (this.page - 1) * this.pageSize;
                    const end = start + this.pageSize;
                    return this.filteredData.slice(start, end);
                },

                get totalPages() {
                    return Math.ceil(this.filteredData.length / this.pageSize);
                },
                get startItem() {
                    return this.filteredData.length === 0 ? 0 : (this.page - 1) * this.pageSize + 1;
                },
                get endItem() {
                    return Math.min(this.page * this.pageSize, this.filteredData.length);
                },

                nextPage() {
                    if (this.page < this.totalPages) this.page++;
                },
                prevPage() {
                    if (this.page > 1) this.page--;
                }
            }));
        });
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection
