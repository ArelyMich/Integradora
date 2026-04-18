@extends('layouts.principal')

@section('content')
@php
    $roleIds = auth()->user()?->roles?->pluck('id')->all() ?? [];
    $isReviewer = in_array(3, $roleIds, true);
@endphp
<div
    x-data="secuenciasDashboard({
        secuencias: {{ Js::from($secuencias->map(fn($secuencia) => [
            'id' => $secuencia->id,
            'materia' => $secuencia->materia?->nombre ?? 'Materia no disponible',
            'carrera' => $secuencia->carrera?->nombre_carrera ?? 'Carrera no disponible',
            'docente' => $secuencia->docente ? trim($secuencia->docente->name . ' ' . $secuencia->docente->apellido_paterno) : 'Sin docente asignado',
            'periodo' => $secuencia->periodo ? ($secuencia->periodo->nombre . ' ' . $secuencia->periodo->anio) : 'Sin periodo',
            'estatus' => $secuencia->estatus,
            'status' => (int) $secuencia->status,
            'director' => $secuencia->director ? trim($secuencia->director->name . ' ' . $secuencia->director->apellido_paterno) : 'Sin director',
            'revisor' => $secuencia->revisor ? trim($secuencia->revisor->name . ' ' . $secuencia->revisor->apellido_paterno) : 'Sin revisor',
            'revisor_id' => $secuencia->revisor_id,
            'horas_programadas' => $secuencia->horas_programadas ?: 'Sin captura',
            'fecha_entrega' => optional($secuencia->fecha_entrega)->format('d/m/Y H:i'),
            'created_at' => optional($secuencia->created_at)->format('d/m/Y H:i'),
        ])) }},
        statusBaseUrl: '{{ url('/secuencias') }}',
        academicStatusBaseUrl: '{{ url('/secuencias') }}',
        ocrUrl: '{{ route('secuencias.uploadAndExtract') }}',
        templatePdfUrl: '{{ asset('docs/secuencia-didactica-uth.pdf') }}',
        canReview: {{ $isReviewer ? 'true' : 'false' }}
    })"
    class="min-h-screen bg-slate-100/70 p-4 md:p-8"
    x-cloak>
    <div class="mx-auto max-w-7xl space-y-8">
        <section class="overflow-hidden rounded-[2rem] bg-white shadow-xl shadow-slate-200/60">
            <div class="grid gap-3 bg-[radial-gradient(circle_at_top_left,_rgba(12,75,84,0.16),_transparent_30%),linear-gradient(135deg,_#ffffff,_#f5f7fb)] px-6 py-8 md:grid-cols-2">
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

        @if ($isReviewer)
        <section class="rounded-[2rem] border border-sky-100 bg-gradient-to-r from-sky-50 to-cyan-50 p-5 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-sky-600">Plantilla oficial</p>
                    <h2 class="mt-1 text-xl font-black text-slate-900">Vista rápida del formato SECUENCIA DIDÁCTICA UTH</h2>
                    <p class="mt-1 text-sm text-slate-600">Presiona el recuadro para abrir el PDF en un visor rápido y revisar el formato antes del dictamen.</p>
                </div>
                <button
                    type="button"
                    @click="openTemplateModal()"
                    class="rounded-2xl bg-sky-600 px-5 py-3 text-sm font-black text-white shadow-lg transition hover:bg-sky-700"
                >
                    Abrir formato rápido
                </button>
            </div>
        </section>
        @endif

        <section class="grid gap-6 lg:grid-cols-[1.55fr_.85fr]">
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

                <div class="mt-6 overflow-hidden rounded-[1.75rem] border border-slate-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left">
                            <thead class="bg-slate-900 text-xs font-black uppercase tracking-[0.2em] text-white">
                                <tr>
                                    <th class="px-4 py-4">ID</th>
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
                                        <td class="px-4 py-4 text-sm font-black text-slate-500" x-text="'#' + secuencia.id"></td>
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
                                                    class="rounded-2xl border border-cyan-200 bg-cyan-50 px-3 py-2 text-xs font-black text-cyan-700 transition hover:border-cyan-300 hover:bg-cyan-100"
                                                >
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
                                                <template x-if="canReview">
                                                    <button
                                                        @click="openTemplateModal()"
                                                        class="rounded-2xl bg-cyan-50 px-3 py-2 text-xs font-black text-cyan-700 transition hover:bg-cyan-100">
                                                        Formato rápido
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

                <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-sm font-black text-slate-800">Ayuda con OCR</p>
                            <p class="text-xs text-slate-500">Sube PDF o imagen para extraer texto y apoyar tu revision.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <input type="file" accept=".pdf,.png,.jpg,.jpeg" @change="setOcrFile($event)" class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-black file:text-white md:w-auto">
                            <button type="button" @click="runOcrFromFile()" class="rounded-2xl bg-slate-900 px-4 py-2 text-xs font-black text-white transition hover:bg-slate-700" :disabled="ocrLoading || !ocrFile">
                                <span x-show="!ocrLoading">Extraer OCR</span>
                                <span x-show="ocrLoading" style="display: none;">Procesando...</span>
                            </button>
                        </div>
                    </div>

                    <template x-if="ocrError">
                        <p class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700" x-text="ocrError"></p>
                    </template>

                    <template x-if="Object.keys(ocrData).length">
                        <div class="mt-4 grid gap-2 rounded-2xl bg-white p-3 text-xs text-slate-700 md:grid-cols-2">
                            <template x-for="entry in Object.entries(ocrData)" :key="entry[0]">
                                <p><span class="font-black" x-text="entry[0]"></span>: <span x-text="entry[1]"></span></p>
                            </template>
                        </div>
                    </template>
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
        <div class="relative z-10 h-[90vh] w-full max-w-6xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
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
            <iframe :src="templatePdfUrl + '#zoom=page-width'" class="h-[calc(90vh-60px)] w-full" title="Formato de secuencia didactica"></iframe>
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