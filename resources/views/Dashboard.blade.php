@extends('layouts.principal')

@section('content')
    <div class="space-y-8">

        {{-- ENCABEZADO PRINCIPAL --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#2FA69A] flex items-center justify-center shadow-lg">
                        <i class="fas fa-chart-line text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
                        <p class="text-sm text-slate-500 mt-0.5">Resumen del sistema académico</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====================================== --}}
        {{-- ************** KPI CARDS ************* --}}
        {{-- ====================================== --}}

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">

            {{-- KPI 1: Total de Docentes --}}
            <div
                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-[#2FA69A]/10">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-3xl font-bold text-slate-800">{{ $totalDocentes }}</span>
                        <p class="text-sm text-slate-500 mt-1 font-medium">Docentes</p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#2FA69A] to-[#23877E] flex items-center justify-center shadow-lg">
                        <i class="fas fa-chalkboard-teacher text-xl text-white"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <span class="text-xs text-[#2FA69A] font-medium"><i class="fas fa-arrow-up mr-1"></i>Activos en el
                        sistema</span>
                </div>
            </div>

            {{-- KPI 2: Materias Activas --}}
            <div
                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-[#2FA69A]/10">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-3xl font-bold text-slate-800">{{ $materiasActivas }}</span>
                        <p class="text-sm text-slate-500 mt-1 font-medium">Materias Activas</p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#2FA69A] to-[#23877E] flex items-center justify-center shadow-lg">
                        <i class="fas fa-book text-xl text-white"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <span class="text-xs text-[#2FA69A] font-medium"><i class="fas fa-check-circle mr-1"></i>Disponibles
                        para impartir</span>
                </div>
            </div>

            {{-- KPI 3: Secuencias Aprobadas --}}
            <div
                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-100">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-3xl font-bold text-slate-800">{{ $secAprobadas }}</span>
                        <p class="text-sm text-slate-500 mt-1 font-medium">Aprobadas</p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg">
                        <i class="fas fa-check-circle text-xl text-white"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <span class="text-xs text-emerald-600 font-medium"><i class="fas fa-star mr-1"></i>Secuencias
                        verificadas</span>
                </div>
            </div>

            {{-- KPI 4: Secuencias Pendientes --}}
            <div
                class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 transition duration-200 hover:-translate-y-1 hover:shadow-lg hover:shadow-amber-100">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-3xl font-bold text-slate-800">{{ $secPendientes }}</span>
                        <p class="text-sm text-slate-500 mt-1 font-medium">Pendientes</p>
                    </div>
                    <div
                        class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center shadow-lg">
                        <i class="fas fa-hourglass-half text-xl text-white"></i>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100">
                    <span class="text-xs text-amber-600 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>En
                        revisión</span>
                </div>
            </div>

        </div>

        {{-- ====================================== --}}
        {{-- GRÁFICAS + CALENDARIO --}}
        {{-- ====================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                {{-- GRÁFICA 1: ENTREGADAS POR MES --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-[#2FA69A]/10 flex items-center justify-center">
                            <i class="fas fa-chart-bar text-[#2FA69A]"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Secuencias por Mes</h2>
                    </div>
                    <canvas id="graficaMeses" style="height:220x;"></canvas>
                </div>

                {{-- GRÁFICA 2: ESPECIALIDADES (DONA) --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-[#2FA69A]/10 flex items-center justify-center">
                            <i class="fas fa-chart-pie text-[#2FA69A]"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Por Especialidad</h2>
                    </div>
                    <canvas id="graficaDona" style="height:300px;"></canvas>
                </div>

                
                {{-- PROGRESO DEL MES --}}

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition">

                    <div class="flex items-center gap-3 mb-5">

                        <div class="w-8 h-8 rounded-lg bg-[#2FA69A]/10 flex items-center justify-center">

                            <i class="fas fa-tasks text-[#2FA69A]"></i>

                        </div>

                        <h2 class="text-lg font-semibold text-slate-800">

                            Progreso del Mes

                        </h2>

                    </div>

                    <div class="space-y-5">

                        {{-- Barra 1 --}}
                        <div>

                            <div class="flex justify-between text-sm mb-1">

                                <span>Secuencias Completadas</span>

                                <span class="font-semibold">8/10</span>

                            </div>

                            <div class="w-full bg-slate-200 rounded-full h-2">

                                <div class="bg-[#2FA69A] h-2 rounded-full w-[80%]"></div>

                            </div>

                        </div>

                        {{-- Barra 2 --}}
                        <div>

                            <div class="flex justify-between text-sm mb-1">

                                <span>Tasa de Aprobación</span>

                                <span class="font-semibold">85%</span>

                            </div>

                            <div class="w-full bg-slate-200 rounded-full h-2">

                                <div class="bg-[#10B981] h-2 rounded-full w-[85%]"></div>

                            </div>

                        </div>

                        {{-- Barra 3 --}}
                        <div>

                            <div class="flex justify-between text-sm mb-1">

                                <span>Objetivo Mensual</span>

                                <span class="font-semibold">70%</span>

                            </div>

                            <div class="w-full bg-slate-200 rounded-full h-2">

                                <div class="bg-[#F59E0B] h-2 rounded-full w-[70%]"></div>

                            </div>

                        </div>

                    </div>

                </div>
            </div>

            {{-- CALENDARIO --}}
            <div class="lg:col-span-1">
                <div
                    class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 h-full flex flex-col hover:shadow-md transition">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-[#2FA69A]/10 flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-[#2FA69A]"></i>
                        </div>
                        <h2 class="text-lg font-semibold text-slate-800">Calendario</h2>
                    </div>
                    <div 
id="calendar" 
class="flex-grow 
min-h-[320px] 
bg-gradient-to-br 
from-white 
to-[#F8FAFC] 
rounded-xl 
p-2">
</div>
                </div>
            </div>

        </div>

    </div>

    <!-- MODAL -->
    <div id="eventModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex justify-center items-center z-50">
        <div class="bg-white w-11/12 md:w-1/3 p-6 rounded-2xl shadow-lg border border-slate-200">
            <h2 id="modalTitle" class="text-xl font-semibold text-slate-800 mb-3"></h2>
            <p id="modalContent" class="text-slate-600 mb-4 text-sm"></p>

            <div class="flex justify-end">
                <button onclick="closeModal()"
                    class="bg-[#2FA69A] text-white font-medium px-5 py-2 rounded-lg hover:bg-[#23877E] transition text-sm">
                    Cerrar
                </button>
            </div>
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
                new Chart(document.getElementById('graficaMeses'), {
                    type: 'bar',
                    data: {
                        labels: data.map(d => 'Mes ' + d.mes),
                        datasets: [{
                            label: 'Entregadas',
                            data: data.map(d => d.total),
                            backgroundColor: '#2FA69A'
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
                                '#2FA69A', '#10B981', '#F59E0B', '#EF4444', '#6366F1'
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
                    if (!title) return;

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
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(nuevo)
                    });

                    calendar.addEvent(nuevo);
                },

                eventClick: function(info) {
                    openModal(info.event.title, info.event.extendedProps.description ||
                        'Sin descripción');
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
