@extends('layouts.principal')

@section('content')
@php
$archivoUrl = $archivoUrl ?? asset('docs/secuencia-didactica-uth.pdf');
$pdfUrl = $pdfUrl ?? null;
$pdfAvailable = isset($pdfAvailable) ? (bool) $pdfAvailable : ($pdfUrl !== null);

if ($pdfAvailable && $pdfUrl === null) {
$pdfUrl = $archivoUrl;
}

$contextComments = $secuencia->comentarios
->filter(fn ($comentario) =>
    $comentario->page &&
    $comentario->x !== null &&
    $comentario->y !== null &&
    $comentario->width !== null &&
    $comentario->height !== null &&
    (float) $comentario->width > 0 &&
    (float) $comentario->height > 0
)
->values();

$pdfComments = $contextComments
->map(fn ($comentario) => [
'id' => $comentario->id,
'page' => (int) $comentario->page,
'coord_mode' => $comentario->coord_mode ?: 'percent',
'x' => (float) $comentario->x,
'y' => (float) $comentario->y,
'width' => (float) $comentario->width,
'height' => (float) $comentario->height,
'titulo' => $comentario->titulo,
'info' => $comentario->info,
'comentario' => $comentario->comentario,
'texto_seleccionado' => $comentario->texto_seleccionado,
'autor' => $comentario->usuario?->name ?? 'Usuario',
'estatus' => $comentario->estatus ?? 'pendiente',
])
->values();
@endphp

