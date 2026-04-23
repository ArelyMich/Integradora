<html lang="es">

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://www.google.com/recaptcha/api.js" async></script>
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

            background:

                radial-gradient(circle at 15% 20%,
                    rgba(20, 184, 166, 0.18),
                    transparent 40%),

                radial-gradient(circle at 85% 80%,
                    rgba(15, 118, 110, 0.25),
                    transparent 45%),

                linear-gradient(145deg,
                    #020617,
                    #111827,
                    #0F766E);

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

        .form-modern {

            display: flex;
            flex-direction: column;
            gap: 14px;

            background-color: #111827;
            /* 🔥 tarjeta oscura */

            padding: 2.5em;

            border-radius: 25px;

            transition: .4s ease-in-out;

            box-shadow:
                0 20px 50px rgba(0, 0, 0, 0.6);

            border: 1px solid rgba(20, 184, 166, 0.25);
        }

        /* Hover elegante */

        .form-modern:hover {

            transform:
                translateY(-4px);

            box-shadow:
                0 30px 70px rgba(0, 0, 0, 0.8);

        }

        /* TITULO */

        .heading-modern {

            color: white;
            /* 🔥 ahora visible */

            padding-bottom: 1.5em;

            text-align: center;

            font-weight: bold;

            font-size: 26px;

        }

        /* INPUT */

        .input-modern {

            border-radius: 12px;

            border: 1px solid rgba(255, 255, 255, 0.15);

            background-color: #020617;

            color: white;

            outline: none;

            padding: 12px;

            transition: .3s;

            font-size: 14px;

        }

        /* Hover */

        .input-modern:hover {

            border-color: #14B8A6;

        }

        /* Focus */

        .input-modern:focus {

            border-color: #14B8A6;

            box-shadow:
                0 0 0 2px rgba(20, 184, 166, 0.3);

        }

        /* BOTÓN */

        .btn-modern {

            width: 100%;

            margin-top: 1.5em;

            padding: 14px;

            border-radius: 12px;

            border: none;

            color: white;

            font-weight: bold;

            background:

                linear-gradient(135deg,
                    #0F766E,
                    #14B8A6);

            transition: .3s;

        }

        /* Hover */

        .btn-modern:hover {

            transform: translateY(-2px);

            box-shadow:

                0 10px 30px rgba(20, 184, 166, 0.5);

        }

        .btn-modern:active {
            transform:
                translateX(0) translateY(0);
            box-shadow: none;

        }

        /* LINK RECUPERAR */

        .link-modern {
            color: #14B8A6;
            font-weight: 500;

        }

        .link-modern:hover {
            color: #5EEAD4;

        }

        #recoveryModal {

            transition: opacity 0.25s ease;

        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center px-4 text-white">
    <div class="bg-orbit"></div>

    <main class="relative z-10 w-full max-w-sm mx-auto">

        <div class="form-modern">

            <div class="heading-modern">
                <p class="text-sm uppercase tracking-[0.35em] text-white/55">UTH</p>
                <h1 class="mt-3 text-4xl font-bold">Bienvenido</h1>
                <p class="text-sm text-gray-500">Inicia sesión en tu cuenta</p>
            </div>

            @if (session('status'))
                <div
                    class="mb-5 rounded-2xl border border-emerald-300/30 bg-emerald-500/20 px-4 py-3 text-sm text-emerald-50">
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

            <form action="{{ route('login.process') }}" method="POST" class="space-y-4">
                @csrf


                <label for="username" class="mb-2 block text-sm font-semibold tracking-wide">Username o
                    correo</label>

                <div class="relative">

                    <i class="fa-solid fa-user
absolute left-4 top-1/2
-translate-y-1/2
text-[#14B8A6] text-sm"></i>

                    <input id="username" type="text" name="username" required value="{{ old('username') }}"
                        placeholder="Usuario o correo"
                        class="input-modern
w-full
pl-12
pr-4
py-3
text-white
placeholder-white/40
rounded-xl">

                </div>

                <label for="password_login" class="mb-2 block text-sm font-semibold tracking-wide">Contraseña</label>

                <div class="relative">

                    <i class="fa-solid fa-lock
absolute left-4 top-1/2
-translate-y-1/2
text-[#14B8A6] text-sm"></i>

                    <input id="password_login" type="password" name="password" required placeholder="••••••••"
                        class="input-modern
w-full
pl-12
pr-12
py-3
text-white
placeholder-white/40
rounded-xl">

                    <button type="button" onclick="togglePassword('password_login', this)"
                        class="absolute right-4
top-1/2
-translate-y-1/2
text-[#14B8A6]">

                        <i class="fa-solid fa-eye"></i>

                    </button>

                </div>

                <div>
                    <button type="button" id="openRecovery" class="link-modern">

                        ¿Olvidaste tu contraseña?

                    </button>
                </div>

                <div class="rounded-2xl bg-white/8 p-3">
                    <div class="flex justify-center overflow-x-auto">
                        <div class="g-recaptcha" data-sitekey="{{ $recaptcha_key }}">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-modern w-full rounded-2xl py-4 text-base font-bold">
                    Iniciar sesión
                </button>
            </form>



    </main>


    <!-- MODAL FUERA DEL FORMULARIO -->

    <div id="recoveryModal"
        class="fixed inset-0 hidden
flex items-center justify-center
bg-black/70 backdrop-blur-md
z-50 p-4">

        <div
            class="bg-gradient-to-br 
from-[#020617] 
to-[#111827]
w-full max-w-sm
p-6
rounded-2xl
border border-[#14B8A6]/30
shadow-[0_20px_60px_rgba(0,0,0,0.7)]
relative">

            <button id="closeRecovery"
                class="absolute top-3 right-4
        text-gray-400 hover:text-white
        text-xl">

                ✕

            </button>

            <h2 class="text-lg font-bold text-white mb-2">
                Recuperar acceso
            </h2>

            <p class="text-sm text-gray-400 mb-4">
                Escribe tu correo institucional
                y te enviaremos un código.
            </p>

            <form action="{{ route('password.recovery.send') }}" method="POST" class="space-y-4">

                @csrf

                <input type="hidden" name="role_id" value="4">

                <div class="relative">

                    <i
                        class="fa-solid fa-envelope
                absolute left-4 top-1/2
                -translate-y-1/2
                text-[#14B8A6]"></i>

                    <input id="recovery_email" type="email" name="email" required placeholder="usuario@uth.edu.mx"
                        class="input-modern w-full pl-12 pr-4 py-3  text-white placeholder-white/40">

                </div>

                <button type="submit" class="btn-modern
w-full
flex items-center
justify-center
gap-2">

                    <i class="fa-solid fa-paper-plane"></i>

                    Enviar código

                </button>

                <a href="{{ route('password.recovery.email') }}"
                    class="flex items-center gap-2
text-sm
text-[#14B8A6]
hover:text-[#5EEAD4]
font-medium
transition">

                    <i class="fa-solid fa-arrow-right"></i>

                    Flujo completo

                </a>
            </form>

        </div>

    </div>

    <x-verify-email-modal :show="session('showVerifyModal')" />

    <script>
        function togglePassword(inputId, button) {

            const input = document.getElementById(inputId);

            if (input.type === 'password') {

                input.type = 'text';
                button.textContent = '🔒';

            } else {

                input.type = 'password';
                button.textContent = '🔓';

            }

        }

        // MODAL RECOVERY

        const openBtn =
            document.getElementById("openRecovery");

        const modal =
            document.getElementById("recoveryModal");

        const closeBtn =
            document.getElementById("closeRecovery");

        openBtn.addEventListener("click", () => {

            modal.classList.remove("hidden");

            modal.classList.add("flex");

        });

        closeBtn.addEventListener("click", () => {

            modal.classList.add("hidden");

            modal.classList.remove("flex");

        });

        modal.addEventListener("click", (e) => {

            if (e.target === modal) {

                modal.classList.add("hidden");

                modal.classList.remove("flex");

            }

        });
    </script>

</body>

</html>
