<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar código | UTH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(50, 109, 108, 0.12), transparent 25%),
                linear-gradient(135deg, #edf4f8 0%, #e6eef4 45%, #f5f8fb 100%);
        }
    </style>
</head>
<body class="min-h-screen px-4 py-8 text-slate-800">
    <main class="mx-auto max-w-3xl">
        <section class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200 sm:p-10" x-data="{ code: '' }">
            <div class="mb-8 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Paso 2 de 3</p>
                    <h1 class="mt-3 text-3xl font-bold text-[#173C4C]">Verifica el código</h1>
                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        Revisa tu correo e ingresa el código de 6 dígitos para continuar.
                    </p>
                </div>

                <div class="flex items-center gap-3 text-sm font-semibold">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500 text-white">1</span>
                    <span class="h-1 w-10 rounded-full bg-emerald-500"></span>
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#326D6C] text-white">2</span>
                    <span class="h-1 w-10 rounded-full bg-slate-200"></span>
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-slate-500">3</span>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.recovery.verify.post') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="code" class="mb-2 block text-sm font-semibold text-slate-700">Código de 6 dígitos</label>
                    <input
                        id="code"
                        type="text"
                        name="code"
                        required
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        x-model="code"
                        @input="code = $event.target.value.replace(/[^0-9]/g, '').slice(0, 6)"
                        placeholder="000000"
                        class="w-full rounded-2xl border border-slate-300 px-4 py-4 text-center text-3xl font-bold tracking-[0.5em] text-slate-800 outline-none transition focus:border-[#568F7C] focus:ring-4 focus:ring-[#85B093]/20">
                    <p class="mt-3 text-sm text-slate-500">El código expira en 30 minutos.</p>
                </div>

                <button
                    type="submit"
                    :disabled="code.length !== 6"
                    class="w-full rounded-2xl bg-[#326D6C] px-6 py-3 font-bold text-white shadow-lg transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 disabled:hover:translate-y-0">
                    Verificar código
                </button>
            </form>

            <div class="mt-6 flex flex-col gap-3 text-center sm:flex-row sm:justify-between">
                <a href="{{ route('password.recovery.email') }}" class="text-sm font-semibold text-[#326D6C] transition hover:text-[#173C4C]">
                    Usar otro correo
                </a>
                <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-500 transition hover:text-slate-700">
                    Volver al login
                </a>
            </div>
        </section>
    </main>
</body>
</html>