<div
    x-data="pdfWorkspace({
        pdfUrl: @js($pdfUrl),
        rawFileUrl: @js($archivoUrl),
        comments: @js($pdfComments),
        secuenciaId: @js($secuencia->id),
    })"
    x-init="init()"
    class="min-h-screen bg-linear-to-b from-slate-50 to-slate-100 p-2 md:p-4">
    <div class="mx-auto grid max-w-[2200px] gap-3 xl:grid-cols-[3fr_.7fr]">
        <section class="panel-surface overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="editor-header flex flex-col gap-4 border-b border-slate-200 bg-linear-to-r from-white to-blue-50/40 px-6 py-6 lg:flex-row lg:items-center lg:justify-between shadow-sm">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Editor de Documentos PDF</p>
                    <h1 class="mt-2.5 text-3xl font-bold text-slate-900 tracking-tight">{{ $secuencia->materia?->nombre ?? 'Secuencia' }}</h1>
                    <p class="mt-2 text-sm font-medium text-slate-600">
                        {{ $secuencia->carrera?->nombre_carrera ?? 'Sin Carrera' }} · {{ $secuencia->periodo?->nombre ?? 'Sin Período' }} {{ $secuencia->periodo?->anio ?? '' }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        @click="toggleSelector()"
                        class="rounded-lg px-4 py-2.5 text-sm font-semibold transition-all"
                        :class="selectorEnabled ? 'bg-blue-600 text-white hover:bg-blue-700 shadow-md hover:shadow-lg' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'">
                        <span x-text="selectorEnabled ? 'Selector: ACT' : 'Selector: INACT'"></span>
                    </button>

                    <button type="button" @click="clearSelection()" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 hover:border-slate-400">
                        Limpiar Zona
                    </button>

                    <button type="button" @click="modalTodosComentariosAbierto = true" class="rounded-lg bg-blue-100 px-4 py-2.5 text-sm font-semibold text-blue-700 transition hover:bg-blue-200 shadow-sm hover:shadow-md">
                        Ver Observaciones
                    </button>

                    <a href="{{ route('secuencias.show', $secuencia) }}" class="rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-900 transition hover:bg-slate-200">
                        Volver al Panel
                    </a>
                </div>
            </div>

            <div class="grid gap-3 border-b border-slate-700/80 bg-slate-900/50 px-4 py-3 text-xs text-slate-200 md:grid-cols-4">
                <div class="rounded-2xl border border-slate-700/70 bg-slate-950/55 px-3 py-3 backdrop-blur-sm transition hover:border-cyan-400/40 hover:bg-slate-900/80">
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Modo de operación</p>
                    <p class="mt-1 font-semibold" x-text="selectorEnabled ? 'Crear, mover y redimensionar zona' : 'Solo lectura'"></p>
                </div>
                <div class="rounded-2xl border border-slate-700/70 bg-slate-950/55 px-3 py-3 backdrop-blur-sm transition hover:border-cyan-400/40 hover:bg-slate-900/80">
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Páginas renderizadas</p>
                    <p class="mt-1 font-semibold"><span x-text="totalPages"></span> totales</p>
                </div>
                <div class="rounded-2xl border border-slate-700/70 bg-slate-950/55 px-3 py-3 backdrop-blur-sm transition hover:border-cyan-400/40 hover:bg-slate-900/80">
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Selección actual</p>
                    <p class="mt-1 font-semibold" x-text="selection.page ? `Página ${selection.page}` : 'Sin seleccionar'"></p>
                </div>
                <div class="rounded-2xl border border-slate-700/70 bg-slate-950/55 px-3 py-3 backdrop-blur-sm transition hover:border-cyan-400/40 hover:bg-slate-900/80">
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500">Archivo original</p>
                    <a :href="rawFileUrl" target="_blank" class="mt-1 inline-flex font-semibold text-cyan-300 transition hover:text-cyan-200">Descargar archivo</a>
                </div>
            </div>

            @if (session('success'))
            <div class="border-b border-emerald-300 bg-emerald-50 px-6 py-3 text-sm font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="border-b border-red-300 bg-red-50 px-6 py-3 text-sm text-red-800">
                <p class="font-bold">Errores de validación:</p>
                <ul class="mt-2 space-y-1 text-xs">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="relative">
                <div x-show="loading" class="absolute inset-0 z-10 flex items-center justify-center bg-slate-950/75 text-sm font-black text-white" style="display: none;">
                    Renderizando documento...
                </div>

                <div x-show="pdfError" class="border-b border-rose-500/20 bg-rose-500/10 px-4 py-3 text-sm text-rose-100" x-text="pdfError" style="display: none;"></div>

                @if ($pdfAvailable)
                <div class="relative">
                    <div x-ref="viewerHost" class="h-[90vh] overflow-y-auto bg-slate-100 px-2 py-3 md:px-3"></div>

                    <div class="pointer-events-none absolute bottom-4 left-4 z-30 rounded-lg border border-blue-300 bg-white px-3 py-2 text-[11px] text-slate-700 shadow-lg">
                        <p class="font-bold uppercase tracking-wider text-blue-600">Estado</p>
                        <p class="mt-1 font-semibold" x-text="workflowStatus"></p>
                    </div>
                </div>
                @else
                <div class="flex h-[70vh] flex-col items-center justify-center gap-3 px-6 text-center text-slate-300">
                    <p class="text-lg font-black text-white">No hay un documento PDF disponible en el editor.</p>
                    <p class="max-w-xl text-sm">Puedes descargar el archivo original o subir una versión en PDF.</p>
                    <a href="{{ $archivoUrl }}" target="_blank" class="rounded-2xl bg-sky-600 px-4 py-2 text-xs font-black text-white hover:bg-sky-700">Descargar archivo</a>
                </div>
                @endif
            </div>
        </section>

        <aside class="editor-sidebar space-y-3 xl:sticky xl:top-3 xl:max-h-[94vh] xl:overflow-y-auto xl:pr-1">

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-lg">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Herramientas de Selección</p>
                <h2 class="mt-1 text-base font-bold text-slate-900">Región del Documento</h2>

                <div class="mt-4 grid gap-2 text-xs">
                    <div class="rounded-lg border bg-white px-4 py-3 transition duration-300"
                        :class="selection.page ? 'border-blue-300 bg-blue-50 shadow-sm' : 'border-slate-200'">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Página</p>
                        <p class="mt-1 font-semibold text-slate-800" x-text="selection.page || 'Sin página'"></p>
                    </div>
                </div>

                <div class="mt-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-[11px] text-slate-700">
                    <p class="font-bold uppercase tracking-wider text-blue-700">Mover y Redimensionar</p>
                    <p class="mt-1 leading-5 text-slate-600">Arrastra dentro del recuadro para moverlo. Usa las esquinas para redimensionar con precisión.</p>
                </div>

                <div class="mt-3 rounded-lg border bg-white p-4 transition duration-300"
                    :class="selection.page ? 'border-amber-300 bg-amber-50 shadow-sm' : 'border-slate-200'">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-600">Texto Detectado</p>
                        <button type="button" @click="useSelectedTextAsComment()" class="text-[11px] btn btn-blue-600  font-bold text-blue-600 transition hover:text-blue-700">Copiar al Comentario</button>
                    </div>
                    <p class="mt-2 whitespace-pre-wrap text-xs leading-5 text-slate-700" x-text="selection.text || 'Arrastra para crear un rectángulo. Cuando termines, aquí aparecerá el texto detectado.'"></p>
                </div>

                <div class="mt-4 space-y-3 rounded-lg border p-4 transition duration-300 bg-white"
                    :class="selectionGlow ? 'border-blue-300 shadow-md ring-1 ring-blue-100' : 'border-slate-200'">
                    <div>
                        <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-700">Título</label>
                        <input type="text" x-model="draftTitle" maxlength="120"
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="Ej. Corrección de formato">
                    </div>

                    <div>
                        <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-700">Info adicional</label>
                        <input type="text" x-model="draftInfo" maxlength="300" class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100" placeholder="Ej. Unidad 2 · Apertura">
                    </div>

                    <div>
                        <label class="mb-2 block text-[11px] font-bold uppercase tracking-wider text-slate-700">Comentario</label>
                        <textarea x-model="draftComment" rows="6"
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            placeholder="Escribe la observación ligada a la zona seleccionada"></textarea>
                    </div>

                    <p class="text-[11px] text-slate-600 font-medium" x-text="selectionHint"></p>
                </div>

                <div class="mt-5 grid gap-3">
                    <form id="selection-comment-form" action="{{ route('secuencias.comentarios.guardar', $secuencia) }}" method="POST">
                        @csrf
                        <input type="hidden" name="coord_mode" value="percent">
                        <input type="hidden" name="page" :value="selection.page || ''">
                        <input type="hidden" name="x" :value="selection.page ? selection.x : ''">
                        <input type="hidden" name="y" :value="selection.page ? selection.y : ''">
                        <input type="hidden" name="width" :value="selection.page ? selection.width : ''">
                        <input type="hidden" name="height" :value="selection.page ? selection.height : ''">
                        <input type="hidden" name="titulo" :value="draftTitle">
                        <input type="hidden" name="info" :value="draftInfo">
                        <input type="hidden" name="texto_seleccionado" :value="selection.text">
                        <input type="hidden" name="comentario" :value="draftComment">

                        <button type="submit" :disabled="!hasValidSelection" class="w-full rounded-lg bg-linear-to-r from-blue-600 via-blue-600 to-blue-700 px-4 py-3 text-sm font-semibold text-white transition shadow-lg hover:shadow-xl hover:from-blue-700 hover:via-blue-700 hover:to-blue-800 disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none disabled:from-slate-400 disabled:to-slate-400">
                            Guardar Observación
                        </button>
                    </form>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-lg">
                <div class="flex items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Observaciones del Documento</p>
                        <h2 class="mt-2.5 text-xl font-bold text-slate-900">Comentarios y Retroalimentación</h2>
                    </div>
                    <span class="rounded-full bg-linear-to-br from-blue-100 to-blue-50 px-3.5 py-1.5 text-[11px] font-bold text-blue-700 border border-blue-200 shadow-sm">{{ $contextComments->count() }}</span>
                </div>

                <div class="mt-4 max-h-[38vh] space-y-3 overflow-y-auto pr-1">
                    @forelse ($contextComments as $comentario)
                    <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:border-blue-300 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ $comentario->usuario?->name ?? 'Usuario' }}</p>
                                <p class="mt-1 text-[11px] text-slate-500 font-medium">
                                    {{ optional($comentario->created_at)->format('d/m/Y — H:i') }}
                                    @if ($comentario->page)
                                    • Página {{ $comentario->page }}
                                    @endif
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-wide {{ $comentario->estatus === 'resuelto' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($comentario->estatus === 'reabierto' ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-amber-100 text-amber-800 border border-amber-200') }}">
                                <span class="inline-block w-2.5 h-2.5 rounded-full {{ $comentario->estatus === 'resuelto' ? 'bg-emerald-600' : ($comentario->estatus === 'reabierto' ? 'bg-red-600' : 'bg-amber-600') }}"></span>
                                {{ $comentario->estatus === 'resuelto' ? 'Resuelto' : ($comentario->estatus === 'reabierto' ? 'Reabierto' : 'Pendiente') }}
                            </span>
                        </div>

                        <p class="mt-3 text-sm text-slate-700">{{ $comentario->comentario }}</p>

                        <button
                            type="button"
                            @click="jumpToComment({{ $comentario->id }}, {{ $comentario->page ?? 1 }})"
                           
                            class="mt-3 rounded-lg bg-blue-100 px-3 py-2 text-[11px] font-bold text-blue-700 transition hover:bg-blue-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            Ver Región
                        </button>
                    </article>
                    @empty
                    <div class="mt-4 rounded-lg border border-dashed border-slate-300 bg-linear-to-br from-slate-50 to-white px-6 py-12 text-center">
                        <p class="text-sm text-slate-600 font-semibold">Sin observaciones aún</p>
                        <p class="text-xs text-slate-500 mt-1.5 leading-5">Selecciona regiones en el PDF y añade observaciones. Aparecerán aquí.</p>
                    </div>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>

    <!-- MODAL DE TODAS LAS OBSERVACIONES -->
    <div x-show="modalTodosComentariosAbierto" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="modalTodosComentariosAbierto = false">
        <div class="w-full max-w-3xl mx-4 max-h-[90vh] rounded-2xl bg-white shadow-2xl flex flex-col" @click.stop>
            <!-- Header del Modal -->
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-gradient-to-r from-white to-blue-50 px-6 py-5 flex-shrink-0">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Panel de Observaciones</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900">Todas las Observaciones</h3>
                </div>
                <button type="button" @click="modalTodosComentariosAbierto = false" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700 flex-shrink-0">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Contenido - Lista de Comentarios -->
            <div class="overflow-y-auto flex-1 p-6 space-y-4">
                <template x-if="comments.length > 0">
                    <div class="space-y-4">
                        <template x-for="comentario in comments" :key="comentario.id">
                            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md hover:border-blue-300">
                                <!-- Header del comentario -->
                                <div class="flex items-start justify-between gap-4 mb-4">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900" x-text="comentario.usuario"></p>
                                        <p class="mt-1 text-[11px] text-slate-500 font-medium" x-text="comentario.fecha"></p>
                                    </div>
                                    <span class="inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-wide flex-shrink-0" x-bind:class="comentario.estatus === 'resuelto' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : (comentario.estatus === 'reabierto' ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-amber-100 text-amber-800 border border-amber-200')">
                                        <span class="inline-block w-2.5 h-2.5 rounded-full" x-bind:class="comentario.estatus === 'resuelto' ? 'bg-emerald-600' : (comentario.estatus === 'reabierto' ? 'bg-red-600' : 'bg-amber-600')"></span>
                                        <span x-text="comentario.estatus === 'resuelto' ? 'Resuelto' : (comentario.estatus === 'reabierto' ? 'Reabierto' : 'Pendiente')"></span>
                                    </span>
                                </div>

                                <!-- Título y Contexto -->
                                <template x-if="comentario.titulo || comentario.info">
                                    <div class="mb-4 rounded-lg border border-slate-200 bg-slate-50 p-3 space-y-2 text-xs">
                                        <template x-if="comentario.titulo">
                                            <div>
                                                <p class="font-bold text-slate-600">Título:</p>
                                                <p class="text-slate-800" x-text="comentario.titulo"></p>
                                            </div>
                                        </template>
                                        <template x-if="comentario.info">
                                            <div>
                                                <p class="font-bold text-slate-600">Contexto:</p>
                                                <p class="text-slate-800" x-text="comentario.info"></p>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                <!-- Texto Seleccionado -->
                                <template x-if="comentario.texto">
                                    <div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 p-3">
                                        <p class="text-[10px] font-bold uppercase tracking-widest text-blue-600 mb-2">Texto Detectado</p>
                                        <p class="text-sm text-blue-900 font-medium italic" x-text="comentario.texto"></p>
                                    </div>
                                </template>

                                <!-- Comentario -->
                                <div class="mb-4 p-3 bg-slate-50 rounded-lg border border-slate-200">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-600 mb-2">Observación</p>
                                    <p class="whitespace-pre-wrap text-sm text-slate-800 leading-6" x-text="comentario.comentario"></p>
                                </div>

                                <!-- Respuesta -->
                                <template x-if="comentario.respuesta">
                                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="w-1 h-4 bg-emerald-600 rounded"></div>
                                            <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-700">Respuesta del Docente</p>
                                        </div>
                                        <p class="whitespace-pre-wrap text-sm text-emerald-800 leading-6" x-text="comentario.respuesta"></p>
                                        <p class="mt-2 text-[11px] text-emerald-700 font-semibold" x-text="'— ' + comentario.respuesta_usuario"></p>
                                    </div>
                                </template>

                                <!-- Cambiar Estado -->
                                <div class="mb-4 rounded-lg border border-slate-200 bg-white p-3">
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-600 mb-3">Cambiar Estado</p>
                                    <div class="flex gap-2 flex-wrap">
                                        <button type="button" @click="actualizarEstadoComentario(comentario.id, 'pendiente')" class="rounded-lg border border-amber-300 bg-amber-100 px-3 py-1.5 text-[11px] font-bold text-amber-700 transition hover:bg-amber-200">
                                            Pendiente
                                        </button>
                                        <button type="button" @click="actualizarEstadoComentario(comentario.id, 'reabierto')" class="rounded-lg border border-red-300 bg-red-100 px-3 py-1.5 text-[11px] font-bold text-red-700 transition hover:bg-red-200">
                                            Reabierto
                                        </button>
                                        <button type="button" @click="actualizarEstadoComentario(comentario.id, 'resuelto')" class="rounded-lg border border-emerald-300 bg-emerald-100 px-3 py-1.5 text-[11px] font-bold text-emerald-700 transition hover:bg-emerald-200">
                                            Resuelto
                                        </button>
                                    </div>
                                </div>

                                <!-- Botón Ver Región -->
                                <template x-if="comentario.page">
                                    <button
                                        type="button"
                                        @click="jumpToComment(comentario.id, comentario.page); modalTodosComentariosAbierto = false;"
                                        class="inline-flex rounded-lg bg-blue-100 px-3.5 py-2.5 text-sm font-bold text-blue-700 transition hover:bg-blue-200">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 20h10a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                                        </svg>
                                        Ver en PDF
                                    </button>
                                </template>
                            </article>
                        </template>
                    </div>
                </template>
                <template x-if="comments.length === 0">
                    <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                        <p class="text-sm font-semibold text-slate-600">Sin observaciones aún</p>
                        <p class="text-xs text-slate-500 mt-2">No hay observaciones registradas en este documento.</p>
                    </div>
                </template>
            </div>

            <!-- Footer -->
            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4 flex-shrink-0">
                <button type="button" @click="modalTodosComentariosAbierto = false" class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 shadow-md hover:shadow-lg">
                    Cerrar Panel
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }

    .panel-surface {
        position: relative;
    }

    .editor-sidebar {
        scrollbar-width: thin;
        scrollbar-color: rgba(59, 130, 246, 0.4) rgba(226, 232, 240, 0.6);
    }

    .editor-sidebar::-webkit-scrollbar {
        width: 8px;
    }

    .editor-sidebar::-webkit-scrollbar-track {
        background: rgba(241, 245, 249, 0.5);
        border-radius: 999px;
    }

    .editor-sidebar::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, rgba(59, 130, 246, 0.5), rgba(29, 78, 216, 0.6));
        border-radius: 999px;
        border: 2px solid transparent;
        background-clip: padding-box;
    }

    .editor-sidebar::-webkit-scrollbar-thumb:hover {
        background-color: rgba(37, 99, 235, 0.7);
    }

    .pdf-page-surface {
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        cursor: crosshair;
        box-shadow: 0 30px 60px rgba(15, 23, 42, 0.35);
        transition: transform 0.25s ease, box-shadow 0.25s ease, outline-color 0.25s ease;
        outline: 1px solid rgba(148, 163, 184, 0.15);
        outline-offset: 0;
    }

    .pdf-page-active {
        transform: translateY(-2px);
    }

    .pdf-page-surface-active {
        box-shadow: 0 36px 70px rgba(14, 165, 233, 0.24);
        outline-color: rgba(56, 189, 248, 0.55);
    }

    .pdf-page-canvas {
        display: block;
        width: 100%;
        height: auto;
        background: white;
    }

    .pdf-selection-box {
        position: absolute;
        border: 3px solid rgba(56, 189, 248, 0.98);
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.42), rgba(14, 165, 233, 0.22));
        border-radius: 12px;
        pointer-events: auto;
        z-index: 25;
        cursor: grab;
        box-shadow:
            0 0 0 9999px rgba(2, 6, 23, 0.34),
            0 0 0 1px rgba(56, 189, 248, 0.4),
            0 0 36px rgba(56, 189, 248, 0.32),
            inset 0 0 0 1px rgba(186, 230, 253, 0.35),
            0 8px 32px rgba(56, 189, 248, 0.25);
        animation: selectionPulse 1.35s ease-in-out infinite;
        transition: all 0.18s ease-out;
    }

    .pdf-selection-box:hover {
        border-color: rgba(56, 189, 248, 1);
        box-shadow:
            0 0 0 9999px rgba(2, 6, 23, 0.42),
            0 0 0 1px rgba(56, 189, 248, 0.6),
            0 0 48px rgba(56, 189, 248, 0.4),
            inset 0 0 0 1px rgba(186, 230, 253, 0.45),
            0 12px 48px rgba(56, 189, 248, 0.35);
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.45), rgba(14, 165, 233, 0.25));
    }

    .pdf-selection-box::after {
        content: '';
        position: absolute;
        inset: 6px;
        border-radius: 8px;
        border: 1px dashed rgba(224, 242, 254, 0.72);
        pointer-events: none;
    }

    .pdf-selection-box.dragging {
        cursor: grabbing;
        box-shadow:
            0 0 0 9999px rgba(2, 6, 23, 0.55),
            0 0 0 2px rgba(56, 189, 248, 0.8),
            0 0 56px rgba(56, 189, 248, 0.5),
            inset 0 0 0 1px rgba(186, 230, 253, 0.6),
            0 20px 64px rgba(56, 189, 248, 0.4);
    }

    .pdf-selection-box::before {
        content: attr(data-label);
        position: absolute;
        top: -14px;
        left: 12px;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(8, 47, 73, 0.92);
        color: #e0f2fe;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        white-space: nowrap;
        box-shadow: 0 10px 24px rgba(2, 6, 23, 0.28);
    }

    .pdf-selection-handle {
        position: absolute;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(8, 47, 73, 0.95);
        background: #e0f2fe;
        border-radius: 999px;
        pointer-events: auto;
        box-shadow: 0 8px 16px rgba(2, 6, 23, 0.28), 0 0 0 1px rgba(56, 189, 248, 0.42);
    }

    .pdf-selection-handle-nw {
        top: -8px;
        left: -8px;
        cursor: nwse-resize;
    }

    .pdf-selection-handle-ne {
        top: -8px;
        right: -8px;
        cursor: nesw-resize;
    }

    .pdf-selection-handle-sw {
        bottom: -8px;
        left: -8px;
        cursor: nesw-resize;
    }

    .pdf-selection-handle-se {
        right: -8px;
        bottom: -8px;
        cursor: nwse-resize;
    }

    .pdf-comment-box {
        position: absolute;
        border: 2px solid rgba(251, 191, 36, 0.95);
        background: rgba(251, 191, 36, 0.16);
        border-radius: 12px;
        pointer-events: none;
        z-index: 18;
    }

    .pdf-comment-box.is-active {
        border-color: rgba(34, 197, 94, 1);
        background: rgba(34, 197, 94, 0.22);
        box-shadow: 0 0 0 1px rgba(34, 197, 94, 0.25), 0 0 26px rgba(34, 197, 94, 0.2);
    }

    .textLayer {
        position: absolute;
        inset: 0;
        overflow: hidden;
        opacity: 1;
        line-height: 1;
        z-index: 15;
        pointer-events: none;
        user-select: none;
    }

    .textLayer span,
    .textLayer br {
        position: absolute;
        color: transparent;
        white-space: pre;
        cursor: crosshair;
        transform-origin: 0 0;
        user-select: none;
    }

    .textLayer ::selection {
        background: rgba(56, 189, 248, 0.35);
    }

    @keyframes selectionPulse {

        0%,
        100% {
            box-shadow:
                0 0 0 9999px rgba(2, 6, 23, 0.28),
                0 0 0 1px rgba(56, 189, 248, 0.3),
                0 0 24px rgba(56, 189, 248, 0.2),
                inset 0 0 0 1px rgba(186, 230, 253, 0.28);
        }

        50% {
            box-shadow:
                0 0 0 9999px rgba(2, 6, 23, 0.38),
                0 0 0 1px rgba(56, 189, 248, 0.45),
                0 0 44px rgba(56, 189, 248, 0.36),
                inset 0 0 0 1px rgba(186, 230, 253, 0.42);
        }
    }

