@extends('layouts.principal')

@section('content')



@php

$unidadesFormateadas = [];

if(isset($unidades)){

foreach($unidades as $u){

$unidadesFormateadas[] = [
'id' => $u->id,
'numero' => $u->numero,
'titulo' => $u->nombre,
'horas' => $u->horas,
'objetivo' => '',
'actividades' => ''

];

}

}

@endphp


{{--
    ***********************************************************************************************************
    VISTA DE CREACIÓN DE SECUENCIAS: Formulario Multi-Paso (Stepper) con Modal de Subida de Documentos
    Colores principales: #0C4B54 (Azul Primario), #F59E0B (Naranja Acento para progreso)
    ***********************************************************************************************************
--}}

<div x-data="formSteps()">

    {{-- ENCABEZADO Y BOTÓN DE RETORNO --}}
    <div class="mb-8 flex flex-col md:flex-row items-start md:items-center justify-between border-b border-[#0C4B54]/10 pb-4">
        <div class="flex items-center gap-3">
            <div class="p-3 bg-[#0C4B54] rounded-xl shadow-lg">
                <i class="fas fa-file-alt text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-4xl font-extrabold text-[#0C4B54]">Creación de Secuencia Didáctica</h1>
                <p class="text-gray-600 mt-1" x-text="'Paso ' + currentStep + ': ' + steps.find(s => s.id === currentStep).title"></p>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-3 mt-4 md:mt-0">
            {{-- BOTÓN NUEVO: Subir Documento --}}
            @if(isset($secuencia))
            <a
                href="{{ route('secuencias.exportWord', $secuencia->id) }}"
                class="bg-blue-600 text-white px-6 py-3 rounded-xl shadow-lg hover:bg-blue-700 transition-all duration-300 flex items-center gap-2 font-semibold text-base">
                <i class="fas fa-file-word"></i> Exportar Word
            </a>
            @else
            <button
                @click="showUploadModal = true"
                type="button"
                class="bg-[#F59E0B] text-white px-6 py-3 rounded-xl shadow-lg hover:bg-[#e0900a] transition-all duration-300 flex items-center gap-2 font-semibold text-base">
                <i class="fas fa-cloud-upload-alt"></i> Subir Documento
            </button>
            @endif

            <a href="{{ route('secuencias.index') }}" class="bg-gray-400 text-white px-6 py-3 rounded-xl shadow-lg hover:bg-gray-500 transition-all duration-300 flex items-center gap-2 font-semibold text-base">
                <i class="fas fa-arrow-left"></i> Volver a Secuencias
            </a>
        </div>
    </div>

    {{-- BARRA DE NAVEGACIÓN (BOTONES DE PASOS) --}}
    <div class="space-y-6">


        {{-- ********************************************************************************************** --}}
        {{-- MODAL PARA SUBIR DOCUMENTO --}}
        {{-- ********************************************************************************************** --}}
        @unless(isset($secuencia))
        <div
            x-show="showUploadModal"
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-75 backdrop-blur-sm"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click.away="showUploadModal = false"
            @keydown.escape.window="showUploadModal = false">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

                {{-- Contenedor del Modal --}}
                <div
                    x-show="showUploadModal"
                    class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-[#0C4B54]/10 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-file-upload text-[#0C4B54] text-xl"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-2xl leading-6 font-extrabold text-gray-900" id="modal-title">
                                    Subir Documento de Secuencia
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Aquí puedes subir un archivo de secuencia didáctica existente (ej. PDF, DOCX) para auto-llenar el formulario.
                                    </p>
                                </div>
                            </div>
                        </div>
                        {{-- Formulario de Subida --}}

                        <form
                            id="upload-form"
                            action="{{ route('secuencias.upload') }}"
                            method="POST"
                            enctype="multipart/form-data"
                            class="mt-5 space-y-4">
                            @csrf

                            <div>

                                <label class="block text-sm font-medium text-gray-700">
                                    Seleccionar Archivo
                                </label>

                                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl">

                                    <div class="text-center">

                                        <label
                                            for="file-upload"
                                            class="cursor-pointer font-medium text-[#F59E0B]">

                                            Sube un archivo

                                            <input
                                                id="file-upload"
                                                name="caratula_file"
                                                type="file"
                                                class="sr-only"
                                                accept=".pdf"
                                                required>

                                        </label>

                                        <p class="text-xs text-gray-500">
                                            PDF hasta 10MB
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </form>

                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" form="upload-form" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-3 bg-[#0C4B54] text-base font-medium text-white hover:bg-[#093D45] sm:ml-3 sm:w-auto sm:text-sm transition">
                            Procesar y Llenar
                        </button>
                        <button @click="showUploadModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm transition">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endunless
        {{-- FIN DEL MODAL --}}
        {{-- ********************************************************************************************** --}}

        {{-- ========================= --}}
        {{-- BARRA SUPERIOR DINÁMICA --}}
        {{-- ========================= --}}

        <div class="sticky top-0 z-10 bg-white p-4 rounded-xl shadow-lg border border-gray-200">

            <div class="flex justify-between space-x-2 overflow-x-auto">

                <template x-for="step in steps" :key="step.id">

                    <button

                        @click="goToStep(step.id)"

                        :class="{

'bg-[#0C4B54] text-white shadow-xl transform scale-105':

currentStep === step.id,

'bg-gray-100 text-gray-700 hover:bg-gray-200':

currentStep !== step.id

}"

                        class="flex-1 px-4 py-3 rounded-xl font-semibold transition whitespace-nowrap text-sm flex items-center justify-center gap-2">

                        <i :class="step.icon"></i>

                        <span x-text="step.title"></span>

                    </button>

                </template>

            </div>

        </div>

        {{-- ========================= --}}
        {{-- FORMULARIO --}}
        {{-- ========================= --}}



        @if(isset($secuencia))

        <form action="{{ route('secuencias.update',$secuencia->id) }}" method="POST">
            @method('PUT')

            @else

            <form action="{{ route('secuencias.store') }}" method="POST">

                @endif

                @csrf

                <div class="bg-white rounded-2xl shadow-2xl p-6 md:p-10 border border-gray-100 relative">

                    {{-- CABECERA --}}

                    <div

                        class="bg-green-600 text-white font-extrabold text-2xl p-4 rounded-t-xl absolute top-0 left-0 w-full uppercase"

                        x-text="steps.find(s => s.id === currentStep).title"></div>

                    <div class="pt-16">

                        {{-- ====================================================== --}}
                        {{-- PASO 1 — CARÁTULA --}}
                        {{-- ====================================================== --}}

                        <div x-show="currentStep === 1">

                            <h3 class="text-xl font-bold mb-6">

                                Identificación de la Asignatura

                            </h3>


                            {{-- Carrera --}}

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center border-b pb-4">

                                <label class="block text-lg font-bold text-gray-800">

                                    Carrera:

                                </label>

                                <div class="col-span-2">

                                    <input

                                        type="text"

                                        name="carrera"

                                        value="{{ $caratula->carrera ?? '' }}"

                                        class="w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm">

                                </div>

                            </div>

                            {{-- Asignatura --}}

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center border-b pb-4 mt-4">

                                <label class="block text-lg font-bold text-gray-800">

                                    Asignatura:

                                </label>

                                <div class="col-span-2">

                                    <input

                                        type="text"

                                        name="asignatura"

                                        value="{{ $caratula->asignatura ?? '' }}"

                                        class="w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm">

                                </div>

                            </div>

                            {{-- Competencia --}}

                            <div class="grid grid-cols-3 gap-6 mt-4">

                                <label class="font-bold">

                                    Competencia:

                                </label>

                                <div class="col-span-2">

                                    <textarea

                                        name="competencia"

                                        rows="4"

                                        class="w-full rounded-xl">{{ $caratula->competencia ?? '' }}</textarea>

                                </div>

                            </div>

                            {{-- Cuatrimestre (VARCHAR) --}}

                            <div class="grid grid-cols-3 gap-6 mt-4">

                                <label class="font-bold">

                                    Cuatrimestre:

                                </label>

                                <div class="col-span-2">

                                    <input

                                        type="text"

                                        name="cuatrimestre"

                                        value="{{ $caratula->cuatrimestre ?? '' }}"

                                        class="w-full rounded-xl">

                                </div>

                            </div>

                        </div>

                        {{-- ====================================================== --}}
                        {{-- PASOS DINÁMICOS — UNIDADES --}}
                        {{-- ====================================================== --}}

                        <template x-for="(unidad,index) in unidades" :key="index">

                            <div

                                x-show="currentStep === index + 2">

                                <h3 class="text-xl font-bold mb-6">

                                    Unidad

                                    <span x-text="unidad.numero"></span>

                                </h3>

                                <div class="space-y-4">

                                    {{-- TÍTULO --}}

                                    <div>

                                        <label class="font-semibold">

                                            Título de la Unidad

                                        </label>

                                        <input

                                            type="text"

                                            :name="'unidades['+index+'][titulo]'"

                                            x-model="unidad.titulo"

                                            class="w-full px-4 py-3 rounded-xl border">
                                        <input
                                            type="hidden"
                                            :name="'unidades['+index+'][id]'"
                                            :value="unidad.id">

                                    </div>

                                    {{-- OBJETIVO --}}

                                    <div>

                                        <label class="font-semibold">

                                            Objetivo de la Unidad

                                        </label>

                                        <textarea

                                            :name="'unidades['+index+'][objetivo]'"

                                            rows="3"

                                            x-model="unidad.objetivo"

                                            class="w-full px-4 py-3 rounded-xl border"></textarea>

                                    </div>

                                    {{-- DURACIÓN --}}

                                    <div>

                                        <label class="font-semibold">

                                            Duración (Horas)

                                        </label>

                                        <input

                                            type="number"

                                            :name="'unidades['+index+'][duracion]'"

                                            x-model="unidad.horas"

                                            class="w-1/4 px-4 py-3 rounded-xl border">

                                    </div>

                                    {{-- ACTIVIDADES --}}

                                    <div class="p-4 bg-gray-50 rounded-xl">

                                        <label class="font-bold">

                                            Actividades

                                        </label>

                                        <textarea

                                            :name="'unidades['+index+'][actividades]'"

                                            rows="4"

                                            x-model="unidad.actividades"

                                            class="w-full px-4 py-3 rounded-xl border"></textarea>

                                    </div>

                                </div>

                            </div>

                        </template>

                        {{-- ====================================================== --}}
                        {{-- PASO FINAL --}}
                        {{-- ====================================================== --}}

                        <div x-show="currentStep === maxStep">

                            <h3 class="text-2xl font-bold text-green-600">

                                Listo para guardar

                            </h3>

                        </div>

                    </div>

                </div>

                {{-- ========================= --}}
                {{-- NAVEGACIÓN INFERIOR --}}
                {{-- ========================= --}}

                <div class="flex justify-between mt-6">

                    <button

                        type="button"

                        @click="prevStep()"

                        class="px-6 py-3 bg-gray-200 rounded-xl">

                        Anterior

                    </button>

                    <div class="flex gap-4">

                        <button

                            type="button"

                            @click="nextStep()"

                            class="px-8 py-3 bg-[#F59E0B] text-white rounded-xl">

                            Siguiente

                        </button>

                        <button

                            type="submit"

                            class="px-8 py-3 bg-[#0C4B54] text-white rounded-xl">

                            Guardar Secuencia

                        </button>

                    </div>

                </div>

            </form>

    </div>

    {{-- PASO 5: FINALIZAR (Resumen y Envío) --}}
    <div x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 transform translate-x-full" x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-full">
        <h3 class="text-2xl font-bold text-[#10B981] mb-6 flex items-center gap-2"><i class="fas fa-rocket"></i> Listo para Enviar</h3>
        <div class="p-6 bg-[#10B981]/10 border-l-4 border-[#10B981] rounded-lg">
            <p class="text-gray-700 font-semibold">Ha completado la sección de Identificación y los esquemas de las tres unidades.</p>
            <p class="text-sm text-gray-600 mt-2">Presione **"Guardar Secuencia"** para almacenar toda la información.</p>
        </div>

        <div class="mt-8 p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
            <h4 class="font-bold text-yellow-700 flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> Revisión Final</h4>
            <ul class="list-disc list-inside text-sm text-yellow-800 mt-2">
                <li>Verifique que todos los campos obligatorios del Paso 1 estén llenos.</li>
                <li>Asegúrese de que el contenido de las unidades sea correcto.</li>
            </ul>
        </div>
    </div>

