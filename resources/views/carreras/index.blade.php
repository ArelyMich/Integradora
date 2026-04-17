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
    class="min-h-screen bg-slate-100/70 p-4 md:p-8"
    x-cloak>
    <div class="mx-auto max-w-7xl space-y-8">
        <section class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#0C4B54] via-[#145d69] to-[#E29F33] text-white shadow-2xl shadow-slate-300/40">
            <div class="grid gap-8 px-6 py-8 md:grid-cols-[1.4fr_.9fr] md:px-10 md:py-10">
                <div class="space-y-4">
                    <div>
                        <h1 class="text-3xl font-black tracking-tight md:text-5xl">Carreras </h1>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @if (Auth::user()->hasPermission('carreras.store'))
                        <button
                            @click="openCreateModal()"
                            class="rounded-2xl bg-white px-5 py-3 text-sm font-black text-[#0C4B54] shadow-lg transition hover:-translate-y-0.5">
                            Nueva carrera
                        </button>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-2">
                    <div class="rounded-3xl bg-white/12 p-5 backdrop-blur-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/70">Total</p>
                        <p class="mt-3 text-4xl font-black" x-text="carreras.length"></p>
                    </div>
                    <div class="rounded-3xl bg-white/12 p-5 backdrop-blur-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/70">Activas</p>
                        <p class="mt-3 text-4xl font-black" x-text="activasCount"></p>
                    </div>
                    <div class="rounded-3xl bg-white/12 p-5 backdrop-blur-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/70">Inactivas</p>
                        <p class="mt-3 text-4xl font-black" x-text="inactivasCount"></p>
                    </div>
                    <div class="rounded-3xl bg-white/12 p-5 backdrop-blur-sm">
                        <p class="text-xs font-bold uppercase tracking-[0.25em] text-white/70">Con Director</p>
                        <p class="mt-3 text-4xl font-black" x-text="conDirectorCount"></p>
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

        <section class="grid gap-6 lg:grid-cols-[1.5fr_.9fr]">
            <div class="rounded-[2rem] bg-white p-6 shadow-xl shadow-slate-200/60">
                <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Explorador</p>
                        <h2 class="mt-1 text-2xl font-black text-slate-900">Catálogo de carreras</h2>
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

                <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <template x-for="carrera in filteredCarreras" :key="carrera.id">
                        <article class="relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-slate-50/60 p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="absolute inset-x-0 top-0 h-1.5" :class="carrera.status ? 'bg-emerald-500' : 'bg-rose-500'"></div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400" x-text="'Carrera #' + carrera.id"></p>
                                    <h3 class="mt-2 text-lg font-black leading-tight text-slate-900" x-text="carrera.nombre"></h3>
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

                            <div class="mt-5 grid grid-cols-2 gap-3">
                                @if (Auth::user()->hasPermission('carreras.asignarDirector'))
                                <button
                                    @click="openAssignModal(carrera)"
                                    class="rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm font-black text-slate-700 transition hover:border-[#0C4B54] hover:text-[#0C4B54]">
                                    Director
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

            <aside class="rounded-[2rem] bg-white p-6 shadow-xl shadow-slate-200/60">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Historial</p>
                        <h2 class="mt-1 text-2xl font-black text-slate-900">Últimos cambios</h2>
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
                        Aún no hay movimientos registrados para carreras.
                    </div>
                    @endforelse
                </div>
            </aside>
        </section>
    </div>

    <div x-show="modalAssignOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
        <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" @click="closeAssignModal()"></div>
        <div class="relative z-10 w-full max-w-lg rounded-[2rem] bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Asignación</p>
                    <h3 class="mt-1 text-2xl font-black text-slate-900">Director de carrera</h3>
                    <p class="mt-2 text-sm text-slate-500" x-text="selectedCarreraNombre"></p>
                </div>
                <button @click="closeAssignModal()" class="rounded-full bg-slate-100 px-3 py-2 text-slate-500 transition hover:bg-slate-200">×</button>
            </div>

            <form action="{{ route('carreras.asignarDirector') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                <input type="hidden" name="carrera_id" :value="selectedCarreraId">

                <div>
                    <label class="text-sm font-bold text-slate-600">Director</label>
                    <select name="director_id" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#0C4B54]">
                        <option value="">Seleccione un director</option>
                        <template x-for="director in directores" :key="director.id">
                            <option :value="director.id" x-text="director.nombre" :selected="director.id === selectedDirectorId"></option>
                        </template>
                    </select>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="closeAssignModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-600">Cancelar</button>
                    <button type="submit" class="rounded-2xl bg-[#0C4B54] px-5 py-3 text-sm font-black text-white shadow-lg">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="modalProfesoresOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
        <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" @click="closeProfesoresModal()"></div>
        <div class="relative z-10 w-full max-w-2xl rounded-[2rem] bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Claustro</p>
                    <h3 class="mt-1 text-2xl font-black text-slate-900">Docentes asignados</h3>
                    <p class="mt-2 text-sm text-slate-500" x-text="selectedCarreraNombre"></p>
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

    <div x-show="modalCreateOpen" class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display: none;">
        <div class="absolute inset-0 bg-slate-950/55 backdrop-blur-sm" @click="closeCreateModal()"></div>
        <div class="relative z-10 w-full max-w-xl rounded-[2rem] bg-white p-6 shadow-2xl">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-slate-400">Alta</p>
                    <h3 class="mt-1 text-2xl font-black text-slate-900">Registrar nueva carrera</h3>
                </div>
                <button @click="closeCreateModal()" class="rounded-full bg-slate-100 px-3 py-2 text-slate-500 transition hover:bg-slate-200">×</button>
            </div>

            <form action="{{ route('carreras.store') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label class="text-sm font-bold text-slate-600">Nombre oficial</label>
                    <input type="text" name="nombre_carrera" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#0C4B54]" placeholder="Ej. Ingeniería Industrial">
                </div>
                <div>
                    <label class="text-sm font-bold text-slate-600">Descripción</label>
                    <textarea name="descripcion" rows="4" class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#0C4B54]" placeholder="Resumen breve de la carrera"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="closeCreateModal()" class="rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-600">Cancelar</button>
                    <button type="submit" class="rounded-2xl bg-[#0C4B54] px-5 py-3 text-sm font-black text-white shadow-lg">Registrar</button>
                </div>
            </form>
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
                    <h3 class="mt-1 text-2xl font-black text-slate-900" x-text="selectedStatusValue === 0 ? 'Desactivar carrera' : 'Reactivar carrera'"></h3>
                    <p class="mt-2 text-sm text-slate-500">
                        Esta acción no elimina información. Solo cambia el estado operativo y deja registro en el historial con usuario, fecha y motivo.
                    </p>
                </div>
            </div>

            <form :action="selectedStatusAction" method="POST" class="mt-6 space-y-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="status" :value="selectedStatusValue">

                <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-700">
                    <span class="font-black text-slate-900">Carrera:</span>
                    <span x-text="selectedCarreraNombre"></span>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-600">Motivo del cambio</label>
                    <textarea name="motivo" rows="4" required class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#0C4B54]" placeholder="Ej. Se desactiva por reestructuración temporal del programa"></textarea>
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
@endsection