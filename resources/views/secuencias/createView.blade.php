@extends('layouts.principal')

@section('content')

{{--
    ***********************************************************************************************************
    VISTA DE CREACIÓN DE SECUENCIAS: Formulario Multi-Paso (Stepper) con Modal de Subida de Documentos
    Colores principales: #0C4B54 (Azul Primario), #F59E0B (Naranja Acento para progreso)
    ***********************************************************************************************************
--}}

<div 
    x-data="{ 
        // 1. CONTROL DE PASOS
        currentStep: 1,
        maxStep: 5,
        steps: [
            { id: 1, title: 'Carátula (Identificación)', icon: 'fas fa-id-card' },
            { id: 2, title: 'Unidad 1', icon: 'fas fa-bookmark' },
            { id: 3, title: 'Unidad 2', icon: 'fas fa-bookmark' },
            { id: 4, title: 'Unidad 3', icon: 'fas fa-bookmark' },
            { id: 5, title: 'Finalizar', icon: 'fas fa-check-circle' }
        ],
        goToStep(step) {
            this.currentStep = step;
        },
        nextStep() {
            if (this.currentStep < this.maxStep) {
                this.currentStep++;
            }
        },
        prevStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },
        
        // **********************************************
        // NUEVO: ESTADO DEL MODAL
        showUploadModal: false,
        // **********************************************

        // 2. DATOS SIMULADOS (deberían venir de Laravel)
        carrerasDisponibles: [
            { id: 1, nombre: 'Ingeniería en Desarrollo y Gestión de Software' },
            { id: 2, nombre: 'Ingeniería en Tecnologías de la Información' },
            { id: 3, nombre: 'Ingeniería en Alimentos' }
        ],
        materiasDisponibles: [
            { id: 101, nombre: 'Arquitectura de Software' },
            { id: 102, nombre: 'Desarrollo Web Avanzado' },
            { id: 103, nombre: 'Matemáticas para Ingeniería' }
        ],
        docentesDisponibles: [
            { id: 201, nombre: 'Gloria Patricia Sánchez Sánchez', email: 'gloria.sanchez@uth.edu.mx' },
            { id: 202, nombre: 'Mtra. Antonia Alameda Bermeo', email: 'antonia.b@uth.edu.mx' },
            { id: 203, nombre: 'Dr. Juan Pérez López', email: 'juan.perez@uth.edu.mx' }
        ],

        // 3. ESTADO DEL FORMULARIO
        selectedDocente: null,
        docenteEmail: '',
        selectedTutor: null,
        tutorEmail: '',
        
        // 4. FUNCIONALIDAD PARA POBLAR EMAILS
        setDocenteEmail(docenteId) {
            const docente = this.docentesDisponibles.find(d => String(d.id) === String(docenteId));
            this.docenteEmail = docente ? docente.email : '';
        },
        setTutorEmail(tutorId) {
            const tutor = this.docentesDisponibles.find(d => String(d.id) === String(tutorId));
            this.tutorEmail = tutor ? tutor.nombre : '';
        }
    }" 
    class="w-full p-4 md:p-8 space-y-8 min-h-screen bg-gray-50"
