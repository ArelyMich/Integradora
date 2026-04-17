@extends('layouts.principal')

@section('content')

<div class="p-8 space-y-8 min-h-screen bg-gray-50">
    
    {{-- ENCABEZADO PRINCIPAL --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
        <h1 class="text-4xl font-extrabold text-[#0C4B54] border-l-4 border-[#0C4B54] pl-3">
            Tablero de Control Académico
        </h1>
        <p class="text-gray-500 mt-2 sm:mt-0 text-sm">Resumen en tiempo real del sistema educativo</p>
    </div>

    {{-- ====================================== --}}
    {{-- ************** KPI CARDS ************* --}}
    {{-- ====================================== --}}

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        {{-- KPI 1: Total de Docentes --}}
        <div class="bg-white p-6 rounded-2xl shadow-xl border-b-4 border-blue-500 transition duration-300 hover:scale-[1.02]">
            <div class="flex items-center justify-between">
                <span class="text-3xl font-bold text-blue-600">{{ $totalDocentes }}</span>
                <i class="fas fa-chalkboard-teacher text-3xl text-blue-200"></i>
            </div>
            <p class="text-sm font-semibold text-gray-700 mt-1">Docentes Registrados</p>
        </div>

        {{-- KPI 2: Materias Activas --}}
        <div class="bg-white p-6 rounded-2xl shadow-xl border-b-4 border-green-500 transition duration-300 hover:scale-[1.02]">
            <div class="flex items-center justify-between">
                <span class="text-3xl font-bold text-green-600">{{ $materiasActivas }}</span>
                <i class="fas fa-book-open text-3xl text-green-200"></i>
            </div>
            <p class="text-sm font-semibold text-gray-700 mt-1">Materias Activas</p>
        </div>

        {{-- KPI 3: Secuencias Aprobadas --}}
        <div class="bg-white p-6 rounded-2xl shadow-xl border-b-4 border-yellow-500 transition duration-300 hover:scale-[1.02]">
            <div class="flex items-center justify-between">
                <span class="text-3xl font-bold text-yellow-600">{{ $secAprobadas }}</span>
                <i class="fas fa-check-circle text-3xl text-yellow-200"></i>
            </div>
            <p class="text-sm font-semibold text-gray-700 mt-1">Secuencias Aprobadas</p>
        </div>

        {{-- KPI 4: Secuencias Pendientes --}}
        <div class="bg-white p-6 rounded-2xl shadow-xl border-b-4 border-red-500 transition duration-300 hover:scale-[1.02]">
            <div class="flex items-center justify-between">
                <span class="text-3xl font-bold text-red-600">{{ $secPendientes }}</span>
                <i class="fas fa-hourglass-half text-3xl text-red-200"></i>
            </div>
            <p class="text-sm font-semibold text-gray-700 mt-1">Pendientes de Revisión</p>
        </div>

    </div>

    {{-- ====================================== --}}
    {{-- GRÁFICAS + CALENDARIO --}}
    {{-- ====================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">
            
            {{-- GRÁFICA 1: ENTREGADAS POR MES --}}
            <div class="bg-white shadow-xl p-6 rounded-2xl">
                <h2 class="text-xl font-bold text-gray-700 mb-4 border-b pb-2">Secuencias Entregadas por Mes</h2>
                <canvas id="graficaMeses" style="height:350px;"></canvas>
            </div>
            
            {{-- GRÁFICA 2: ESPECIALIDADES (DONA) --}}
            <div class="bg-white shadow-xl p-6 rounded-2xl">
                <h2 class="text-xl font-bold text-gray-700 mb-4 border-b pb-2">Secuencias por Especialidad</h2>
                <canvas id="graficaDona" style="height:350px;"></canvas>
            </div>
        </div>

        {{-- CALENDARIO --}}
        <div class="lg:col-span-1">
            <div class="bg-white shadow-xl p-6 rounded-2xl h-full flex flex-col">
                <h2 class="text-xl font-bold text-gray-700 mb-4 border-b pb-2">Calendario Escolar</h2>
                <div id="calendar" class="flex-grow min-h-[500px]"></div>
            </div>
        </div>

    </div>

</div>

<!-- MODAL -->
<div id="eventModal" class="hidden fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm flex justify-center items-center z-50">
    <div class="bg-white w-11/12 md:w-1/3 p-8 rounded-2xl shadow-2xl">
        <h2 id="modalTitle" class="text-2xl font-bold text-[#0C4B54] mb-4"></h2>
        <p id="modalContent" class="text-gray-700 mb-6 border-b pb-4"></p>
        
        <div class="flex justify-end">
            <button onclick="closeModal()" class="bg-red-600 text-white font-semibold px-6 py-2 rounded-xl shadow-md hover:bg-red-700 transition">
                Cerrar
            </button>
        </div>
    </div>
</div>

{{-- ====================================== --}}
{{-- SCRIPTS DE LIBRERÍAS Y LÓGICA--}}
{{-- ====================================== --}}

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

<script>

// ============================================
// 1. GRAFICA BARRAS - SECUENCIAS POR MES
// ============================================
fetch('/chart/secuencias-mes')
.then(res => res.json())
.then(data => {
    new Chart(document.getElementById('graficaMeses'), {
        type: 'bar',
        data: {
            labels: data.map(d => 'Mes ' + d.mes),
            datasets: [{
                label: 'Entregadas',
                data: data.map(d => d.total),
                backgroundColor: '#0C4B54'
            }]
        }
    });
});

// ============================================
// 2. GRAFICA DONA - ESPECIALIDADES
// ============================================
fetch('/chart/especialidades')
.then(res => res.json())
.then(data => {
    new Chart(document.getElementById('graficaDona'), {
        type: 'doughnut',
        data: {
            labels: data.map(d => d.especialidad),
            datasets: [{
                data: data.map(d => d.total),
                backgroundColor: [
                    '#0C4B54','#10B981','#F59E0B','#EF4444','#6366F1'
                ]
            }]
        }
    });
});

// ============================================
// 3. FULLCALENDAR - EVENTOS DESDE BD
// ============================================
document.addEventListener('DOMContentLoaded', async function() {

    const eventos = await fetch('/eventos').then(r => r.json());
    const calendarEl = document.getElementById('calendar');

    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'es',
        selectable: true,

        events: eventos,

        select: async function(info) {
            const title = prompt('Título del evento:');
            if(!title) return;

            const nuevo = {
                title: title,
                start: info.startStr,
                end: info.endStr,
                color: '#F59E0B'
            };

            // Guardar en BD
            await fetch('/eventos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(nuevo)
        });

            calendar.addEvent(nuevo);
        },

        eventClick: function(info) {
            openModal(info.event.title, info.event.extendedProps.description || 'Sin descripción');
        }
    });

    calendar.render();
});

// ============================================
// MODAL
// ============================================
function openModal(t, c) {
    document.getElementById('modalTitle').innerText = t;
    document.getElementById('modalContent').innerText = c;
    document.getElementById('eventModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('eventModal').classList.add('hidden');
}

</script>

@endsection
