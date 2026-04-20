<!DOCTYPE html>
<html lang="es" x-data="{ open: window.innerWidth >= 768, userMenu: false, notif: false, perfilModal: false }">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Panel' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --c1: #BDDIBD;
            --c2: #85B093;
            --c3: #568F7C;
            --c4: #326D6C;
            --c5: #173C4C;
            --c6: #07142B;
            --c7: #000009;
        }

        body {
            font-family: "Segoe UI", sans-serif;
            background: linear-gradient(135deg, var(--c1), var(--c2));
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 260px;
            background: var(--c6);
            height: 100vh;
            color: white;
            padding: 25px 20px;
            position: fixed;
            left: 0;
            top: 0;
            transition: 0.3s ease;
            box-shadow: 5px 0 15px rgba(0,0,0,0.2);
            z-index: 50;
        }
        .sidebar-hidden { transform: translateX(-260px); }
        .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 40; }

        .topbar {
            width: calc(100% - 260px);
            height: 60px;
            background: var(--c6);
            color: white;
            position: fixed;
            top: 0;
            left: 260px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            z-index: 60;
            transition: left 0.3s ease, width 0.3s ease;
        }
        .topbar.shifted { left: 0; width: 100%; }
        .main { margin-left: 260px; padding: 80px 25px; width: 100%; transition: margin-left 0.3s ease; }
        .main.expanded { margin-left: 0; }
        .topbar-icon-btn { padding: 8px; border-radius: 9999px; transition: background-color 0.2s; }
        .topbar-icon-btn:hover { background-color: var(--c5); }

        @media (max-width: 768px) {
            body { display: block; }
            .sidebar { transform: translateX(-260px); z-index: 70; }
            .sidebar.shown-mobile { transform: translateX(0); }
            .topbar { left: 0 !important; width: 100% !important; }
            .main { margin-left: 0 !important; padding-top: 80px; }
        }
    </style>
</head>
<body>

    <!-- OVERLAY móvil -->
    <div x-show="open && window.innerWidth < 768" x-transition.opacity @click="open = false" class="overlay"></div>

    <!-- SIDEBAR -->
    <div id="sidebar" class="sidebar" :class="{'sidebar-hidden': !open, 'shown-mobile': open && window.innerWidth < 768}">
        <x-sidebar />
    </div>

    <!-- TOPBAR -->
    <div id="topbar" class="topbar" :class="open && window.innerWidth >= 768 ? '' : 'shifted'" role="navigation">
        <div class="flex items-center gap-4">
            <button @click="open = !open" class="text-white text-xl topbar-icon-btn">
                <i class="fas fa-bars"></i>
            </button>
            <span class="font-bold text-white tracking-wider">Panel Administrativo</span>
        </div>

        <div class="flex items-center gap-6">
            <span class="hidden md:inline text-white font-semibold tracking-wide text-sm">
                {{ Auth::user()->name }} [{{ Auth::user()->roles?->first()?->nombre ?? "S/A" }}]
            </span>

            <a href="#" class="text-xl text-white hover:text-[var(--c2)] transition topbar-icon-btn">
                <i class="fa-solid fa-gear"></i>
            </a>

            <!-- Notificaciones -->
            <div class="relative" @click="notif = !notif">
                <button class="text-xl text-white topbar-icon-btn relative"><i class="fa-regular fa-bell"></i></button>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full border border-white animate-pulse"></span>
                <div x-show="notif" @click.outside="notif = false" x-transition
                     class="absolute right-0 mt-3 w-64 bg-white text-[var(--c6)] rounded-xl shadow-2xl p-4 border border-gray-100 z-50">
                    <p class="text-sm font-bold">Notificaciones</p>
                    <p class="text-xs opacity-70">No tienes notificaciones nuevas.</p>
                    <a href="#" class="mt-2 block text-xs text-[var(--c3)] hover:text-[var(--c4)] font-semibold">Ver todas</a>
                </div>
            </div>

            <!-- Perfil -->
            <div class="relative">
                <button @click="userMenu = !userMenu" class="w-10 h-10 rounded-full bg-[var(--c4)] text-white flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-user"></i>
                </button>

                <div x-show="userMenu" @click.outside="userMenu = false" x-transition
                     class="absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 py-1 z-50">
                    <div class="px-4 py-2 text-sm text-gray-700 border-b">
                        <p class="font-semibold">{{ Auth::user()->name }} {{ Auth::user()->apellido_paterno }}</p>
                        <p class="text-xs opacity-70">Miembro</p>
                    </div>
                    <a href="#" @click.prevent="perfilModal = true; userMenu = false"
                       class="block px-4 py-2 text-sm text-gray-700 border-t hover:bg-[var(--c1)]">
                        Ver mis datos
                    </a>
                    <a href="{{ route('logout') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 border-t mt-1 pt-2">
                        Cerrar sesión
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN -->
    <div id="main" class="main" :class="open ? '' : 'expanded'">
        @yield('content')
    </div>

   <!-- MODAL PERFIL -->
