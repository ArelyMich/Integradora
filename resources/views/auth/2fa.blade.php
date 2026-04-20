<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación 2FA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>
    body {
        background-image: url('/images/2fa-background.jpg');
        background-size: cover;
        background-position: center;
        
    }

</style>

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
        @endif
    </form>

</body>
</html>
