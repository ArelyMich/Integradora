<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        /* Paleta de colores - Usuario */
        :root {
            --color-light: #85B093;      /* Verde claro */
            --color-accent: #568F7C;     /* Verde medio */
            --color-secondary: #326D6C;  /* Verde azulado oscuro */
            --color-primary: #173C4C;    /* Azul verdoso muy oscuro */
            --color-dark: #07142B;       /* Azul noche oscuro */
            --color-darker: #000009;     /* Casi negro azulado */
            --color-glow: rgba(133, 176, 147, 0.4);
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
            background: linear-gradient(135deg, #000009 0%, #07142B 30%, #173C4C 100%);
            position: relative;
            overflow: hidden;
        }
        
        .bg-gradient-primary::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(133, 176, 147, 0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .bg-gradient-secondary {
            background: linear-gradient(135deg, #326D6C 0%, #568F7C 50%, #85B093 100%);
        }
        
        .bg-gradient-light {
            background: linear-gradient(135deg, #568F7C 0%, #85B093 100%);
        }
        
        /* Efectos de vidrio mejorados */
        .glass-effect {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3),
                        0 0 0 1px rgba(255, 255, 255, 0.05) inset;
        }
        
        /* Campos de entrada mejorados */
        .input-field {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
            border: 1.5px solid rgba(255, 255, 255, 0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .input-field:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--color-light);
            box-shadow: 0 0 0 4px rgba(133, 176, 147, 0.15),
                        0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }
        
        .input-field:hover:not(:focus) {
            border-color: rgba(255, 255, 255, 0.3);
        }
        
        /* Botones mejorados */
        .btn-primary {
            background: linear-gradient(135deg, #568F7C 0%, #85B093 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(86, 143, 124, 0.3);
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(86, 143, 124, 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(86, 143, 124, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #326D6C 0%, #568F7C 100%);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px rgba(50, 109, 108, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(50, 109, 108, 0.4);
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
            border: 4px solid rgba(133, 176, 147, 0.3);
            border-top: 4px solid #85B093;
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
        }
        
        /* Sombras personalizadas */
        .shadow-premium {
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5),
                        0 0 0 1px rgba(255, 255, 255, 0.1) inset;
        }
        
        /* Texto con brillo */
        .text-glow {
            text-shadow: 0 0 20px rgba(133, 176, 147, 0.5);
        }
        
        /* Responsividad para modales */
        .modal-container {
            max-height: 90vh;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.3) transparent;
        }

        /* Webkit scrollbar styling */
        .modal-container::-webkit-scrollbar {
            width: 6px;
        }

        .modal-container::-webkit-scrollbar-track {
            background: transparent;
        }

        .modal-container::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 20px;
        }

        @media (max-width: 640px) {
            .modal-container {
                padding: 1.5rem;
                width: 95%;
                max-height: 85vh; /* Reduced to ensure button visibility with browser bars */
            }
            
            .modal-container h1,
            .modal-container h2 {
                font-size: 1.5rem;
            }
            
            .input-field {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }
            
            button[type="submit"] {
                padding: 0.875rem;
                font-size: 0.875rem;
            }
        }
        
        @media (min-width: 641px) and (max-width: 768px) {
            .modal-container {
                padding: 2rem;
                max-height: 85vh;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-primary flex items-center justify-center p-4">
    <!-- Contenedor principal -->
    <div class="glass-effect shadow-premium rounded-3xl p-10 w-full max-w-md text-white fade-in relative modal-container">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold mb-3 text-glow" style="letter-spacing: -0.5px;">Bienvenido</h1>
            <p class="text-white/80 text-lg font-light">Inicia sesión en tu cuenta</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-500/40 text-white p-4 rounded-lg mb-6 backdrop-blur-lg slide-up">
                <ul class="text-sm">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORMULARIO DE LOGIN --}}
        <form action="{{ route('login.process') }}" method="POST" class="space-y-6" id="loginForm">
            @csrf

            <div>
                <label class="block mb-3 font-semibold text-sm tracking-wide">Username o correo</label>
                <input type="text" name="username" required placeholder="Puedes utilizar tu username o tu correo"
                    class="w-full px-5 py-4 rounded-xl input-field text-white placeholder-white/50 focus:outline-none text-base">
            </div>

            <div>
                <label class="block mb-3 font-semibold text-sm tracking-wide">Contraseña</label>
                <div class="relative">
                    <input type="password" id="password_login" name="password" required placeholder="••••••••"
                        class="w-full px-5 py-4 pr-12 rounded-xl input-field text-white placeholder-white/50 focus:outline-none text-base">
                    <button 
                        type="button"
                        onclick="togglePassword('password_login', this)"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white transition-all hover:scale-110">
                        👁️
                    </button>
                </div>
                <p id="msgPassword" class="mt-2 text-sm text-red-400 hidden"></p>
            </div>

            <div class="flex items-center justify-between text-sm">
               
                <a href="javascript:void(0)" onclick="openResetModal()" class="text-highlight hover:text-light transition-all font-semibold hover-lift">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <button type="submit"
                class="w-full btn-primary text-dark font-bold py-4 rounded-xl transition-all text-base shadow-lg relative overflow-hidden">
                <span class="relative z-10">Iniciar sesión</span>
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
        <div class="bg-gradient-secondary rounded-2xl shadow-2xl w-full max-w-md p-8 relative fade-in text-white modal-container">
            {{-- BOTÓN CERRAR --}}
            <button onclick="closeModal()"
                class="absolute top-4 right-4 text-white/70 hover:text-white text-2xl transition-colors">&times;</button>

            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold">Crear cuenta</h2>
                <p class="text-white/70 mt-2">Completa el formulario para registrarte</p>
            </div>

            {{-- MENSAJE DE ÉXITO --}}
@if (session('status'))
    <div class="bg-green-600/80 text-white p-3 rounded-lg text-sm text-center">
        {{ session('status') }}
    </div>
@endif

{{-- ERRORES GENERALES --}}
@if ($errors->any())
    <div class="bg-red-600/80 text-white p-3 rounded-lg text-sm">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


            {{-- FORMULARIO DE REGISTRO --}}
            <form action="{{ route('register.process') }}" method="POST" class="space-y-5" id="registerForm">
                @csrf
                <input type="hidden" name="role_id" value="4">

                <div>
                    <label class="block mb-2 font-medium">Nombre</label>
                    <input type="text" name="name" required minlength="2" maxlength="255" pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$" placeholder="Tu nombre"
                        class="w-full px-4 py-3 rounded-lg input-field text-white placeholder-white/60 focus:outline-none">
                </div>
                
                <div>
                    <label class="block mb-2 font-medium">Apellido Paterno</label>
                    <input type="text" name="apellido_paterno" required minlength="2" maxlength="255" pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$" placeholder="Tu apellido paterno"
                        class="w-full px-4 py-3 rounded-lg input-field text-white placeholder-white/60 focus:outline-none">
                </div>
                
                <div>
                    <label class="block mb-2 font-medium">Apellido Materno</label>
                    <input type="text" name="apellido_materno" required minlength="2" maxlength="255" pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$" placeholder="Tu apellido materno"
                        class="w-full px-4 py-3 rounded-lg input-field text-white placeholder-white/60 focus:outline-none">
                </div>

                <div>
                    <label class="block mb-2 font-medium">Username</label>
                    <input type="text" name="username" required minlength="3" maxlength="30" pattern="^[A-Za-z0-9_.-]+$" placeholder="Escribe un username para que puedas ingresar."
                        class="w-full px-4 py-3 rounded-lg input-field text-white placeholder-white/60 focus:outline-none">
                </div>

                <p class="text-xs text-white/80">Tu cuenta se registrara como Docente.</p>


                <div>
                    <label class="block mb-2 font-medium">Email</label>
                    <input 
                        type="email" 
                        name="email" 
                        required 
                        pattern="^[\w\.-]+@[\w\.-]+\.edu\.mx$"
                        title="Solo correos que terminen en .edu.mx"
                        placeholder="usuario@escuela.edu.mx"
                        class="w-full px-4 py-3 rounded-lg input-field text-white placeholder-white/60 focus:outline-none"
                        oninput="validarEmailRegistro()">
                    <p id="msgEmailRegistro" class="mt-1 text-sm text-green-300 hidden"></p>
                </div>

                <div>
                    <label class="block mb-2 font-medium">Contraseña</label>
                    <div class="relative">
                        <input 
                            id="password_registro"
                            type="password" 
                            name="password" 
                            required
                            minlength="8"
                            pattern="^(?=.*[0-9])(?=.*[@$!%*#?&]).{8,}$"
                            class="w-full px-4 py-3 pr-12 rounded-lg input-field text-white placeholder-white/60 focus:outline-none"
                            oninput="validarPasswordRegistro()"
                            placeholder="••••••••">

                        <button 
                            type="button"
                            onclick="togglePassword('password_registro', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-white/70 hover:text-white">
                            👁️
                        </button>
                    </div>
                    
                    <div class="mt-2">
                        <div id="passwordStrength" class="password-strength password-weak"></div>
                    </div>
                    
                    <p id="msgPasswordRegistro" class="mt-1 text-sm text-green-300 hidden"></p>
                </div>

               <div>
                    <label class="block mb-2 font-medium">Confirmar contraseña</label>
                    <div class="relative">
                        <input 
                            id="password_confirmation"
                            type="password" 
                            name="password_confirmation" 
                            required
                            class="w-full px-4 py-3 pr-12 rounded-lg input-field text-white placeholder-white/60 focus:outline-none"
                            oninput="validarConfirmacionPassword()"
                            placeholder="••••••••">

                        <button 
                            type="button"
                            onclick="togglePassword('password_confirmation', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-white/70 hover:text-white">
                            👁️
                        </button>
                    </div>
                    <p id="msgPasswordConfirmation" class="mt-1 text-sm text-green-300 hidden"></p>
                </div>

                <button type="submit"
                    class="w-full btn-secondary text-white font-bold py-3 rounded-lg transition-all mt-2">
                    Registrarme
                </button>
            </form>
        </div>
    </div>
    
{{-- MODAL DE RESTABLECER CONTRASEÑA --}}
<div id="modalReset" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center p-4 z-50">
    <div class="bg-gradient-secondary rounded-3xl shadow-premium w-full max-w-md p-10 relative fade-in text-white modal-container">
        {{-- LOADING OVERLAY --}}
        <div id="loadingOverlay" class="loading-overlay hidden">
            <div class="loading-spinner"></div>
            <p class="text-white mt-4 font-semibold">Enviando código...</p>
            <p class="text-white/70 text-sm mt-1">Por favor espera</p>
        </div>
        
        {{-- BOTÓN CERRAR --}}
        <button onclick="closeResetModal()"
            class="absolute top-5 right-5 text-white/70 hover:text-white text-3xl transition-all hover:scale-110 z-20">&times;</button>

        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-glow">Restablecer Contraseña</h2>
            <p class="text-white/80 mt-3 font-light">Ingresa un correo válido y previamente registrado, el código y la nueva contraseña</p>
        </div>
<form id="sendCodeForm" action="{{ route('password.email') }}" method="POST" class="space-y-5">
    @csrf
    <div>
        <label for="email_send_code" class="block text-white mb-3 font-semibold text-sm tracking-wide">Correo electrónico</label>
        <input type="email" name="email" id="email_send_code"
               class="w-full px-5 py-4 rounded-xl input-field text-white placeholder-white/50 focus:outline-none text-base"
               placeholder="usuario@escuela.edu.mx" required>
    </div>

    <button type="submit" id="sendCodeBtn"
            class="w-full btn-primary text-dark font-bold py-4 rounded-xl transition-all shadow-lg">
        <span class="relative z-10">Enviar Código</span>
    </button>
</form>

        {{-- FORMULARIO DE RESET --}}
<form id="resetPasswordForm" action="{{ route('password.update.code') }}" method="POST" class="space-y-5 hidden">
    @csrf

    <div>
        <label for="email_reset" class="block text-white mb-3 font-semibold text-sm tracking-wide">Correo electrónico</label>
        <input type="email" name="email" id="email_reset"
               class="w-full px-5 py-4 rounded-xl input-field text-white placeholder-white/50 focus:outline-none text-base"
               placeholder="usuario@escuela.edu.mx" required>
    </div>

    <div>
        <label for="token_reset" class="block text-white mb-3 font-semibold text-sm tracking-wide">Código</label>
        <input type="text" name="token" id="token_reset"
               class="w-full px-5 py-4 rounded-xl input-field text-white placeholder-white/50 focus:outline-none text-base tracking-widest"
               placeholder="123456" required>
    </div>

    <div>
        <label for="password_reset" class="block text-white mb-3 font-semibold text-sm tracking-wide">Nueva Contraseña</label>
        <div class="relative">
            <input type="password" name="password" id="password_reset"
                   class="w-full px-5 py-4 pr-14 rounded-xl input-field text-white placeholder-white/50 focus:outline-none text-base"
                   placeholder="••••••••" required>
            <button type="button" onclick="togglePassword('password_reset', this)"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white transition-all hover:scale-110">👁️
            </button>
        </div>
    </div>

    <div>
        <label for="password_confirmation_reset" class="block text-white mb-3 font-semibold text-sm tracking-wide">Confirmar Contraseña</label>
        <div class="relative">
            <input type="password" name="password_confirmation" id="password_confirmation_reset"
                   class="w-full px-5 py-4 pr-14 rounded-xl input-field text-white placeholder-white/50 focus:outline-none text-base"
                   placeholder="••••••••" required>
            <button type="button" onclick="togglePassword('password_confirmation_reset', this)"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white transition-all hover:scale-110">
                👁️
            </button>
        </div>
    </div>

    <button type="submit"
            class="w-full btn-primary text-dark font-bold py-4 rounded-xl transition-all shadow-lg">
        <span class="relative z-10">Cambiar Contraseña</span>
    </button>
</form>

    </div>
</div>

    <script>
// ------------------ MODALES ------------------

// Abrir modal de registro
function openModal() {
    const modal = document.getElementById('modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

// Cerrar modal de registro
function closeModal() {
    const modal = document.getElementById('modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
}

// Abrir modal de reset
function openResetModal() {
    const modal = document.getElementById('modalReset');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

// Cerrar modal de reset
function closeResetModal() {
    const modal = document.getElementById('modalReset');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = 'auto';
}

// Cerrar modales al hacer clic fuera del contenido
['modal', 'modalReset'].forEach(id => {
    const modal = document.getElementById(id);
    modal.addEventListener('click', e => {
        if (e.target === modal) {
            id === 'modal' ? closeModal() : closeResetModal();
        }
    });
});

// Cerrar modales con Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        const modalReg = document.getElementById('modal');
        const modalReset = document.getElementById('modalReset');
        if (!modalReg.classList.contains('hidden')) closeModal();
        if (!modalReset.classList.contains('hidden')) closeResetModal();
    }
});

// ------------------ ENVIAR CÓDIGO ------------------
const sendCodeForm = document.getElementById('sendCodeForm');
const resetForm = document.getElementById('resetPasswordForm');
const emailInput = document.getElementById('email_send_code');
const emailReset = document.getElementById('email_reset');
const loadingOverlay = document.getElementById('loadingOverlay');
const sendCodeBtn = document.getElementById('sendCodeBtn');

sendCodeForm.addEventListener('submit', function(e){
    e.preventDefault();
    
    // Mostrar overlay de carga y deshabilitar botón
    loadingOverlay.classList.remove('hidden');
    sendCodeBtn.disabled = true;
    
    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('#sendCodeForm input[name="_token"]').value,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: emailInput.value })
    })
    .then(res => res.json())
    .then(data => {
        // Ocultar overlay de carga
        loadingOverlay.classList.add('hidden');
        sendCodeBtn.disabled = false;
        
        if (data.status) {
            emailReset.value = emailInput.value;

            // Mostrar mensaje
            let msg = document.getElementById('msgSendCode');
            if (!msg) {
                msg = document.createElement('p');
                msg.id = 'msgSendCode';
                msg.className = 'mt-3 text-green-300 text-center font-semibold text-base';
                sendCodeForm.appendChild(msg);
            }
            msg.textContent = '✓ Código enviado. Revisa tu bandeja de entrada.';

            // Mostrar formulario de reset después de 1.5s
            setTimeout(() => {
                sendCodeForm.classList.add('hidden');
                resetForm.classList.remove('hidden');
            }, 1500);
        } else {
            // Mostrar error
            let msg = document.getElementById('msgSendCode');
            if (!msg) {
                msg = document.createElement('p');
                msg.id = 'msgSendCode';
                msg.className = 'mt-3 text-red-300 text-center font-semibold text-base';
                sendCodeForm.appendChild(msg);
            }
            msg.textContent = '✗ Error al enviar código. Verifica tu correo.';
        }
    })
    .catch(err => {
        // Ocultar overlay y mostrar error
        loadingOverlay.classList.add('hidden');
        sendCodeBtn.disabled = false;
        console.error(err);
        
        let msg = document.getElementById('msgSendCode');
        if (!msg) {
            msg = document.createElement('p');
            msg.id = 'msgSendCode';
            msg.className = 'mt-3 text-red-300 text-center font-semibold text-base';
            sendCodeForm.appendChild(msg);
        }
        msg.textContent = '✗ Error de conexión. Intenta nuevamente.';
    });
});