>

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
            <button
                @click="showUploadModal = true"
                type="button"
                class="bg-[#F59E0B] text-white px-6 py-3 rounded-xl shadow-lg hover:bg-[#e0900a] transition-all duration-300 flex items-center gap-2 font-semibold text-base"
            >
                <i class="fas fa-cloud-upload-alt"></i> Subir Documento
            </button>
            
            <a href="{{ url()->previous() }}" class="bg-gray-400 text-white px-6 py-3 rounded-xl shadow-lg hover:bg-gray-500 transition-all duration-300 flex items-center gap-2 font-semibold text-base">
                <i class="fas fa-arrow-left"></i> Volver a Secuencias
            </a>
        </div>
    </div>

    {{-- BARRA DE NAVEGACIÓN (BOTONES DE PASOS) --}}
    <div class="sticky top-0 z-10 bg-white p-4 rounded-xl shadow-lg border border-gray-200">
        <div class="flex justify-between space-x-2 overflow-x-auto">
            <template x-for="step in steps" :key="step.id">
                <button
                    @click="goToStep(step.id)"
                    :class="{ 
                        'bg-[#0C4B54] text-white shadow-xl transform scale-105': currentStep === step.id,
                        'bg-gray-100 text-gray-700 hover:bg-gray-200': currentStep !== step.id
                    }"
                    class="flex-1 px-4 py-3 rounded-xl font-semibold transition-all duration-300 ease-in-out whitespace-nowrap text-sm flex items-center justify-center gap-2"
                >
                    <i :class="step.icon"></i>
                    <span x-text="step.title"></span>
                </button>
            </template>
        </div>
    </div>
    
    <form action="{{ route('secuencias.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- CONTENEDOR DE PASOS (Carrousel) --}}
        <div class="bg-white rounded-2xl shadow-2xl p-6 md:p-10 border border-gray-100 overflow-hidden relative">
            
            {{-- Indicador de cabecera --}}
            <div class="bg-green-600 text-white font-extrabold text-2xl p-4 rounded-t-xl absolute top-0 left-0 w-full mb-6 shadow-md uppercase tracking-wider" x-text="steps.find(s => s.id === currentStep).title">
            </div>
            <div class="pt-16">
            
                {{-- PASO 1: CARÁTULA / IDENTIFICACIÓN (Se mantiene el contenido anterior) --}}
                <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 transform -translate-x-full" x-transition:enter-end="opacity-100 transform translate-x-0"
                     x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform translate-x-full"
                >
                    <h3 class="text-xl font-bold text-gray-700 mb-6">A.- Identificación de la Asignatura</h3>
                    
                    {{-- 1. Carrera --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center border-b pb-4">
                        <label for="carrera" class="block text-lg font-bold text-gray-800">Carrera:</label>
                        <div class="col-span-2">
                            <select id="carrera" name="carrera_id" required class="w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm focus:ring-[#0C4B54] focus:border-[#0C4B54] transition bg-white">
                                <option value="" disabled selected>Seleccione la Carrera</option>
                                <template x-for="carrera in carrerasDisponibles" :key="carrera.id">
                                    <option :value="carrera.id" x-text="carrera.nombre"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- 2. Asignatura --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center border-b pb-4 mt-4">
                        <label for="asignatura" class="block text-lg font-bold text-gray-800">Asignatura:</label>
                        <div class="col-span-2">
                            <select id="asignatura" name="materia_id" required class="w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm focus:ring-[#0C4B54] focus:border-[#0C4B54] transition bg-white">
                                <option value="" disabled selected>Seleccione la Asignatura</option>
                                <template x-for="materia in materiasDisponibles" :key="materia.id">
                                    <option :value="materia.id" x-text="materia.nombre"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- 3. Competencia --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start border-b pb-4 mt-4">
                        <label for="competencia" class="block text-lg font-bold text-gray-800 mt-2">Competencia(s):</label>
                        <div class="col-span-2">
                            <textarea 
                                id="competencia" 
                                name="competencia" 
                                rows="4" 
                                required 
                                placeholder="Escriba la competencia principal de la asignatura..."
                                class="w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm focus:ring-[#0C4B54] focus:border-[#0C4B54] transition resize-y"
                            >Construir soluciones de software y sistemas inteligentes mediante la gestión de proyectos...</textarea>
                        </div>
                    </div>

                    {{-- 4. Periodo y Agrupación (Año, Cuatrimestre, Grupo) --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center border-b pb-4 mt-4">
                        <label class="block text-lg font-bold text-gray-800">Período:</label>
                        <div class="col-span-2 grid grid-cols-2 sm:grid-cols-4 gap-4">
                            
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1">Año</label><input type="number" name="anio" value="{{ date('Y') }}" required class="w-full px-3 py-2 rounded-xl border-gray-300 shadow-sm focus:ring-[#F59E0B] focus:border-[#F59E0B] transition"></div>
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1">Cuatrimestre</label><select name="cuatrimestre" required class="w-full px-3 py-2 rounded-xl border-gray-300 shadow-sm focus:ring-[#F59E0B] focus:border-[#F59E0B] transition bg-white"><option value="7" selected>7°</option></select></div>
                            <div><label class="block text-xs font-semibold text-gray-500 mb-1">Grupo</label><input type="text" name="grupo" value="A" required class="w-full px-3 py-2 rounded-xl border-gray-300 shadow-sm focus:ring-[#F59E0B] focus:border-[#F59E0B] transition uppercase"></div>
                            <div class="sm:col-span-1"><label class="block text-xs font-semibold text-gray-500 mb-1">Periodo Escolar</label><select name="periodo_id" required class="w-full px-3 py-2 rounded-xl border-gray-300 shadow-sm focus:ring-[#F59E0B] focus:border-[#F59E0B] transition bg-white"><option value="2" selected>Mayo-Agosto</option></select></div>
                        </div>
                    </div>

                    {{-- 5. Docente y e-mail --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center border-b pb-4 mt-4">
                        <label for="docente" class="block text-lg font-bold text-gray-800">Docente:</label>
                        <div class="col-span-2 grid grid-cols-2 gap-4">
                            <div>
                                <select 
                                    id="docente" 
                                    name="docente_id" 
                                    x-model="selectedDocente"
                                    @change="setDocenteEmail($event.target.value)"
                                    required 
                                    class="w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm focus:ring-[#0C4B54] focus:border-[#0C4B54] transition bg-white"
                                >
                                    <option value="" disabled selected>Seleccione el Docente</option>
                                    <template x-for="docente in docentesDisponibles" :key="docente.id">
                                        <option :value="docente.id" x-text="docente.nombre"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <input type="email" name="docente_email" x-model="docenteEmail" readonly placeholder="e-mail del docente" class="w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm bg-gray-100 text-gray-600 transition">
                            </div>
                        </div>
                    </div>

                    {{-- 6. Link y Tutor --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center mt-4">
                        <label for="link_internet" class="block text-lg font-bold text-gray-800">Link Aula Virtual:</label>
                        <div class="col-span-2">
                            <input type="url" name="link_internet" placeholder="Ej: https://classroom.google.com/..." class="w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm focus:ring-[#F59E0B] focus:border-[#F59E0B] transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center mt-4">
                        <label for="tutor" class="block text-lg font-bold text-gray-800">Tutor(a) de grupo:</label>
                        <div class="col-span-2">
                            <select id="tutor" name="tutor_id" x-model="selectedTutor" @change="setTutorEmail($event.target.value)" class="w-full px-4 py-3 rounded-xl border-gray-300 shadow-sm focus:ring-[#0C4B54] focus:border-[#0C4B54] transition bg-white">
                                <option value="" disabled selected>Seleccione el Tutor (Opcional)</option>
                                <template x-for="tutor in docentesDisponibles" :key="tutor.id">
                                    <option :value="tutor.id" x-text="tutor.nombre"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-center mt-4">
                        <label for="archivo_secuencia" class="block text-lg font-bold text-gray-800">Archivo de la secuencia:</label>
                        <div class="col-span-2">
                            <input
                                id="archivo_secuencia"
                                name="archivo_secuencia"
                                type="file"
                                accept=".pdf,.doc,.docx,.png,.jpg,.jpeg"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm shadow-sm focus:border-[#0C4B54] focus:outline-none focus:ring-[#0C4B54]"
                            >
                            <p class="mt-1 text-xs text-gray-500">Opcional. Permitidos: PDF, DOC, DOCX, PNG, JPG, JPEG (max 20MB).</p>
                        </div>
                    </div>

                </div>
                
                {{-- PASO 2: UNIDAD 1 --}}
                <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 transform translate-x-full" x-transition:enter-end="opacity-100 transform translate-x-0"
                     x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-full"
                >
                    <h3 class="text-xl font-bold text-gray-700 mb-6">B.- Desarrollo de la Unidad 1</h3>
                    
                    {{-- Contenido de la Unidad 1 --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Título de la Unidad</label>
                            <input type="text" name="unidad1_titulo" placeholder="Ej: Introducción a la Arquitectura de Software" class="w-full px-4 py-3 rounded-xl border-gray-300 focus:ring-[#F59E0B] transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Objetivo de la Unidad</label>
                            <textarea name="unidad1_objetivo" rows="3" placeholder="Describa el objetivo de la unidad..." class="w-full px-4 py-3 rounded-xl border-gray-300 focus:ring-[#F59E0B] transition resize-y"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Duración (Horas)</label>
                            <input type="number" name="unidad1_duracion" placeholder="Ej: 15" class="w-1/4 px-4 py-3 rounded-xl border-gray-300 focus:ring-[#F59E0B] transition">
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                             <label class="block text-sm font-bold text-gray-700 mb-2">Actividades de Aprendizaje</label>
                             <textarea name="unidad1_actividades" rows="4" placeholder="Lista de actividades (ej: Mapas mentales, Debate, Proyecto)..." class="w-full px-4 py-3 rounded-xl border-gray-300 focus:ring-[#0C4B54] transition resize-y"></textarea>
                        </div>
                    </div>

                </div>
                
                {{-- PASO 3: UNIDAD 2 (Simulado) --}}
                <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 transform translate-x-full" x-transition:enter-end="opacity-100 transform translate-x-0"
                     x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-full"
                >
                    <h3 class="text-xl font-bold text-gray-700 mb-6">C.- Desarrollo de la Unidad 2</h3>
                     <p class="text-gray-500 italic">Contenido similar al de la Unidad 1, esperando ser llenado.</p>
                </div>
                
                {{-- PASO 4: UNIDAD 3 (Simulado) --}}
                <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 transform translate-x-full" x-transition:enter-end="opacity-100 transform translate-x-0"
                     x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-full"
                >
                    <h3 class="text-xl font-bold text-gray-700 mb-6">D.- Desarrollo de la Unidad 3</h3>
                    <p class="text-gray-500 italic">Contenido similar al de la Unidad 1, esperando ser llenado.</p>
                </div>
                
                {{-- PASO 5: FINALIZAR (Resumen y Envío) --}}
                <div x-show="currentStep === 5" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 transform translate-x-full" x-transition:enter-end="opacity-100 transform translate-x-0"
                     x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-x-0" x-transition:leave-end="opacity-0 transform -translate-x-full"
                >
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
                class="px-6 py-3 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold transition shadow-md flex items-center gap-2"
            >
                <i class="fas fa-chevron-left"></i> Anterior
            </button>
            <div x-show="currentStep === 1"></div> {{-- Placeholder para centrar si es necesario --}}

            {{-- Botones de Acción Final --}}
            <div class="flex gap-4">
                <button 
                    type="button"
                    @click="nextStep()"
                    x-show="currentStep < maxStep"
                    class="px-8 py-3 rounded-xl bg-[#F59E0B] hover:bg-[#e0900a] text-white font-bold transition shadow-lg flex items-center gap-2"
                >
                    Siguiente <i class="fas fa-chevron-right"></i>
                </button>

                <button 
                    type="submit"
                    x-show="currentStep === maxStep"
                    class="px-8 py-3 rounded-xl bg-[#0C4B54] hover:bg-[#093D45] text-white font-bold transition shadow-lg"
                >
                    <i class="fas fa-save"></i> Guardar Secuencia
                </button>
            </div>
        </div>

    </form>


    {{-- ********************************************************************************************** --}}
    {{-- MODAL PARA SUBIR DOCUMENTO --}}
    {{-- ********************************************************************************************** --}}
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
        @keydown.escape.window="showUploadModal = false"
    >
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
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            >
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
                    <form action="#" method="POST" enctype="multipart/form-2025" class="mt-5 space-y-4">
                        @csrf
                        
                        {{-- Campo de archivo con estilo Drag & Drop --}}
                        <div>
                            <label for="file-upload" class="block text-sm font-medium text-gray-700">Seleccionar Archivo</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer hover:border-[#0C4B54] transition-colors duration-200">
                                <div class="space-y-1 text-center">
                                    <i class="fas fa-file-pdf text-4xl text-gray-400"></i>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-[#F59E0B] hover:text-[#e0900a] focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-[#0C4B54] transition">
                                            <span>Sube un archivo</span>
                                            <input id="file-upload" name="documento_secuencia" type="file" class="sr-only" accept=".pdf,.doc,.docx">
                                        </label>
                                        <p class="pl-1">o arrastra y suelta aquí.</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PDF, DOCX hasta 10MB
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Opciones adicionales (opcional) --}}
                        <div class="pt-4 border-t border-gray-100">
                            <label for="tipo-documento" class="block text-sm font-medium text-gray-700">Tipo de Documento</label>
                            <select id="tipo-documento" name="tipo_documento" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-[#0C4B54] focus:border-[#0C4B54] sm:text-sm rounded-md">
                                <option>Secuencia Didáctica Oficial</option>
                                <option>Esquema de Planeación</option>
                            </select>
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
    {{-- FIN DEL MODAL --}}
    {{-- ********************************************************************************************** --}}

</div>

@endsection