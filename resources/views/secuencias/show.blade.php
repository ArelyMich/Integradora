@extends('layouts.principal')

@section('content')
@php
    $user = auth()->user();
    $roleIds = $user?->roles?->pluck('id')->all() ?? [];
    $isReviewer = in_array(3, $roleIds, true);
    $isDocente = in_array(4, $roleIds, true);
@endphp

<div x-data="pdfEditorSelector()" class="min-h-screen bg-slate-100/70 p-4 md:p-8">
    <div class="mx-auto max-w-7xl space-y-6">
        <section class="overflow-hidden rounded-[2rem] bg-white shadow-xl shadow-slate-200/60">
            <div class="grid gap-4 bg-[radial-gradient(circle_at_top_left,_rgba(12,75,84,0.16),_transparent_30%),linear-gradient(135deg,_#ffffff,_#f5f7fb)] px-6 py-8 md:grid-cols-[1.2fr_.8fr]">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Panel de secuencia</p>
                    <h1 class="mt-2 text-3xl font-black text-slate-900 md:text-4xl">
                        {{ $secuencia->materia?->nombre ?? 'Secuencia' }}
                    </h1>
                    <p class="mt-2 text-sm font-semibold text-slate-600">
                        {{ $secuencia->carrera?->nombre_carrera ?? 'Sin carrera' }} · {{ $secuencia->periodo?->nombre ?? 'Sin periodo' }} {{ $secuencia->periodo?->anio ?? '' }}
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">ID #{{ $secuencia->id }}</span>
                        <span class="rounded-full px-3 py-1 text-xs font-black {{ $secuencia->status ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            {{ $secuencia->status ? 'Activa' : 'Inactiva' }}
                        </span>
                        <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-black text-sky-700">{{ ucfirst($secuencia->estatus) }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-3xl bg-slate-900 p-4 text-white">
                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-white/60">Docente</p>
                        <p class="mt-2 text-sm font-bold">{{ trim(($secuencia->docente?->name ?? '') . ' ' . ($secuencia->docente?->apellido_paterno ?? '')) ?: 'Sin docente' }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-900 p-4 text-white">
                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-white/60">Revisor</p>
                        <p class="mt-2 text-sm font-bold">{{ trim(($secuencia->revisor?->name ?? '') . ' ' . ($secuencia->revisor?->apellido_paterno ?? '')) ?: 'Sin revisor' }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-900 p-4 text-white">
                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-white/60">Director</p>
                        <p class="mt-2 text-sm font-bold">{{ trim(($secuencia->director?->name ?? '') . ' ' . ($secuencia->director?->apellido_paterno ?? '')) ?: 'Sin director' }}</p>
                    </div>
                    <div class="rounded-3xl bg-slate-900 p-4 text-white">
                        <p class="text-[11px] font-black uppercase tracking-[0.2em] text-white/60">Entrega</p>
                        <p class="mt-2 text-sm font-bold">{{ optional($secuencia->fecha_entrega)->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700 shadow-sm">
                {{ session('success') }}
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

        <section class="grid gap-6 lg:grid-cols-[1.4fr_.9fr]">
            <div class="rounded-[2rem] bg-white p-5 shadow-xl shadow-slate-200/60">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Archivo asignado</p>
                        <h2 class="mt-1 text-xl font-black text-slate-900">Visor de secuencia</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('secuencias.editor', $secuencia) }}" class="rounded-2xl bg-sky-600 px-4 py-2 text-xs font-black text-white transition hover:bg-sky-700">
                            Editor grande OCR
                        </a>
                        <a href="{{ $archivoUrl }}" target="_blank" class="rounded-2xl bg-slate-100 px-4 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-200">
                            Abrir en pestaña
                        </a>
                    </div>
                </div>

                <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                    @if ($isPreviewable)
                        <div class="relative h-[70vh] w-full" x-ref="pdfCanvas" @mouseleave="finishSelection()">
                            <iframe src="{{ $archivoUrl }}#zoom=page-width" class="h-full w-full" title="Archivo de secuencia"></iframe>

                            <div
                                x-show="selectorEnabled"
                                class="absolute inset-0 cursor-crosshair bg-sky-500/5"
                                @mousedown.prevent="startSelection($event)"
                                @mousemove.prevent="moveSelection($event)"
                                @mouseup.prevent="finishSelection()"
                                style="display: none;"
                            ></div>

                            <div
                                x-show="selectorEnabled && hasRect"
                                class="pointer-events-none absolute border-2 border-sky-600 bg-sky-200/20"
                                :style="`left:${rectPx.left}px;top:${rectPx.top}px;width:${rectPx.width}px;height:${rectPx.height}px;`"
                                style="display: none;"
                            ></div>
                        </div>
                    @else
                        <div class="p-8 text-center">
                            <p class="text-sm font-semibold text-slate-600">El tipo de archivo no admite vista embebida.</p>
                            <a href="{{ $archivoUrl }}" target="_blank" class="mt-3 inline-flex rounded-2xl bg-[#0C4B54] px-4 py-2 text-xs font-black text-white">
                                Descargar / Abrir archivo
                            </a>
                        </div>
                    @endif
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                    <p class="text-sm font-black text-slate-800">Editar archivo de la secuencia</p>
                    <p class="mt-1 text-xs text-slate-500">Sube una nueva versión del archivo para reemplazar la actual.</p>

                    <form action="{{ route('secuencias.actualizarArchivo', $secuencia) }}" method="POST" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3 md:flex-row md:items-center">
                        @csrf
                        <input
                            type="file"
                            name="archivo_secuencia"
                            accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                            required
                            class="block w-full text-xs text-slate-500 file:mr-3 file:rounded-xl file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-black file:text-white md:w-auto"
                        >
                        <button type="submit" class="rounded-2xl bg-[#0C4B54] px-5 py-2.5 text-xs font-black text-white transition hover:bg-[#083840]">
                            Reemplazar archivo
                        </button>
                    </form>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                    <p class="text-sm font-black text-slate-800">Editor interno PDF por recuadro</p>
                    <p class="mt-1 text-xs text-slate-500">Genera una version anotada con Titulo, Info y Comentario. Puedes adjuntar OCR del archivo actual.</p>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            @click="toggleSelector()"
                            class="rounded-xl px-4 py-2 text-xs font-black transition"
                            :class="selectorEnabled ? 'bg-sky-600 text-white hover:bg-sky-700' : 'bg-slate-900 text-white hover:bg-slate-700'"
                        >
                            <span x-text="selectorEnabled ? 'Selector activado' : 'Activar selector visual'"></span>
                        </button>
                        <button type="button" @click="clearSelection()" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-600 hover:bg-slate-50">
                            Limpiar recuadro
                        </button>
                        <p class="text-xs text-slate-500">Tip: activa selector y arrastra sobre el visor PDF para autollenar coordenadas.</p>
                    </div>

                    <form action="{{ route('secuencias.anotarArchivo', $secuencia) }}" method="POST" class="mt-4 space-y-3">
                        @csrf

                        <div class="grid gap-3 md:grid-cols-5">
                            <div>
                                <label class="mb-1 block text-[11px] font-black uppercase text-slate-500">Pagina</label>
                                <input type="number" name="page" min="1" value="1" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-[#0C4B54]">
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-black uppercase text-slate-500">X</label>
                                <input type="number" name="x" min="0" step="0.1" x-model="formRect.x" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-[#0C4B54]">
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-black uppercase text-slate-500">Y</label>
                                <input type="number" name="y" min="0" step="0.1" x-model="formRect.y" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-[#0C4B54]">
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-black uppercase text-slate-500">Ancho</label>
                                <input type="number" name="width" min="10" step="0.1" x-model="formRect.width" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-[#0C4B54]">
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-black uppercase text-slate-500">Alto</label>
                                <input type="number" name="height" min="10" step="0.1" x-model="formRect.height" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-[#0C4B54]">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase text-slate-500">Titulo</label>
                            <input type="text" name="titulo" maxlength="120" placeholder="Ej. Observacion de Revisor" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-[#0C4B54]">
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase text-slate-500">Info</label>
                            <input type="text" name="info" maxlength="300" placeholder="Ej. Unidad 1 - Seccion apertura" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-[#0C4B54]">
                        </div>

                        <div>
                            <label class="mb-1 block text-[11px] font-black uppercase text-slate-500">Comentario</label>
                            <textarea name="comentario" rows="4" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-[#0C4B54]" placeholder="Escribe aqui el comentario que se incrustara en el recuadro del PDF"></textarea>
                        </div>

                        <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600">
                            <input type="checkbox" name="usar_ocr" value="1" class="h-4 w-4 rounded border-slate-300 text-[#0C4B54] focus:ring-[#0C4B54]">
                            Incluir OCR automatico del archivo actual dentro del comentario
                        </label>

                        <button type="submit" class="w-full rounded-2xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-slate-700">
                            Generar version anotada PDF
                        </button>
                    </form>
                </div>

                <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <p class="text-sm font-black text-slate-800">Historial de versiones</p>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-black text-slate-600">{{ $secuencia->archivoVersiones->count() }}</span>
                    </div>

                    <div class="mt-3 space-y-2 max-h-56 overflow-y-auto pr-1">
                        @forelse ($secuencia->archivoVersiones as $version)
                            <article class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-xs font-black text-slate-800">{{ $version->archivo_nombre_original ?: 'Archivo sin nombre' }}</p>
                                        <p class="mt-1 text-[11px] text-slate-500">
                                            {{ strtoupper($version->accion) }} · {{ optional($version->created_at)->format('d/m/Y H:i') }} · {{ $version->usuario?->name ?? 'Sistema' }}
                                        </p>
                                    </div>
                                    <a href="{{ route('secuencias.verArchivoVersion', [$secuencia, $version]) }}" target="_blank" class="rounded-lg bg-slate-900 px-3 py-1.5 text-[11px] font-black text-white">
                                        Ver
                                    </a>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-3 py-4 text-center text-xs text-slate-500">
                                No hay versiones registradas aún.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="space-y-6">
                <section class="rounded-[2rem] bg-white p-5 shadow-xl shadow-slate-200/60">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Estatus y dictamen</p>
                    <h2 class="mt-1 text-xl font-black text-slate-900">Seguimiento académico</h2>

                    @if ($isReviewer)
                        <form action="{{ route('secuencias.actualizarEstatusAcademico', $secuencia) }}" method="POST" class="mt-4 space-y-3">
                            @csrf
                            @method('PUT')
                            <select name="estatus" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 outline-none focus:border-[#0C4B54]">
                                <option value="revision" {{ $secuencia->estatus === 'revision' ? 'selected' : '' }}>Revisión</option>
                                <option value="correcciones" {{ $secuencia->estatus === 'correcciones' ? 'selected' : '' }}>Correcciones</option>
                                <option value="aprobada" {{ $secuencia->estatus === 'aprobada' ? 'selected' : '' }}>Aprobada</option>
                            </select>
                            <textarea name="motivo" rows="4" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#0C4B54]" placeholder="Motivo del cambio de estatus"></textarea>
                            <button type="submit" class="w-full rounded-2xl bg-sky-600 px-5 py-3 text-sm font-black text-white transition hover:bg-sky-700">
                                Guardar estatus
                            </button>
                        </form>
                    @else
                        <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                            Estatus actual: <span class="font-black text-slate-800">{{ ucfirst($secuencia->estatus) }}</span>
                        </div>
                    @endif
                </section>

                <section class="rounded-[2rem] bg-white p-5 shadow-xl shadow-slate-200/60">
                    <p class="text-xs font-black uppercase tracking-[0.25em] text-slate-400">Comentarios</p>
                    <h2 class="mt-1 text-xl font-black text-slate-900">Observaciones y respuestas</h2>

                    <form action="{{ route('secuencias.comentarios.guardar', $secuencia) }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <textarea name="comentario" rows="3" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none focus:border-[#0C4B54]" placeholder="Escribe un comentario u observación"></textarea>
                        <button type="submit" class="w-full rounded-2xl bg-[#0C4B54] px-5 py-3 text-sm font-black text-white transition hover:bg-[#083840]">
                            Registrar comentario
                        </button>
                    </form>

                    <div class="mt-5 space-y-4">
                        @forelse ($secuencia->comentarios as $comentario)
                            <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm font-black text-slate-800">{{ $comentario->usuario?->name ?? 'Usuario' }}</p>
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-black uppercase {{ $comentario->estatus === 'cerrado' ? 'bg-emerald-100 text-emerald-700' : ($comentario->estatus === 'respondido' ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ $comentario->estatus }}
                                    </span>
                                </div>
                                <p class="mt-2 text-sm text-slate-700">{{ $comentario->comentario }}</p>

                                @if ($comentario->respuesta)
                                    <div class="mt-3 rounded-xl border border-sky-200 bg-sky-50 p-3">
                                        <p class="text-xs font-black uppercase tracking-[0.2em] text-sky-600">Respuesta</p>
                                        <p class="mt-1 text-sm text-slate-700">{{ $comentario->respuesta }}</p>
                                        <p class="mt-1 text-[11px] font-semibold text-slate-500">{{ $comentario->respuestaUsuario?->name ?? 'Usuario' }}</p>
                                    </div>
                                @endif

                                @if ($isReviewer || $isDocente)
                                    <form action="{{ route('secuencias.comentarios.responder', [$secuencia, $comentario]) }}" method="POST" class="mt-3 space-y-2">
                                        @csrf
                                        @method('PUT')
                                        <textarea name="respuesta" rows="2" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#0C4B54]" placeholder="Responder comentario"></textarea>
                                        <div class="flex gap-2">
                                            <select name="estatus" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-[#0C4B54]">
                                                <option value="pendiente" {{ $comentario->estatus === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                <option value="respondido" {{ $comentario->estatus === 'respondido' ? 'selected' : '' }}>Respondido</option>
                                                <option value="cerrado" {{ $comentario->estatus === 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                                            </select>
                                            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-black text-white">Guardar</button>
                                        </div>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                Aún no hay comentarios registrados para esta secuencia.
                            </div>
                        @endforelse
                    </div>
                </section>
            </aside>
        </section>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pdfEditorSelector', () => ({
            selectorEnabled: false,
            selecting: false,
            hasRect: false,
            startPx: { x: 0, y: 0 },
            rectPx: { left: 0, top: 0, width: 0, height: 0 },
            formRect: {
                x: 12,
                y: 18,
                width: 90,
                height: 50,
            },
            toggleSelector() {
                this.selectorEnabled = !this.selectorEnabled;
                this.selecting = false;
            },
            startSelection(event) {
                const point = this.resolvePoint(event);
                this.selecting = true;
                this.hasRect = true;
                this.startPx = point;
                this.rectPx = { left: point.x, top: point.y, width: 0, height: 0 };
            },
            moveSelection(event) {
                if (!this.selecting) {
                    return;
                }

                const current = this.resolvePoint(event);
                const left = Math.min(this.startPx.x, current.x);
                const top = Math.min(this.startPx.y, current.y);
                const width = Math.abs(current.x - this.startPx.x);
                const height = Math.abs(current.y - this.startPx.y);

                this.rectPx = { left, top, width, height };
                this.syncFormRect();
            },
            finishSelection() {
                if (!this.selecting) {
                    return;
                }

                this.selecting = false;
                this.syncFormRect();
            },
            clearSelection() {
                this.hasRect = false;
                this.selecting = false;
                this.rectPx = { left: 0, top: 0, width: 0, height: 0 };
                this.formRect = { x: 12, y: 18, width: 90, height: 50 };
            },
            resolvePoint(event) {
                const bounds = this.$refs.pdfCanvas.getBoundingClientRect();
                const x = Math.max(0, Math.min(event.clientX - bounds.left, bounds.width));
                const y = Math.max(0, Math.min(event.clientY - bounds.top, bounds.height));

                return { x, y };
            },
            syncFormRect() {
                const bounds = this.$refs.pdfCanvas.getBoundingClientRect();

                if (!bounds.width || !bounds.height) {
                    return;
                }

                const pageWidthMm = 210;
                const pageHeightMm = 297;

                const xMm = (this.rectPx.left / bounds.width) * pageWidthMm;
                const yMm = (this.rectPx.top / bounds.height) * pageHeightMm;
                const widthMm = (this.rectPx.width / bounds.width) * pageWidthMm;
                const heightMm = (this.rectPx.height / bounds.height) * pageHeightMm;

                this.formRect = {
                    x: Number(Math.max(0, xMm).toFixed(1)),
                    y: Number(Math.max(0, yMm).toFixed(1)),
                    width: Number(Math.max(10, widthMm).toFixed(1)),
                    height: Number(Math.max(10, heightMm).toFixed(1)),
                };
            },
        }));
    });
</script>
@endsection
