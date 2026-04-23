@extends('layouts.principal')

@section('content')
    <div class="space-y-8">

    {{-- ENCABEZADO PRINCIPAL --}}
    <div class="bg-white shadow-sm rounded-lg p-6 flex items-center justify-between border border-[#F3F4F6]">
  <!-- Texto -->
  <div>
    <h2 class="text-xl font-bold text-[#111827]">
      ¡Bienvenido de vuelta! 👋
    </h2>
    <p class="text-[#111827] mt-2 opacity-80">
      Aquí tienes un resumen de la actividad académica.
    </p>
  </div>

  <div class="flex-shrink-0">
    <img src="{{ asset('img/banner.png') }}" 
     alt="Banner académico" 
     class="w-32 h-32">
  </div>
</div>

    {{-- CONTENIDO PRINCIPAL --}}
    <div class="px-6 lg:px-8 py-8 space-y-8">
{{-- ====================================== --}}
        {{-- ACCIONES RÁPIDAS --}}
        {{-- ====================================== --}}
        <div class="bg-white rounded-xl shadow-md p-6 border-t-4 border-[#0F766E]">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-[#F3F4F6] flex items-center justify-center">
                    <i class="fas fa-lightning-bolt text-[#0F766E] text-lg"></i>
                </div>
                <h3 class="text-lg font-semibold text-[#111827]">Acciones Rápidas</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                {{-- Nueva Materia --}}
                <a href="/materias/crear" class="group flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-[#0F766E] hover:bg-[#F3F4F6] transition-all duration-200">
                    <div class="w-10 h-10 rounded-lg bg-[#F3F4F6] group-hover:bg-[#E0F2F1] flex items-center justify-center transition-colors flex-shrink-0">
                        <i class="fas fa-plus text-[#0F766E]"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-[#111827] text-sm">Nueva Materia</p>
                        <p class="text-xs text-[#6B7280]">Crear materia</p>
                    </div>
                </a>

                {{-- Nueva Secuencia --}}
                <a href="/secuenciasCrueltyFreeCreate" class="group flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-[#14B8A6] hover:bg-[#F3F4F6] transition-all duration-200">
                    <div class="w-10 h-10 rounded-lg bg-[#F3F4F6] group-hover:bg-[#D8F7F3] flex items-center justify-center transition-colors flex-shrink-0">
                        <i class="fas fa-plus text-[#14B8A6]"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-[#111827] text-sm">Nueva Secuencia</p>
                        <p class="text-xs text-[#6B7280]">Crear secuencia</p>
                    </div>
                </a>

                {{-- Nuevo Usuario --}}
                <a href="/usuarios/crear" class="group flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-[#0F766E] hover:bg-[#F3F4F6] transition-all duration-200">
                    <div class="w-10 h-10 rounded-lg bg-[#F3F4F6] group-hover:bg-[#E0F2F1] flex items-center justify-center transition-colors flex-shrink-0">
                        <i class="fas fa-plus text-[#0F766E]"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-[#111827] text-sm">Nuevo Revisor</p>
                        <p class="text-xs text-[#6B7280]">Registrar revisor</p>
                    </div>
                </a>

                {{-- Ver Reportes --}}
                <a href="/reportes" class="group flex items-center gap-3 p-4 rounded-lg border border-gray-200 hover:border-[#14B8A6] hover:bg-[#F3F4F6] transition-all duration-200">
                    <div class="w-10 h-10 rounded-lg bg-[#F3F4F6] group-hover:bg-[#D8F7F3] flex items-center justify-center transition-colors flex-shrink-0">
                        <i class="fas fa-file-download text-[#0F766E]"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-[#111827] text-sm">Ver Reportes</p>
                        <p class="text-xs text-[#6B7280]">Descargar reportes</p>
                    </div>
                </a>

            </div>
        </div>

    {{-- ====================================== --}}
    {{-- GRÁFICAS + CALENDARIO --}}
    {{-- ====================================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-6">
            
            {{-- GRÁFICA 1: ENTREGADAS POR MES --}}
            <div class="bg-white shadow-xl p-6 rounded-2xl">
                <h2 class="text-xl font-bold text-[#111827] mb-4 border-b border-[#F3F4F6] pb-2">Secuencias Entregadas por Mes</h2>
                <canvas id="graficaMeses" style="height:350px;"></canvas>
            </div>
        </div>        {{-- CALENDARIO --}}
        <div class="lg:col-span-1">
            <div class="bg-white shadow-xl p-6 rounded-2xl h-full flex flex-col">
                <h2 class="text-xl font-bold text-[#111827] mb-4 border-b border-[#F3F4F6] pb-2">Calendario Escolar</h2>
                <div id="calendar" class="flex-grow min-h-[500px]"></div>
            </div>

        </div>
    </div>
</div>
                </div>
            </div>

<!-- MODAL -->
<div id="eventModal" class="hidden fixed inset-0 bg-black bg-opacity-70 backdrop-blur-sm flex justify-center items-center z-50">
    <div class="bg-white w-11/12 md:w-1/3 p-8 rounded-2xl shadow-2xl">
        <h2 id="modalTitle" class="text-2xl font-bold text-[#111827] mb-4"></h2>
        <p id="modalContent" class="text-[#111827] mb-6 border-b pb-4"></p>
        
        <div class="flex justify-end">
            <button onclick="closeModal()" class="bg-[#0F766E] text-white font-semibold px-6 py-2 rounded-xl shadow-md hover:bg-[#14B8A6] transition">
                Cerrar
            </button>
        </div>
    </div>

    {{-- ====================================== --}}
    {{-- SCRIPTS DE LIBRERÍAS Y LÓGICA --}}
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
            const ctx = document.getElementById('graficaMeses');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(d => {
                        const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                        return months[d.mes - 1] || 'Mes ' + d.mes;
                    }),
                    datasets: [{
                        label: 'Secuencias Entregadas',
                        data: data.map(d => d.total),
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3B82F6',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                font: { size: 12, weight: 'bold' },
                                color: '#6B7280',
                                padding: 15
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { color: '#9CA3AF', font: { size: 11 } },
                            grid: { color: '#F3F4F6', drawBorder: false }
                        },
                        x: {
                            ticks: { color: '#9CA3AF', font: { size: 11 } },
                            grid: { display: false, drawBorder: false }
                        }
                    }
                }
            });
        });
                
            document.addEventListener('DOMContentLoaded', async function() {
            const eventos = await fetch('/eventos').then(r => r.json());
            const calendarEl = document.getElementById('calendar');

            let calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                selectable: true,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                events: eventos,

                select: async function(info) {
                    const title = prompt('Título del evento:');
                    if(!title) return;

                    const nuevo = {
                        title: title,
                        start: info.startStr,
                        end: info.endStr,
                        color: '#3B82F6'
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