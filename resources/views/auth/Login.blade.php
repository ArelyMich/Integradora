<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema | UTH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #E6F4F2 0%, #d0e8e5 50%, #b8ddd6 100%);
            min-height: 100vh;
        }
        .theme-primary {
            background-color: #2FA69A;
        }
        .theme-primary-hover {
            background-color: #23877E;
        }
        .text-theme-primary {
            color: #2FA69A;
        }
        .ring-theme-primary:focus {
            --tw-ring-color: #2FA69A;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <!-- Contenedor Central del Formulario -->
    <div class="w-full max-w-md bg-white p-8 sm:p-10 rounded-2xl shadow-2xl transition duration-300 border border-slate-200">
        
        <!-- Encabezado del Formulario -->
        <header class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                <div class="w-16 h-16 rounded-2xl bg-[#2FA69A] flex items-center justify-center shadow-lg">
                    <i class="fas fa-graduation-cap text-3xl text-white"></i>
                </div>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-800">
                Iniciar Sesión
            </h1>
            <p class="text-slate-500 mt-2 text-sm">
                Gestión de Secuencias Didácticas UTH
            </p>
        </header>

        <!-- Mensajes de Error (Laravel Blade Style) -->
        <!-- NOTA: Debes descomentar y adaptar esto en tu entorno Laravel para mostrar errores -->
        {{-- @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif --}}

        <!-- Mostrar errores de validación -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm" role="alert">
                <strong class="font-bold">Error:</strong>
                <ul class="mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario de Login -->
        <!-- x-data inicia Alpine.js con una variable de estado para la contraseña -->
        <form method="POST" action="{{ route('login.process') }}" x-data="{ showPassword: false }">
            <!-- @csrf es el token de seguridad de Laravel -->
            @csrf

            <!-- Campo de Email -->
            <div class="mb-5">
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Correo Electrónico</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        autofocus 
                        value="{{ old('email') }}"
                        autocomplete="username"
                        class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-[#2FA69A] focus:border-[#2FA69A] transition duration-150"
                        placeholder="ejemplo@uth.edu.mx"
                    >
                </div>
            </div>

            <!-- Campo de Contraseña con Alpine.js (Mostrar/Ocultar) -->
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input 
                        :type="showPassword ? 'text' : 'password'" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        class="w-full pl-10 pr-12 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-[#2FA69A] focus:border-[#2FA69A] transition duration-150"
                        placeholder="••••••••"
                    >
                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition duration-150">
                        <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                    </button>
                </div>
            </div>

            <!-- Google reCAPTCHA v2 -->
            <div style="margin-bottom: 1.5rem; display: flex; justify-content: center;">
                <div class="g-recaptcha" data-sitekey="{{ $recaptcha_key }}"></div>
            </div>

            <!-- Botón Principal de Login -->
            <button type="submit" 
                class="w-full theme-primary text-white font-bold py-3 rounded-xl shadow-lg transition duration-300 transform hover:theme-primary-hover hover:scale-[1.01] focus:outline-none focus:ring-4 focus:ring-[#2FA69A] focus:ring-opacity-50">
                <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
            </button>
        </form>

        <!-- Opciones Adicionales (Recuperación de Contraseña) -->
        <div class="mt-6 text-center">
            <a href="{{ route('password.request') }}" class="text-sm font-medium text-[#2FA69A] hover:text-[#23877E] transition duration-150">
                <i class="fas fa-key mr-1"></i>¿Olvidaste tu contraseña?
            </a>
        </div>

    </div>

</body>
</html>
