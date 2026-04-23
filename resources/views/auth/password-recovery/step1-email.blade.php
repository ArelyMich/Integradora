<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña | UTH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        body {

            font-family: 'Inter', sans-serif;

            background:

                radial-gradient(circle at 15% 20%,
                    rgba(86, 143, 124, 0.18),
                    transparent 40%),

                radial-gradient(circle at 85% 80%,
                    rgba(50, 109, 108, 0.18),
                    transparent 45%),

                linear-gradient(145deg,
                    #f8fafc,
                    #eef2f6,
                    #e2e8f0);

            min-height: 100vh;

        }

        .uth-primary {
            color: #173C4C;
        }

        .uth-button {
            background: linear-gradient(135deg, #326D6C 0%, #568F7C 100%);
        }
    </style>
</head>

<body class="min-h-screen px-4 py-8 text-slate-800">
    <main class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-5xl items-center gap-8 lg:grid-cols-[1.05fr_0.95fr]">
        <section
            class="rounded-[2rem] bg-gradient-to-br from-[#173C4C] to-[#326D6C] p-8 text-white shadow-[0_20px_60px_rgba(0,0,0,0.35)] sm:p-10">
            <p class="text-sm uppercase tracking-[0.35em] text-white/55">Password Recovery</p>
            <h1 class="mt-4 text-4xl font-bold leading-tight">Recupera tu cuenta sin que el formulario se encime.</h1>
            <p class="mt-4 max-w-xl text-sm leading-6 text-white/75 sm:text-base">
                Ingresa tu correo institucional para enviarte un código de 6 dígitos. El proceso toma tres pasos y el
                código tendrá una vigencia de 30 minutos.
            </p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.25em] text-white/50">Paso 1</p>
                    <p class="mt-2 font-semibold">Correo</p>
                    <p class="mt-1 text-sm text-white/70">Validamos que la cuenta exista.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.25em] text-white/40">Paso 2</p>
                    <p class="mt-2 font-semibold">Código</p>
                    <p class="mt-1 text-sm text-white/65">Confirmas el acceso con 6 dígitos.</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-4">
                    <p class="text-xs uppercase tracking-[0.25em] text-white/40">Paso 3</p>
                    <p class="mt-2 font-semibold">Nueva contraseña</p>
                    <p class="mt-1 text-sm text-white/65">Creas una contraseña segura.</p>
                </div>
            </div>
        </section>

        <section
            class="rounded-[2rem] 

bg-white
p-8 

shadow-[0_20px_60px_rgba(0,0,0,0.08)]

border border-slate-200

sm:p-10">
            <div class="mb-8">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Paso 1 de 3</p>
                <h2 class="uth-primary mt-3 text-3xl font-bold">Enviar código de recuperación</h2>
                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Te enviaremos el código al correo asociado a tu cuenta.
                </p>
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
                <div
                    class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.recovery.send') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Correo
                        institucional</label>
                        
                    <input id="email" type="email" name="email" required autofocus
                        value="{{ old('email', session('recovery_email')) }}" placeholder="usuario@uth.edu.mx"
                        class="w-full 

rounded-2xl
border border-slate-300
px-4 py-3
text-slate-800
transition
focus:border-[#568F7C]
focus:ring-4 
focus:ring-[#85B093]/25
shadow-sm">
                </div>

                <div class="rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-600">
                    Usa el correo que registraste en el sistema. Si el código vence, podrás solicitar uno nuevo.
                </div>

                <button type="submit"
                    class="w-full 

rounded-2xl

px-6 py-3

font-bold text-white

bg-gradient-to-r
from-[#326D6C]
to-[#568F7C]

shadow-[0_10px_25px_rgba(50,109,108,0.35)]

transition

hover:-translate-y-0.5
hover:shadow-[0_14px_30px_rgba(50,109,108,0.45)]">
                    Enviar código
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}"
                    class="text-sm font-semibold text-[#326D6C] transition hover:text-[#173C4C]">
                    Volver al login
                </a>
            </div>
        </section>
    </main>
</body>

</html>
