<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema | UTH</title>
    <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Carga de Alpine.js para interactividad (mostrar/ocultar contraseña) -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Google reCAPTCHA v2 -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        /* Fuente principal del proyecto */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8; /* Fondo institucional claro */
        }
        /* Colores institucionales definidos */
        .uth-blue {
            background-color: #004A80; 
        }
        .uth-blue-hover {
            background-color: #003761;
        }
        .text-uth-blue {
            color: #004A80;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <!-- Contenedor Central del Formulario -->
    <div class="w-full max-w-lg bg-white p-8 sm:p-10 rounded-2xl shadow-2xl transition duration-300">
        
        <!-- Encabezado del Formulario -->
        <header class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                <!-- Icono de Llave o Candado -->
                <svg class="w-10 h-10 text-uth-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2v5a2 2 0 01-2 2h-2m-2-2a2 2 0 01-2-2V9a2 2 0 012-2h2m-2 2h2m-2 4h2m-4-8h8a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900">
                Iniciar Sesión
            </h1>
            <p class="text-gray-500 mt-2 text-sm">
                Acceso a la Gestión de Secuencias Didácticas UTH
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
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    required 
                    autofocus 
                    value="{{ old('email') }}"
                    autocomplete="username"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-uth-blue focus:border-uth-blue transition duration-150"
                    placeholder="ejemplo@uth.edu.mx"
                >
            </div>

            <!-- Campo de Contraseña con Alpine.js (Mostrar/Ocultar) -->
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <div class="relative">
                    <input 
                        :type="showPassword ? 'text' : 'password'" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-uth-blue focus:border-uth-blue transition duration-150 pr-12"
                        placeholder="••••••••"
                    >
                    <!-- Botón para mostrar/ocultar contraseña -->
                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition duration-150">
                        <svg x-show="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <!-- Icono de Ojo cerrado -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.024 10.024 0 0112 19c-4.478 0-8.268-2.943-9.543-7.235C2.109 9.38 5.717 6 12 6c1.674 0 3.267.318 4.708.883M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <svg x-show="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <!-- Icono de Ojo abierto -->
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM12 4.5c4.75 0 8.823 3.018 10.513 7.5C20.823 16.482 16.75 19.5 12 19.5c-4.75 0-8.823-3.018-10.513-7.5C3.177 8.018 7.25 4.5 12 4.5z"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Google reCAPTCHA v2 -->
            <div style="margin-bottom: 1.5rem; display: flex; justify-content: center;">
                <div class="g-recaptcha" data-sitekey="{{ $recaptcha_key }}"></div>
            </div>

            <!-- Botón Principal de Login -->
            <button type="submit" 
                class="w-full uth-blue text-white font-bold py-3 rounded-xl shadow-lg transition duration-300 transform hover:bg-uth-blue-hover hover:scale-[1.01] focus:outline-none focus:ring-4 focus:ring-uth-blue focus:ring-opacity-50">
                Iniciar Sesión
            </button>
        </form>

        <!-- Opciones Adicionales (Recuperación de Contraseña) -->
        <div class="mt-6 text-center">
            <a href="{{ route('password.recovery.email') }}" class="text-sm font-medium text-uth-blue hover:text-blue-700 transition duration-150">
                ¿Olvidaste tu contraseña?
            </a>
        </div>

    </div>

</body>
</html>
