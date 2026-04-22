<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        :root {
            --color-light: #85B093;
            --color-accent: #568F7C;
            --color-secondary: #326D6C;
            --color-primary: #173C4C;
            --color-dark: #07142B;
            --color-darker: #000009;
        }

        body {
            background: linear-gradient(135deg, #000009 0%, #07142B 30%, #173C4C 100%);
            min-height: 100vh;
        }

        .bg-orbit {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .bg-orbit::before,
        .bg-orbit::after {
            content: '';
            position: absolute;
            border-radius: 9999px;
            filter: blur(14px);
            opacity: 0.35;
        }

        .bg-orbit::before {
            width: 24rem;
            height: 24rem;
            top: -5rem;
            right: -4rem;
            background: radial-gradient(circle, rgba(133, 176, 147, 0.75) 0%, transparent 70%);
        }

        .bg-orbit::after {
            width: 20rem;
            height: 20rem;
            bottom: -4rem;
            left: -3rem;
            background: radial-gradient(circle, rgba(86, 143, 124, 0.55) 0%, transparent 70%);
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px) saturate(160%);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.45);
        }

        .input-field {
            background: rgba(255, 255, 255, 0.08);
            border: 1.5px solid rgba(255, 255, 255, 0.14);
            transition: all 0.25s ease;
        }

        .input-field:focus {
            outline: none;
            border-color: var(--color-light);
            box-shadow: 0 0 0 4px rgba(133, 176, 147, 0.14);
            background: rgba(255, 255, 255, 0.12);
        }

        .btn-primary {
            background: linear-gradient(135deg, #568F7C 0%, #85B093 100%);
            color: var(--color-darker);
            box-shadow: 0 12px 24px rgba(86, 143, 124, 0.28);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 28px rgba(86, 143, 124, 0.36);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #326D6C 0%, #568F7C 100%);
            color: white;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 24px rgba(50, 109, 108, 0.32);
        }

        .recovery-panel {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), rgba(133, 176, 147, 0.1));
            border: 1px solid rgba(133, 176, 147, 0.22);
        }
    </style>
</head>

<body class="flex items-center justify-center p-4 text-white">
    <div class="bg-orbit"></div>

    <main class="glass-panel relative z-10 w-full max-w-md rounded-[2rem] p-6 sm:p-10">
        <div class="text-center mb-8">
            <p class="text-sm uppercase tracking-[0.35em] text-white/55">UTH</p>
            <h1 class="mt-3 text-4xl font-bold">Bienvenido</h1>
            <p class="mt-2 text-white/75">Inicia sesión en tu cuenta</p>
        </div>

        @if (session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-300/30 bg-emerald-500/20 px-4 py-3 text-sm text-emerald-50">
            {{ session('status') }}
        </div>
        @endif

        @if ($errors->any())
        <div class="mb-5 rounded-2xl border border-red-300/30 bg-red-500/25 px-4 py-3 text-sm">
            <ul class="space-y-1">
                @foreach ($errors->all() as $error)
                <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="username" class="mb-2 block text-sm font-semibold tracking-wide">Username o correo</label>
                <input
                    id="username"
                    type="text"
                    name="username"
                    required
                    value="{{ old('username') }}"
                    placeholder="Puedes utilizar tu username o tu correo"
                    class="input-field w-full rounded-2xl px-5 py-4 text-base text-white placeholder-white/45">
            </div>

            <div>
                <label for="password_login" class="mb-2 block text-sm font-semibold tracking-wide">Contraseña</label>
                <div class="relative">
                    <input
                        id="password_login"
                        type="password"
                        name="password"
                        required
                        placeholder="••••••••"
                        class="input-field w-full rounded-2xl px-5 py-4 pr-14 text-base text-white placeholder-white/45">
                    <button
                        type="button"
                        onclick="togglePassword('password_login', this)"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-xl text-white/70 transition hover:text-white">
                        👁️
                    </button>
                </div>
            </div>

            <div>
                <button
                    type="button"
                    id="toggleRecovery"
                    class="text-sm font-semibold text-[#d9ffe1] transition hover:text-white">
                    ¿Olvidaste tu contraseña?
                </button>
            </div>

            <div class="rounded-2xl bg-white/8 p-3">
                <div class="flex justify-center overflow-x-auto">
                    <div class="g-recaptcha" data-sitekey="{{ $recaptcha_key }}"></div>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full rounded-2xl py-4 text-base font-bold">
                Iniciar sesión
            </button>
        </form>

        <section id="recoveryPanel" class="recovery-panel mt-5 hidden rounded-[1.6rem] p-5">
            <div class="mb-4">
                <h2 class="text-lg font-semibold">Recuperar acceso</h2>
                <p class="mt-1 text-sm text-white/72">
                    Escribe tu correo institucional y te enviaremos un código para continuar con el cambio de contraseña.
                </p>
            </div>

            @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-300/30 bg-emerald-500/20 px-4 py-3 text-sm text-emerald-50">
                {{ session('success') }}
            </div>
            @endif

            <form action="{{ route('password.recovery.send') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="role_id" value="4">

                <div>
                    <label for="recovery_email" class="mb-2 block text-sm font-semibold tracking-wide">Correo electrónico</label>
                    <input
                        id="recovery_email"
                        type="email"
                        name="email"
                        required
                        value="{{ old('email', session('recovery_email')) }}"
                        placeholder="usuario@uth.edu.mx"
                        class="input-field w-full rounded-2xl px-5 py-4 text-base text-white placeholder-white/45">
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <button type="submit" class="btn-secondary rounded-2xl px-6 py-3 font-bold">
                        Enviar código
                    </button>
                    <a href="{{ route('password.recovery.email') }}" class="text-sm text-white/78 transition hover:text-white">
                        Abrir flujo completo
                    </a>
                </div>
            </form>
        </section>
    </main>

    <x-verify-email-modal :show="session('showVerifyModal')" />

    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);

            if (!input) {
                return;
            }

            if (input.type === 'password') {
                input.type = 'text';
                button.textContent = '🙈';
            } else {
                input.type = 'password';
                button.textContent = '👁️';
            }
        }

        const toggleRecovery = document.getElementById('toggleRecovery');
        const recoveryPanel = document.getElementById('recoveryPanel');
const shouldOpenRecovery = @json($errors->has('email') || session('success') || session('recovery_email'));
        if (toggleRecovery && recoveryPanel) {
            toggleRecovery.addEventListener('click', () => {
                recoveryPanel.classList.toggle('hidden');
            });

            if (shouldOpenRecovery) {
                recoveryPanel.classList.remove('hidden');
            }
        }
    </script>
</body>

</html>