</div>
</div>

{{-- BARRA DE NAVEGACIÓN INFERIOR --}}
<div class="flex justify-between items-center pt-4">
    {{-- Botón Anterior --}}
    <button
        type="button"
        @click="prevStep()"
        x-show="currentStep > 1 && currentStep < maxStep"
        class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold transition shadow-md flex items-center gap-2">
        <i class="fas fa-chevron-left"></i> Anterior
    </button>
    <div x-show="currentStep === 1"></div> {{-- Placeholder para centrar si es necesario --}}

    {{-- Botones de Acción Final --}}
    <div class="flex gap-4">
        <button
            type="button"
            @click="nextStep()"
            x-show="currentStep < maxStep"
            class="px-8 py-3 rounded-xl bg-[#F59E0B] hover:bg-[#e0900a] text-white font-bold transition shadow-lg flex items-center gap-2">
            Siguiente <i class="fas fa-chevron-right"></i>
        </button>

        <button
            type="submit"
            x-show="currentStep === maxStep"
            class="px-8 py-3 rounded-xl bg-[#0C4B54] hover:bg-[#093D45] text-white font-bold transition shadow-lg">
            <i class="fas fa-save"></i> Guardar Secuencia
        </button>
    </div>
</div>

</form>


</div>
<script>
    function formSteps() {

        return {

            currentStep: 1,

            showUploadModal: false,

            unidades: @json($unidadesFormateadas ?? []),

            get steps() {

                let lista = [

                    {
                        id: 1,
                        title: 'Carátula',
                        icon: 'fas fa-id-card'
                    }

                ];

                this.unidades.forEach((u, i) => {

                    lista.push({

                        id: i + 2,
                        title: 'Unidad ' + u.numero,
                        icon: 'fas fa-bookmark'

                    });

                });

                lista.push({

                    id: this.unidades.length + 2,
                    title: 'Finalizar',
                    icon: 'fas fa-check-circle'

                });

                return lista;

            },

            get maxStep() {

                return this.unidades.length + 2;

            },

            goToStep(step) {

                this.currentStep = step;

            },

            nextStep() {

                if (this.currentStep < this.maxStep)
                    this.currentStep++;

            },

            prevStep() {

                if (this.currentStep > 1)
                    this.currentStep--;

            }

        }

    }
</script>

@endsection