<div x-show="perfilModal" x-cloak 
     class="fixed inset-0 z-[100] flex items-center justify-center p-4">

    <!-- Overlay -->
    <div x-show="perfilModal"
         x-transition.opacity
         class="fixed inset-0 bg-black/40 backdrop-blur-sm"
         @click="perfilModal = false"></div>

    <!-- Contenedor del modal -->
    <div x-show="perfilModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-6 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-6 scale-95"
         class="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl border border-gray-200 
                overflow-hidden max-h-[90vh] flex flex-col">

        <!-- Header -->
        <div class="px-6 py-4 border-b bg-white flex justify-between items-center">
            <h3 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-user-astronaut text-[#07142B] text-2xl"></i>
                Mi Perfil
            </h3>
            <button @click="perfilModal = false" 
                    class="text-gray-500 hover:bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center transition">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>

        <!-- Contenido scroll -->
        <div class="p-6 overflow-y-auto">

            <!-- Tarjeta del usuario -->
            <div class="bg-gray-50 rounded-xl p-6 mb-8 border border-gray-200 shadow-sm flex flex-col md:flex-row items-center gap-6">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-[#07142B] to-[#07142B] p-1 shadow">
                    <div class="w-full h-full rounded-full bg-white flex items-center justify-center text-4xl text-[#07142B] font-bold">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                </div>

                <div class="flex-1 text-center md:text-left space-y-2">
                    <h2 class="text-2xl font-bold text-gray-800">
                        {{ Auth::user()->name ?? 'Usuario' }} {{ Auth::user()->apellido_paterno ?? '' }}
                    </h2>

                    <div class="flex flex-wrap justify-center md:justify-start gap-3 text-sm">

                        <span class="px-3 py-1 rounded-full bg-[#07142B]/10 text-[#07142B] border border-[#07142B]/30">
                            <i class="fa-solid fa-shield-halved mr-1"></i>
                            {{ Auth::user()->roles?->first()?->nombre ?? "Admin" }}
                        </span>

                        <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 border border-gray-300">
                            <i class="fa-solid fa-envelope mr-1"></i>
                            {{ Auth::user()->email ?? 'email@ejemplo.com' }}
                        </span>

                    </div>
                </div>
            </div>

            <!-- MENSAJES -->
            @if(session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 4000)"
                 class="mb-6 p-4 rounded-lg bg-green-100 border border-green-300 text-green-800 flex items-center gap-3">
                <i class="fa-solid fa-check-circle text-xl"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 p-4 rounded-lg bg-red-100 border border-red-300 text-red-700">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- FORMULARIOS -->
            <div class="grid md:grid-cols-2 gap-6">

                <!-- Usuario -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center gap-3 mb-5 pb-3 border-b border-gray-200">
                        <div class="w-9 h-9 rounded bg-[#07142B]/10 text-[#07142B] flex items-center justify-center">
                            <i class="fa-solid fa-fingerprint"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-700">Usuario</h4>
                    </div>

                    <form action="{{ route('perfil.actualizarUsername') }}" method="POST" class="space-y-4">
                        @csrf

                        <div class="relative">
                            <span class="absolute left-3 top-3.5 text-[#07142B]">
                                <i class="fa-solid fa-at"></i>
                            </span>
                            <input type="text" name="username" value="{{ Auth::user()->username ?? '' }}"
                                   placeholder="Nuevo usuario"
                                   class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 bg-white 
                                          text-gray-700 placeholder-gray-400
                                          focus:ring-2 focus:ring-[#07142B] focus:border-[#07142B] transition">
                        </div>

                        <button type="submit"
                                class="w-full py-2.5 rounded-lg font-semibold text-white 
                                       bg-[#07142B] hover:bg-[#0c203f] transition shadow">
                            Actualizar Usuario
                        </button>
                    </form>
                </div>

                <!-- Seguridad -->
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition">
                    <div class="flex items-center gap-3 mb-5 pb-3 border-b border-gray-200">
                        <div class="w-9 h-9 rounded bg-[#07142B]/10 text-[#07142B] flex items-center justify-center">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-700">Seguridad</h4>
                    </div>

                    <form action="{{ route('perfil.cambiarContrasena') }}" method="POST" class="space-y-4">
                        @csrf

                        <div class="relative">
                            <span class="absolute left-3 top-3.5 text-[#07142B]">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <input type="password" name="current_password" placeholder="Contraseña actual"
                                   class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 bg-white 
                                          focus:ring-2 focus:ring-[#07142B] focus:border-[#07142B]">
                        </div>

                        <div class="relative">
                            <span class="absolute left-3 top-3.5 text-[#07142B]">
                                <i class="fa-solid fa-unlock"></i>
                            </span>
                            <input type="password" name="password" placeholder="Nueva contraseña"
                                   class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 bg-white 
                                          focus:ring-2 focus:ring-[#07142B] focus:border-[#07142B]">
                        </div>

                        <div class="relative">
                            <span class="absolute left-3 top-3.5 text-[#07142B]">
                                <i class="fa-solid fa-unlock-keyhole"></i>
                            </span>
                            <input type="password" name="password_confirmation" placeholder="Confirmar contraseña"
                                   class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 bg-white 
                                          focus:ring-2 focus:ring-[#07142B] focus:border-[#07142B]">
                        </div>

                        <button type="submit"
                                class="w-full py-2.5 rounded-lg font-semibold text-white 
                                       bg-[#07142B] hover:bg-[#0c203f] transition shadow">
                            Cambiar Contraseña
                        </button>
                    </form>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t flex justify-end">
            
        </div>

    </div>
</div>




</body>
</html>
