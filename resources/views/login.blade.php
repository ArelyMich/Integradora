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
            --color-light: #E5E7EB;      /* Gris claro */
            --color-accent: #2FA69A;     /* Verde primario */
            --color-secondary: #23877E;  /* Verde hover */
            --color-primary: #6B7280;    /* Gris medio */
            --color-dark: #374151;       /* Gris oscuro */
            --color-darker: #1F2937;     /* Gris más oscuro */
            --color-glow: rgba(47, 166, 154, 0.4);
        }
        
        /* Animaciones mejoradas */
        .fade-in {
            animation: fadeIn 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: scale(0.9) translateY(20px);
            }
            to { 
                opacity: 1; 
                transform: scale(1) translateY(0);
            }
        }
        
        .slide-up {
            animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Animación de pulsación para el botón */
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(133, 176, 147, 0.2); }
            50% { box-shadow: 0 0 30px rgba(133, 176, 147, 0.4); }
        }
        
        /* Gradientes mejorados */
        .bg-gradient-primary {
            background: linear-gradient(135deg, #1F2937 0%, #374151 30%, #6B7280 100%);
            position: relative;
            overflow: hidden;
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
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(47, 166, 154, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        .bg-orbit::before {
            width: 24rem;
            height: 24rem;
            top: -5rem;
            right: -4rem;
            background: radial-gradient(circle, rgba(133, 176, 147, 0.75) 0%, transparent 70%);
        }
        
        .bg-gradient-secondary {
            background: linear-gradient(135deg, #23877E 0%, #2FA69A 50%, #E5E7EB 100%);
        }
        
        .bg-gradient-light {
            background: linear-gradient(135deg, #2FA69A 0%, #E5E7EB 100%);
        }
        
        /* Efectos de vidrio mejorados */
        .glass-effect {
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
            background: linear-gradient(135deg, #2FA69A 0%, #23877E 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(47, 166, 154, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(47, 166, 154, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(47, 166, 154, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #23877E 0%, #2FA69A 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(35, 135, 126, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(35, 135, 126, 0.4);
        }
        
        .btn-secondary:active {
            transform: translateY(-1px);
        }
        
        /* Indicador de fuerza de contraseña */
        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        
        .password-weak {
            background: linear-gradient(90deg, #ef4444, #dc2626);
            width: 25%;
        }
        
        .password-medium {
            background: linear-gradient(90deg, #f59e0b, #d97706);
            width: 50%;
        }
        
        .password-strong {
            background: linear-gradient(90deg, #10b981, #059669);
            width: 100%;
        }
        
        /* Spinner de carga */
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid #ffffff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 0.8s linear infinite;
            display: inline-block;
            margin-right: 8px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Overlay de carga */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            z-index: 10;
        }
        
        .loading-spinner {
            border: 4px solid rgba(47, 166, 154, 0.3);
            border-top: 4px solid #2FA69A;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        
        /* Efectos hover mejorados */
        .hover-lift {
            transition: transform 0.3s ease;
        }
        
        .hover-lift:hover {
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

        <div class="mt-8 pt-6 border-t border-white/20">
            <p class="text-center text-white/80">
                ¿No tienes cuenta?
                <button onclick="openModal()" class="text-highlight font-semibold hover:text-light transition-colors ml-1">
                    Crear una aquí
                </button>
            </p>
        </div>
    </div>
{{--  AQUI SE INYECTA EL MODAL DE VERIFICACIÓN --}}
<x-verify-email-modal :show="session('showVerifyModal')" />
    {{-- ====================================================
                        MODAL DE REGISTRO
    ==================================================== --}}
    <div id="modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
        <div class="bg-gradient-secondary rounded-2xl shadow-lg w-full max-w-md p-8 relative fade-in text-white modal-container">
            {{-- BOTÓN CERRAR --}}
            <button onclick="closeModal()"
                class="absolute top-4 right-4 text-white/70 hover:text-white text-2xl transition-colors">&times;</button>

            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold">Crear cuenta</h2>
                <p class="text-white/70 mt-2">Completa el formulario para registrarte</p>
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
        const shouldOpenRecovery = @json($errors->has('email') || !is_null(session('success')) || !is_null(session('recovery_email')));
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