</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pdfWorkspace', (config) => ({
            pdfUrl: config.pdfUrl,
            rawFileUrl: config.rawFileUrl,
            comments: config.comments || [],
            secuenciaId: config.secuenciaId,
            selectorEnabled: true,
            loading: false,
            pdfError: '',
            totalPages: 0,
            interaction: null,
            activeCommentId: null,
            selectionGlow: false,
            selectionGlowTimer: null,
            globalListenersBound: false,
            isInitialized: false,
            renderSessionId: 0,
            drawActivationThreshold: 0.35,
            selectionMinSize: 1.2,
            pages: {},
            selection: {
                page: null,
                x: 0,
                y: 0,
                width: 0,
                height: 0,
                text: '',
            },
            draftTitle: '',
            draftInfo: '',
            draftComment: '',
            modalTodosComentariosAbierto: false,
            get selectionHint() {
                if (!this.selection.page) {
                    return 'Arrastra sobre cualquier parte del PDF para crear una ventana rectangular editable.';
                }

                return this.selection.text ?
                    `El rectangulo de la pagina ${this.selection.page} ya extrajo texto. Puedes moverlo y redimensionarlo desde las esquinas.` :
                    `El rectangulo de la pagina ${this.selection.page} esta listo. Puedes moverlo y redimensionarlo desde las esquinas.`;
            },
            get hasValidSelection() {
                return Boolean(
                    this.selection.page &&
                    this.selection.width >= this.selectionMinSize &&
                    this.selection.height >= this.selectionMinSize
                );
            },
            get workflowStatus() {
                if (!this.selectorEnabled) {
                    return 'Selector desactivado. Activalo para crear una zona.';
                }

                if (this.interaction?.type === 'draw') {
                    return 'Trazando seleccion en el documento...';
                }

                if (this.interaction?.type === 'resize') {
                    return 'Redimensionando seleccion actual...';
                }

                if (this.interaction?.type === 'drag') {
                    return 'Moviendo seleccion actual...';
                }

                if (this.selection.page) {
                    return `Zona lista en pagina ${this.selection.page}. Puedes guardar comentario o anotar PDF.`;
                }

                return 'Listo para seleccionar una zona en el documento.';
            },
            init() {
                if (this.isInitialized) {
                    return;
                }

                this.isInitialized = true;
                this.bindGlobalPointerEvents();
                this.renderPdf();
            },
            toggleSelector() {
                this.selectorEnabled = !this.selectorEnabled;
                this.interaction = null;
            },
            clearSelection() {
                this.selection = {
                    page: null,
                    x: 0,
                    y: 0,
                    width: 0,
                    height: 0,
                    text: '',
                };
                this.activeCommentId = null;
                this.selectionGlow = false;
                this.paintSelection();
            },
            useSelectedTextAsComment() {
                if (!this.selection.text) {
                    return;
                }

                this.draftComment = this.draftComment ?
                    `${this.draftComment}\n\nTexto seleccionado:\n${this.selection.text}`.trim() :
                    `Texto seleccionado:\n${this.selection.text}`;
            },
            bindGlobalPointerEvents() {
                if (this.globalListenersBound) {
                    return;
                }

                this.globalListenersBound = true;
                document.addEventListener('mouseleave', () => {
                    if (this.interaction) {
                        this.handleGlobalPointerUp();
                    }
                });
                window.addEventListener('mousemove', (event) => {
                    this.handleGlobalPointerMove(event);
                });

                window.addEventListener('mouseup', () => {
                    this.handleGlobalPointerUp();
                });
            },
            prefillSelectionFields(selection, options = {}) {
                const force = options.force === true;
                const areaLabel = `${selection.width.toFixed(2)}% x ${selection.height.toFixed(2)}%`;
                const coordLabel = `X ${selection.x.toFixed(2)}% · Y ${selection.y.toFixed(2)}%`;

                if (!this.draftTitle || force) {
                    this.draftTitle = `Revision pagina ${selection.page}`;
                }

                if (!this.draftInfo || force) {
                    this.draftInfo = `Pagina ${selection.page} · ${coordLabel}`;
                }

                if (!this.draftComment || force) {
                    this.draftComment = selection.text ?
                        `Revisar texto detectado en pagina ${selection.page}.` :
                        `Revisar esta zona en pagina ${selection.page}.`;
                }
            },
            triggerSelectionGlow() {
                this.selectionGlow = true;

                if (this.selectionGlowTimer) {
                    window.clearTimeout(this.selectionGlowTimer);
                }

                this.selectionGlowTimer = window.setTimeout(() => {
                    this.selectionGlow = false;
                }, 1800);
            },
            async renderPdf() {
                if (!this.pdfUrl) {
                    return;
                }

                const sessionId = ++this.renderSessionId;

                if (!window.pdfjsLib) {
                    this.pdfError = 'No fue posible cargar PDF.js en el navegador.';
                    return;
                }

                this.loading = true;
                this.pdfError = '';
                this.totalPages = 0;
                this.pages = {};
                this.$refs.viewerHost.innerHTML = '';

                try {
                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                    const pdf = await window.pdfjsLib.getDocument(this.pdfUrl).promise;

                    if (sessionId !== this.renderSessionId) {
                        return;
                    }

                    this.totalPages = pdf.numPages;

                    for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                        if (sessionId !== this.renderSessionId) {
                            return;
                        }

                        const page = await pdf.getPage(pageNumber);
                        const viewport = page.getViewport({
                            scale: 1.45
                        });
                        const wrapper = this.buildPageShell(pageNumber);
                        const surface = wrapper.querySelector('[data-role="surface"]');
                        const canvas = wrapper.querySelector('canvas');
                        const context = canvas.getContext('2d');

                        canvas.width = viewport.width;
                        canvas.height = viewport.height;

                        await page.render({
                            canvasContext: context,
                            viewport,
                        }).promise;

                        const textLayer = document.createElement('div');
                        textLayer.className = 'textLayer';
                        surface.appendChild(textLayer);

                        const textContent = await page.getTextContent();
                        const renderTextTask = window.pdfjsLib.renderTextLayer({
                            textContentSource: textContent,
                            container: textLayer,
                            viewport,
                            textDivs: [],
                        });

                        if (renderTextTask?.promise) {
                            await renderTextTask.promise;
                        }

                        if (sessionId !== this.renderSessionId) {
                            return;
                        }

                        this.pages[pageNumber] = {
                            wrapper,
                            surface,
                            textLayer,
                            width: viewport.width,
                            height: viewport.height,
                        };

                        this.bindSurfaceEvents(pageNumber, surface);
                        this.$refs.viewerHost.appendChild(wrapper);
                        this.paintCommentBoxes(pageNumber);
                    }

                    if (sessionId !== this.renderSessionId) {
                        return;
                    }

                    this.paintSelection();
                } catch (error) {
                    if (sessionId !== this.renderSessionId) {
                        return;
                    }

                    this.pdfError = error?.message || 'No fue posible renderizar el documento.';
                } finally {
                    if (sessionId === this.renderSessionId) {
                        this.loading = false;
                    }
                }
            },
            buildPageShell(pageNumber) {
                const article = document.createElement('article');
                article.className = 'mb-5 w-full';
                article.dataset.pageNumber = pageNumber;

                article.innerHTML = `
                    <div class="mb-2 flex items-center justify-between px-1 text-xs text-slate-400">
                        <span class="font-black uppercase tracking-[0.2em]">Pagina ${pageNumber}</span>
                        <span>Arrastra para crear, mover y ajustar</span>
                    </div>
                    <div class="pdf-page-surface bg-white" data-role="surface">
                        <canvas class="pdf-page-canvas"></canvas>
                    </div>
                `;

                return article;
            },
            bindSurfaceEvents(pageNumber, surface) {
                surface.addEventListener('mousedown', (event) => {
                    if (event.button !== 0) {
                        return;
                    }

                    if (!this.selectorEnabled) {
                        return;
                    }

                    if (event.target.closest('[data-role="selection-handle"]')) {
                        return;
                    }

                    if (event.target.closest('[data-role="selection-box"]')) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();

                    const point = this.resolvePercentPoint(surface, event);
                    this.interaction = {
                        type: 'draw',
                        page: pageNumber,
                        startPoint: point,
                        hasMoved: false,
                        previousSelection: {
                            ...this.selection
                        },
                    };
                });
            },
            startDragSelection(event) {
                if (!this.selection.page) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                const page = this.pages[this.selection.page];
                if (!page) {
                    return;
                }

                const point = this.resolvePercentPoint(page.surface, event);

                this.interaction = {
                    type: 'drag',
                    page: this.selection.page,
                    baseSelection: {
                        ...this.selection
                    },
                    startPoint: point,
                };
            },
            startResizeSelection(handle, event) {
                if (!this.selection.page) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                this.interaction = {
                    type: 'resize',
                    page: this.selection.page,
                    handle,
                    baseSelection: {
                        ...this.selection
                    },
                    previousSelection: {
                        ...this.selection
                    },
                };
            },
            handleGlobalPointerMove(event) {
                if (!(event.buttons & 1)) {
                    if (this.interaction) {
                        this.interaction = null;
                        this.paintSelection();
                    }
                    return;
                }

                if (!this.interaction) {
                    return;
                }

                const page = this.pages[this.interaction.page];

                if (!page) {
                    return;
                }

                const point = this.resolvePercentPoint(page.surface, event);

                if (this.interaction.type === 'draw') {
                    const deltaX = Math.abs(point.x - this.interaction.startPoint.x);
                    const deltaY = Math.abs(point.y - this.interaction.startPoint.y);

                    if (!this.interaction.hasMoved && deltaX < this.drawActivationThreshold && deltaY < this.drawActivationThreshold) {
                        return;
                    }

                    this.interaction.hasMoved = true;

                    const nextRect = this.normalizeSelectionBounds(
                        this.interaction.startPoint.x,
                        this.interaction.startPoint.y,
                        point.x,
                        point.y
                    );

                    this.applySelection({
                        page: this.interaction.page,
                        ...nextRect,
                        text: '',
                        skipPrefill: true,
                        skipGlow: true,
                        skipTextExtraction: true,
                    });

                    return;
                }

                if (this.interaction.type === 'drag') {
                    const deltaX = point.x - this.interaction.startPoint.x;
                    const deltaY = point.y - this.interaction.startPoint.y;

                    const base = this.interaction.baseSelection;
                    const nextRect = {
                        x: this.clampPercent(base.x + deltaX),
                        y: this.clampPercent(base.y + deltaY),
                        width: base.width,
                        height: base.height,
                    };

                    // Ensure selection stays within bounds
                    if (nextRect.x + nextRect.width > 100) {
                        nextRect.x = 100 - nextRect.width;
                    }
                    if (nextRect.y + nextRect.height > 100) {
                        nextRect.y = 100 - nextRect.height;
                    }

                    this.applySelection({
                        page: this.interaction.page,
                        ...nextRect,
                        text: this.selection.text,
                        skipPrefill: true,
                        skipGlow: true,
                        skipTextExtraction: true,
                    });

                    return;
                }

                if (this.interaction.type === 'resize') {
                    const nextRect = this.resizeSelection(this.interaction.handle, this.interaction.baseSelection, point);

                    this.applySelection({
                        page: this.interaction.page,
                        ...nextRect,
                        text: this.selection.text,
                        skipPrefill: true,
                        skipGlow: true,
                        skipTextExtraction: true,
                    });
                }
            },
            handleGlobalPointerUp() {
                if (!this.interaction) {
                    return;
                }

                const interaction = this.interaction;
                const pageNumber = interaction.page;
                this.interaction = null;

                if (interaction.type === 'draw') {
                    if (!interaction.hasMoved) {
                        this.selection = interaction.previousSelection?.page ? {
                            ...interaction.previousSelection
                        } : {
                            page: null,
                            x: 0,
                            y: 0,
                            width: 0,
                            height: 0,
                            text: '',
                        };
                        this.paintSelection();
                        return;
                    }

                    if (this.selection.width < this.selectionMinSize || this.selection.height < this.selectionMinSize) {
                        this.selection = interaction.previousSelection?.page ? {
                            ...interaction.previousSelection
                        } : {
                            page: null,
                            x: 0,
                            y: 0,
                            width: 0,
                            height: 0,
                            text: '',
                        };
                        this.paintSelection();
                        return;
                    }
                }

                if (this.selection.page === pageNumber) {
                    this.finalizeSelection();
                }
            },
            resolvePercentPoint(surface, event) {
                const bounds = surface.getBoundingClientRect();
                return {
                    x: this.clampPercent(((event.clientX - bounds.left) / bounds.width) * 100),
                    y: this.clampPercent(((event.clientY - bounds.top) / bounds.height) * 100),
                };
            },
            clampPercent(value, min = 0) {
                return Number(Math.max(min, Math.min(100, value)).toFixed(2));
            },
            normalizeSelectionBounds(left, top, right, bottom) {
                const normalizedLeft = this.clampPercent(Math.min(left, right));
                const normalizedTop = this.clampPercent(Math.min(top, bottom));
                const normalizedRight = this.clampPercent(Math.max(left, right));
                const normalizedBottom = this.clampPercent(Math.max(top, bottom));

                return {
                    x: normalizedLeft,
                    y: normalizedTop,
                    width: Math.max(0.5, Number((normalizedRight - normalizedLeft).toFixed(2))),
                    height: Math.max(0.5, Number((normalizedBottom - normalizedTop).toFixed(2))),
                };
            },
            resizeSelection(handle, baseSelection, point) {
                const left = baseSelection.x;
                const top = baseSelection.y;
                const right = baseSelection.x + baseSelection.width;
                const bottom = baseSelection.y + baseSelection.height;

                if (handle === 'nw') {
                    return this.normalizeSelectionBounds(point.x, point.y, right, bottom);
                }

                if (handle === 'ne') {
                    return this.normalizeSelectionBounds(left, point.y, point.x, bottom);
                }

                if (handle === 'sw') {
                    return this.normalizeSelectionBounds(point.x, top, right, point.y);
                }

                return this.normalizeSelectionBounds(left, top, point.x, point.y);
            },
            extractTextFromSelection(pageNumber, selection) {
                const page = this.pages[pageNumber];

                if (!page?.surface) {
                    return '';
                }

                const surfaceRect = page.surface.getBoundingClientRect();
                const selectionRect = {
                    left: surfaceRect.left + (selection.x / 100) * surfaceRect.width,
                    top: surfaceRect.top + (selection.y / 100) * surfaceRect.height,
                    right: surfaceRect.left + ((selection.x + selection.width) / 100) * surfaceRect.width,
                    bottom: surfaceRect.top + ((selection.y + selection.height) / 100) * surfaceRect.height,
                };

                const spans = Array.from(page.surface.querySelectorAll('.textLayer span'));
                const selectedParts = spans
                    .map((span) => {
                        const value = (span.textContent || '').replace(/\s+/g, ' ').trim();

                        if (!value) {
                            return null;
                        }

                        const rect = span.getBoundingClientRect();
                        const overlapWidth = Math.min(rect.right, selectionRect.right) - Math.max(rect.left, selectionRect.left);
                        const overlapHeight = Math.min(rect.bottom, selectionRect.bottom) - Math.max(rect.top, selectionRect.top);

                        if (overlapWidth <= 0 || overlapHeight <= 0) {
                            return null;
                        }

                        return value;
                    })
                    .filter(Boolean);

                return selectedParts.join(' ').replace(/\s+/g, ' ').trim();
            },
            finalizeSelection(options = {}) {
                if (!this.selection.page) {
                    return;
                }

                this.selection = {
                    ...this.selection,
                    text: this.extractTextFromSelection(this.selection.page, this.selection),
                };

                this.prefillSelectionFields(this.selection, options);
                this.triggerSelectionGlow();
                this.paintSelection();
            },
            updatePageFocusState() {
                Object.entries(this.pages).forEach(([pageNumber, page]) => {
                    const isActive = Number(pageNumber) === Number(this.selection.page);

                    page.wrapper.classList.toggle('pdf-page-active', isActive);
                    page.surface.classList.toggle('pdf-page-surface-active', isActive);
                });
            },
            applySelection(payload) {
                this.selection = {
                    page: payload.page,
                    x: payload.x,
                    y: payload.y,
                    width: payload.width,
                    height: payload.height,
                    text: payload.text || '',
                };
                this.activeCommentId = null;

                if (!payload.skipTextExtraction && this.selection.page) {
                    this.selection.text = this.extractTextFromSelection(this.selection.page, this.selection);
                }

                if (!payload.skipPrefill) {
                    this.prefillSelectionFields(this.selection, payload.options || {});
                }

                if (!payload.skipGlow) {
                    this.triggerSelectionGlow();
                }

                this.paintSelection();
            },
            paintSelection() {
                Object.values(this.pages).forEach((page) => {
                    page.surface.querySelectorAll('[data-role="selection-box"]').forEach((node) => node.remove());
                    page.surface.querySelectorAll('[data-role="comment-box"]').forEach((node) => node.remove());
                });

                Object.keys(this.pages).forEach((pageNumber) => this.paintCommentBoxes(Number(pageNumber)));
                this.updatePageFocusState();

                if (!this.selection.page || !this.pages[this.selection.page]) {
                    return;
                }

                const surface = this.pages[this.selection.page].surface;
                const box = document.createElement('div');
                box.className = 'pdf-selection-box';
                box.dataset.role = 'selection-box';
                box.dataset.label = `P${this.selection.page} · ${this.selection.width.toFixed(1)}% x ${this.selection.height.toFixed(1)}%`;
                box.style.left = `${this.selection.x}%`;
                box.style.top = `${this.selection.y}%`;
                box.style.width = `${Math.max(this.selection.width, 0.5)}%`;
                box.style.height = `${Math.max(this.selection.height, 0.5)}%`;

                // Add dragging class if currently dragging
                if (this.interaction?.type === 'drag') {
                    box.classList.add('dragging');
                }

                // Add drag functionality to the main selection box
                box.addEventListener('mousedown', (event) => {
                    // Only start drag if clicking on the box itself, not on handles
                    if (event.target.dataset.role === 'selection-handle') {
                        return;
                    }
                    this.startDragSelection(event);
                });

                ['nw', 'ne', 'sw', 'se'].forEach((handle) => {
                    const handleNode = document.createElement('button');
                    handleNode.type = 'button';
                    handleNode.className = `pdf-selection-handle pdf-selection-handle-${handle}`;
                    handleNode.dataset.role = 'selection-handle';
                    handleNode.dataset.handle = handle;
                    handleNode.addEventListener('mousedown', (event) => this.startResizeSelection(handle, event));
                    box.appendChild(handleNode);
                });

                surface.appendChild(box);
            },
            paintCommentBoxes(pageNumber) {
                const page = this.pages[pageNumber];

                if (!page) {
                    return;
                }

                const pageComments = this.comments.filter((comment) => Number(comment.page) === pageNumber);

                pageComments.forEach((comment) => {
                    const box = document.createElement('div');
                    box.className = `pdf-comment-box${this.activeCommentId === comment.id ? ' is-active' : ''}`;
                    box.dataset.role = 'comment-box';
                    box.style.left = `${comment.x}%`;
                    box.style.top = `${comment.y}%`;
                    box.style.width = `${Math.max(comment.width, 0.5)}%`;
                    box.style.height = `${Math.max(comment.height, 0.5)}%`;
                    page.surface.appendChild(box);
                });
            },
            actualizarEstadoComentario(comentarioId, nuevoEstatus) {
                const url = `/secuencias/${this.secuenciaId}/comentarios/${comentarioId}/estado`;
                
                fetch(url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({
                        estatus: nuevoEstatus,
                    }),
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Actualizar el estado del comentario en el array
                    const comentario = this.comments.find(c => c.id === comentarioId);
                    if (comentario) {
                        comentario.estatus = nuevoEstatus;
                    }
                })
                .catch(error => {
                    console.error('Error actualizando estado:', error);
                    alert('Error al actualizar el estado: ' + error.message);
                });
            },
            jumpToComment(commentId, pageNumber) {
                this.activeCommentId = commentId;
                const comment = this.comments.find((item) => item.id === commentId);

                if (comment) {
                    const hasValidRect =
                        Number(comment.page) > 0 &&
                        Number(comment.x) >= 0 &&
                        Number(comment.y) >= 0 &&
                        Number(comment.width) > 0 &&
                        Number(comment.height) > 0;

                    if (!hasValidRect) {
                        const page = this.pages[pageNumber];

                        if (page) {
                            page.wrapper.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center',
                            });
                        }

                        this.paintSelection();
                        return;
                    }

                    this.selection = {
                        page: comment.page,
                        x: Number(comment.x),
                        y: Number(comment.y),
                        width: Number(comment.width),
                        height: Number(comment.height),
                        text: comment.texto_seleccionado || '',
                    };

                    this.finalizeSelection({
                        force: true
                    });
                }

                const page = this.pages[pageNumber];

                if (page) {
                    page.wrapper.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                    });
                }
            },
        }));
    });
</script>
@endsection