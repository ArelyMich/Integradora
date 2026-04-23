<!DOCTYPE html>
<html lang="es" x-data="{ open: false, userMenu: false, notif: false, perfilModal: false }">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Panel' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
       
        body {
            font-family: "Segoe UI", -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
        }

        /* SIDEBAR MODERNO */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #ffffff 0%, #F3F4F6 100%);
            height: 100vh;
            color: #0F766E;
            padding: 24px 16px;
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, 0.06);
            z-index: 50;
            border-right: 1px solid rgba(0, 0, 0, 0.05);
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #F3F4F6 #F3F4F6;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #F3F4F6;
        }

        .sidebar-hidden { 
            transform: translateX(-280px);
        }

        .overlay { 
            position: fixed; 
            inset: 0; 
            background: rgba(0, 0, 0, 0.5); 
            z-index: 40;
            backdrop-filter: blur(4px);
        }

        /* NAVBAR MODERNO */
        .topbar {
            width: calc(100% - 280px);
            height: 64px;
            background: #ffffff;
            color: #1e293b;
            position: fixed;
            top: 0;
            left: 280px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            z-index: 60;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .topbar.shifted { 
            left: 0; 
            width: 100%;
        }

        .main { 
            margin-left: 280px; 
            padding: 80px 28px 40px 28px; 
            width: 100%; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main.expanded { 
            margin-left: 0; 
        }

        .topbar-icon-btn { 
            padding: 8px 10px; 
            border-radius: 8px; 
            transition: all 0.2s ease;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .topbar-icon-btn:hover { 
            background-color: #f1f5f9;
            color: #0F766E;
        }

        .topbar-icon-btn.active {
            background-color: #e0e7ff;
            color: #0F766E;
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            body { 
                display: block; 
            }
            
            .sidebar { 
                transform: translateX(-280px); 
                z-index: 70;
                width: 280px;
            }
            
            .sidebar.shown-mobile { 
                transform: translateX(0); 
            }
            
            .topbar { 
                left: 0 !important; 
                width: 100% !important;
                padding: 0 16px;
            }
            
            .main { 
                margin-left: 0 !important; 
                padding: 76px 16px 20px 16px;
            }
        }

        /* ANIMACIONES */
        @keyframes slideIn {
            from {
                transform: translateX(-4px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }

        [x-show*="Modal"], 
        .fixed.inset-0.z-50 {
            z-index: 100 !important;
        }

        /* Opcionalmente, puedes ser más específico si usas 
        una clase común en tus modales */
        .modal-overlay {
            z-index: 100 !important;
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

    <!-- TOPBAR MODERNO -->
    <div id="topbar" class="topbar" :class="open && window.innerWidth >= 768 ? '' : 'shifted'" role="navigation">
        <div class="flex items-center gap-3">
            <!-- Toggle Sidebar -->
            <button @click="open = !open" class="topbar-icon-btn hidden md:flex rounded-lg text-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>

            <!-- Branding -->
            <span class="text-sm font-bold text-gray-900 tracking-wide hidden sm:inline ml-2">
                Sistema de Gestión Académica
            </span>
        </div>

        <div class="flex items-center gap-2 md:gap-4">
            <!-- User Info (Desktop) -->
            <div class="hidden md:flex items-center gap-2 px-3 py-1.5 bg-gray-50 rounded-lg border border-gray-200 text-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-gray-700 font-medium">{{ explode(' ', Auth::user()->name)[0] ?? 'User' }}</span>
                <span class="text-xs text-gray-500 font-medium">{{ Auth::user()->roles?->first()?->nombre ?? "Admin" }}</span>
            </div>

            <!-- Settings -->
            <a href="#" class="topbar-icon-btn" title="Configuración">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </a>

            <!-- Notifications -->
            <div class="relative" @click="notif = !notif">
                <button class="topbar-icon-btn relative" title="Notificaciones">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border border-white shadow-sm"></span>
                </button>

                <!-- Notificaciones Dropdown -->
                <div x-show="notif" @click.outside="notif = false" x-transition
                     class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <p class="text-sm font-semibold text-gray-900">Notificaciones</p>
                    </div>
                    <div class="p-4">
                        <p class="text-sm text-gray-500 text-center py-6">No tienes notificaciones nuevas.</p>
                    </div>
                    <div class="px-4 py-2 border-t border-gray-100 bg-gray-50 text-center">
                        <a href="#" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Ver todas</a>
                    </div>
                </div>
            </div>

            <!-- Profile -->
            <div class="relative" @click="userMenu = !userMenu">
                <button class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 text-white flex items-center justify-center text-sm font-semibold shadow-sm">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </div>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </button>

                <!-- User Menu Dropdown -->
                <div x-show="userMenu" @click.outside="userMenu = false" x-transition
                     class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                        <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ Auth::user()->email }}</p>
                    </div>
                    
                    <a href="#" @click.prevent="perfilModal = true; userMenu = false"
                       class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Ver perfil
                    </a>
                    
                    <a href="{{ route('logout') }}" 
                       class="block px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 border-t border-gray-100 transition-colors flex items-center gap-2 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
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
