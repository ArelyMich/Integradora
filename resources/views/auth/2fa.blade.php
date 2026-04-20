<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación en Dos Pasos | UTH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
        }
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

<body class="min-h-screen flex items-center justify-center bg-gray-900 text-white">

    <form method="POST" action="/2fa"
        class="bg-gray-800 p-8 rounded-xl shadow-md w-full max-w-sm space-y-4">
        @csrf

        <h2 class="text-xl font-bold text-center">Verificación en dos pasos</h2>
        <p class="text-center text-gray-400 text-sm">
            Ingresa el código que se generó para tu cuenta
        </p>

        <input 
            type="text" 
            name="code" 
            placeholder="Código de 6 dígitos"
            class="w-full px-4 py-3 rounded bg-gray-700 text-white outline-none"
            required>

        <button 
            type="submit" 
            class="w-full bg-blue-600 hover:bg-blue-700 py-2 rounded font-bold">
            Verificar
        </button>

        @if ($errors->any())
            <p class="text-red-500 text-center">
                {{ $errors->first() }}
            </p>
        </div>

        <!-- Errores -->
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                <strong class="font-bold">Error:</strong>
                <ul class="mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="/2fa" x-data="{ code: '' }" @submit="$event.preventDefault(); $el.submit();">
            @csrf

            <!-- Campo de Código -->
            <div class="mb-6">
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Código de Verificación</label>
                <input 
                    type="text" 
                    id="code"
                    name="code" 
                    x-model="code"
                    inputmode="numeric"
                    placeholder="000000"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    required
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl text-center text-3xl font-bold tracking-widest focus:border-uth-blue focus:ring-2 focus:ring-blue-200 focus:outline-none transition"
                    @input="code = $event.target.value.replace(/[^0-9]/g, '').slice(0, 6)"
                >
                <p class="text-gray-500 text-xs mt-2 text-center">
                    Ingresa solo números
                </p>
            </div>

            <!-- Botón de Verificación -->
            <button 
                type="submit"
                :disabled="code.length < 6"
                class="w-full uth-blue text-white font-bold py-3 rounded-xl shadow-lg transition duration-300 transform hover:bg-uth-blue-hover hover:scale-[1.01] focus:outline-none focus:ring-4 focus:ring-blue-300 focus:ring-opacity-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                Verificar Código
            </button>

            <!-- Información adicional -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-xs text-gray-600 text-center">
                    <strong>Nota:</strong> El código expira en 5 minutos. Si no lo recibiste, revisa tu carpeta de spam o solicita uno nuevo.
                </p>
            </div>
        </form>

        <!-- Enlace de Ayuda -->
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm font-medium text-uth-blue hover:text-blue-700 transition">
                ¿Problemas? Vuelve al login
            </a>
        </div>
    </div>

</body>
</html>