// ------------------ RESET CONTRASEÑA ------------------
const passwordInput = document.getElementById('password_reset');
const confirmPasswordInput = document.getElementById('password_confirmation_reset');

// Mostrar/ocultar contraseña
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = '🙈';
    } else {
        input.type = 'password';
        button.textContent = '👁️';
    }
}


// Validación de contraseña
function validarPasswordReset() {
    const pattern = /^(?=.*[0-9])(?=.*[@$!%*#?&]).{8,}$/;
    let msg = document.getElementById('msgPasswordReset');
    if (!msg) {
        msg = document.createElement('p');
        msg.id = 'msgPasswordReset';
        msg.className = 'mt-1 text-sm text-red-400';
        passwordInput.parentNode.appendChild(msg);
    }
    if (!pattern.test(passwordInput.value)) {
        msg.textContent = 'Debe tener al menos 8 caracteres, un número y un carácter especial (@$!%*#?&).';
        msg.classList.remove('hidden');
    } else {
        msg.classList.add('hidden');
    }
}

// Validación confirmación
function validarConfirmacionReset() {
    let msg = document.getElementById('msgConfirmReset');
    if (!msg) {
        msg = document.createElement('p');
        msg.id = 'msgConfirmReset';
        msg.className = 'mt-1 text-sm text-red-400';
        confirmPasswordInput.parentNode.appendChild(msg);
    }
    if (passwordInput.value !== confirmPasswordInput.value) {
        msg.textContent = 'Las contraseñas no coinciden.';
        msg.classList.remove('hidden');
    } else {
        msg.classList.add('hidden');
    }
}

    passwordInput.addEventListener('input', validarPasswordReset);
    confirmPasswordInput.addEventListener('input', validarConfirmacionReset);

  
// ------------------ VALIDACIONES REGISTRO ------------------
function validarEmailRegistro() {
    const email = document.querySelector('#registerForm input[name="email"]');
    const msg = document.getElementById('msgEmailRegistro');
    const pattern = /^[\w\.-]+@[\w\.-]+\.edu\.mx$/;
    if (email.value.length === 0 || pattern.test(email.value)) {
        msg.classList.add('hidden');
    } else {
        msg.textContent = 'Solo correos que terminen en .edu.mx';
        msg.classList.remove('hidden');
    }
}

function validarPasswordRegistro() {
    const password = document.getElementById('password_registro');
    const msg = document.getElementById('msgPasswordRegistro');
    const pattern = /^(?=.*[0-9])(?=.*[@$!%*#?&]).{8,}$/;
    if (password.value.length === 0 || pattern.test(password.value)) {
        msg.classList.add('hidden');
    } else {
        msg.textContent = 'Debe tener al menos 8 caracteres, un número y un carácter especial (@$!%*#?&).';
        msg.classList.remove('hidden');
    }
}

function validarConfirmacionPassword() {
    const password = document.getElementById('password_registro');
    const confirmPassword = document.getElementById('password_confirmation');
    const msg = document.getElementById('msgPasswordConfirmation');
    if (confirmPassword.value.length === 0 || password.value === confirmPassword.value) {
        msg.classList.add('hidden');
    } else {
        msg.textContent = 'Las contraseñas no coinciden.';
        msg.classList.remove('hidden');
    }
}

// Prevenir envío si hay errores
document.getElementById('registerForm').addEventListener('submit', function(e) {
    validarEmailRegistro();
    validarPasswordRegistro();
    validarConfirmacionPassword();

    const emailMsg = document.getElementById('msgEmailRegistro');
    const passMsg = document.getElementById('msgPasswordRegistro');
    const confMsg = document.getElementById('msgPasswordConfirmation');

    if (!emailMsg.classList.contains('hidden') || !passMsg.classList.contains('hidden') || !confMsg.classList.contains('hidden')) {
        e.preventDefault();
    }
});
document.getElementById('resetPasswordForm').addEventListener('submit', function(e){
    // Validar antes de enviar
    validarPasswordReset();
    validarConfirmacionReset();

    const msgPassword = document.getElementById('msgPasswordReset');
    const msgConfirm = document.getElementById('msgConfirmReset');

    // Solo bloquear envío si hay errores
    if (!msgPassword.classList.contains('hidden') || !msgConfirm.classList.contains('hidden')) {
        e.preventDefault(); // Detener envío si hay errores
        return;
    }

    // Aquí puedes mostrar mensaje de éxito después de recibir respuesta del servidor
});

</script>

    
</body>
</html>