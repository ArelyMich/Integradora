<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña | UTH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background:
                radial-gradient(circle at right top, rgba(86, 143, 124, 0.14), transparent 25%),
                linear-gradient(135deg, #edf4f8 0%, #e6eef4 45%, #f5f8fb 100%);
        }
    </style>
</head>
<body class="min-h-screen px-4 py-8 text-slate-800">
    <main class="mx-auto max-w-4xl">
        <section class="rounded-[2rem] bg-white p-8 shadow-xl ring-1 ring-slate-200 sm:p-10" x-data="passwordRecoveryForm()">
            <div class="mb-8 flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Paso 3 de 3</p>
                    <h1 class="mt-3 text-3xl font-bold text-[#173C4C]">Crea una nueva contraseña</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500">
                        Evita reutilizar contraseñas anteriores. La nueva contraseña debe incluir mayúscula, minúscula, número y carácter especial.
                    </p>
                </div>

                <div class="flex items-center gap-3 text-sm font-semibold">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500 text-white">1</span>
                    <span class="h-1 w-10 rounded-full bg-emerald-500"></span>
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500 text-white">2</span>
                    <span class="h-1 w-10 rounded-full bg-emerald-500"></span>
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#326D6C] text-white">3</span>
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

            <form method="POST" action="{{ route('password.recovery.update') }}" class="grid gap-8 lg:grid-cols-[1.05fr_0.95fr]">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Nueva contraseña</label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                x-model="password"
                                @input="validatePasswordRules()"
                                required
                                placeholder="••••••••"
                                class="w-full rounded-2xl border border-slate-300 px-4 py-3 pr-14 text-slate-800 outline-none transition focus:border-[#568F7C] focus:ring-4 focus:ring-[#85B093]/20">
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-xl text-slate-400 transition hover:text-slate-700">
                                <span x-text="showPassword ? '🙈' : '👁️'"></span>
                            </button>
                        </div>

                        <div class="mt-4 overflow-hidden rounded-full bg-slate-200">
                            <div
                                class="h-2 transition-all duration-300"
                                :class="strengthClass"
                                :style="`width: ${strengthWidth}`">
                            </div>
                        </div>
                        <p class="mt-2 text-sm font-semibold" :class="strengthTextClass" x-text="strengthLabel"></p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Confirmar contraseña</label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                name="password_confirmation"
                                x-model="passwordConfirm"
                                @input="validatePasswordRules()"
                                required
                                placeholder="••••••••"
                                class="w-full rounded-2xl border border-slate-300 px-4 py-3 pr-14 text-slate-800 outline-none transition focus:border-[#568F7C] focus:ring-4 focus:ring-[#85B093]/20">
                            <button
                                type="button"
                                @click="showPasswordConfirm = !showPasswordConfirm"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-xl text-slate-400 transition hover:text-slate-700">
                                <span x-text="showPasswordConfirm ? '🙈' : '👁️'"></span>
                            </button>
                        </div>
                        <p class="mt-3 text-sm font-medium" :class="passwordConfirm.length ? (passwordMatch ? 'text-emerald-600' : 'text-red-600') : 'text-slate-400'">
                            <span x-text="passwordConfirm.length ? (passwordMatch ? 'Las contraseñas coinciden.' : 'Las contraseñas no coinciden.') : 'Confirma tu nueva contraseña.'"></span>
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="!isFormValid"
                        class="w-full rounded-2xl bg-[#326D6C] px-6 py-3 font-bold text-white shadow-lg transition hover:-translate-y-0.5 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500 disabled:hover:translate-y-0">
                        Actualizar contraseña
                    </button>
                </div>

                <aside class="rounded-[1.75rem] bg-slate-50 p-6 ring-1 ring-slate-200">
                    <h2 class="text-lg font-semibold text-[#173C4C]">Requisitos de seguridad</h2>
                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex items-start gap-3" :class="hasMinLength ? 'text-emerald-700' : 'text-slate-500'">
                            <span class="mt-0.5" x-text="hasMinLength ? '✓' : '○'"></span>
                            <span>Mínimo 8 caracteres</span>
                        </div>
                        <div class="flex items-start gap-3" :class="hasUppercase ? 'text-emerald-700' : 'text-slate-500'">
                            <span class="mt-0.5" x-text="hasUppercase ? '✓' : '○'"></span>
                            <span>Al menos una letra mayúscula</span>
                        </div>
                        <div class="flex items-start gap-3" :class="hasLowercase ? 'text-emerald-700' : 'text-slate-500'">
                            <span class="mt-0.5" x-text="hasLowercase ? '✓' : '○'"></span>
                            <span>Al menos una letra minúscula</span>
                        </div>
                        <div class="flex items-start gap-3" :class="hasNumber ? 'text-emerald-700' : 'text-slate-500'">
                            <span class="mt-0.5" x-text="hasNumber ? '✓' : '○'"></span>
                            <span>Al menos un número</span>
                        </div>
                        <div class="flex items-start gap-3" :class="hasSpecialChar ? 'text-emerald-700' : 'text-slate-500'">
                            <span class="mt-0.5" x-text="hasSpecialChar ? '✓' : '○'"></span>
                            <span>Al menos un carácter especial `@$!%*#?&`</span>
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl bg-white p-4 text-sm leading-6 text-slate-500 ring-1 ring-slate-200">
                        Después de actualizar la contraseña, volverás al login para ingresar con la nueva clave.
                    </div>
                </aside>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm font-semibold text-[#326D6C] transition hover:text-[#173C4C]">
                    Volver al login
                </a>
            </div>
        </section>
    </main>

    <script>
        function passwordRecoveryForm() {
            return {
                password: '',
                passwordConfirm: '',
                showPassword: false,
                showPasswordConfirm: false,
                hasMinLength: false,
                hasUppercase: false,
                hasLowercase: false,
                hasNumber: false,
                hasSpecialChar: false,
                passwordMatch: false,
                strengthLabel: 'Fortaleza pendiente',
                strengthClass: 'bg-slate-300',
                strengthTextClass: 'text-slate-400',
                strengthWidth: '0%',

                validatePasswordRules() {
                    this.hasMinLength = this.password.length >= 8;
                    this.hasUppercase = /[A-Z]/.test(this.password);
                    this.hasLowercase = /[a-z]/.test(this.password);
                    this.hasNumber = /[0-9]/.test(this.password);
                    this.hasSpecialChar = /[@$!%*#?&]/.test(this.password);
                    this.passwordMatch = this.password.length > 0 && this.password === this.passwordConfirm;

                    const score = [
                        this.hasMinLength,
                        this.hasUppercase,
                        this.hasLowercase,
                        this.hasNumber,
                        this.hasSpecialChar
                    ].filter(Boolean).length;

                    if (score === 0) {
                        this.strengthLabel = 'Fortaleza pendiente';
                        this.strengthClass = 'bg-slate-300';
                        this.strengthTextClass = 'text-slate-400';
                        this.strengthWidth = '0%';
                    } else if (score <= 2) {
                        this.strengthLabel = 'Fortaleza baja';
                        this.strengthClass = 'bg-red-500';
                        this.strengthTextClass = 'text-red-600';
                        this.strengthWidth = '35%';
                    } else if (score <= 4) {
                        this.strengthLabel = 'Fortaleza media';
                        this.strengthClass = 'bg-amber-500';
                        this.strengthTextClass = 'text-amber-600';
                        this.strengthWidth = '70%';
                    } else {
                        this.strengthLabel = 'Fortaleza alta';
                        this.strengthClass = 'bg-emerald-500';
                        this.strengthTextClass = 'text-emerald-600';
                        this.strengthWidth = '100%';
                    }
                },

                get isFormValid() {
                    return this.hasMinLength
                        && this.hasUppercase
                        && this.hasLowercase
                        && this.hasNumber
                        && this.hasSpecialChar
                        && this.passwordMatch;
                }
            }
        }
    </script>
</body>
</html>
