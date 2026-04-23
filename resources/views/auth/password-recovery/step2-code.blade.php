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

                radial-gradient(circle at 15% 20%,
                    rgba(86, 143, 124, 0.15),
                    transparent 40%),

                radial-gradient(circle at 85% 80%,
                    rgba(50, 109, 108, 0.15),
                    transparent 45%),

                linear-gradient(145deg,
                    #f8fafc,
                    #eef2f6,
                    #e2e8f0);
        }
    </style>
</head>

<body class="min-h-screen px-4 py-8 text-slate-800">
    <main class="mx-auto max-w-2xl">
        <section
            class="rounded-[2rem]

bg-white

p-8

shadow-[0_25px_60px_rgba(0,0,0,0.08)]

border border-slate-200

sm:p-10"
            x-data="{ code: '' }">
            <div class="mb-8 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.3em] text-slate-400">Paso 2 de 3</p>
                    <h1 class="mt-3 text-3xl font-bold text-[#173C4C]">Verifica el código</h1>
                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        Revisa tu correo e ingresa el código de 6 dígitos para continuar.
                    </p>
                </div>

                <div class="flex items-center gap-3 text-sm font-semibold">

                    <span
                        class="flex h-10 w-10 items-center justify-center
rounded-full
bg-[#326D6C]
text-white
shadow-md">
                        1
                        <i class="fa-solid fa-check"></i>

                    </span>

                    <span class="h-1 w-12 rounded-full bg-[#326D6C]"></span>

                    <span
                        class="flex h-10 w-10 items-center justify-center
rounded-full
bg-[#568F7C]
text-white
shadow-md">

                        2

                    </span>

                    <span class="h-1 w-12 rounded-full bg-slate-200"></span>

                    <span class="flex h-10 w-10 items-center justify-center
rounded-full
bg-slate-200
text-slate-500">

                        3

                    </span>

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
                <div
                    class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.recovery.verify.post') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="code" class="mb-2 block text-sm font-semibold text-slate-700">Código de 6
                        dígitos</label>



                </div>

                <div x-data="{
                
                    code: ['', '', '', '', '', ''],
                
                    minutes: 29,
                    seconds: 59,
                
                    startTimer() {
                
                        setInterval(() => {
                
                            if (this.seconds === 0) {
                
                                if (this.minutes === 0) return
                
                                this.minutes--
                                this.seconds = 59
                
                            } else {
                
                                this.seconds--
                
                            }
                
                        }, 1000)
                
                    },
                
                    handleInput(index, event) {
                
                        let value =
                            event.target.value.replace(/[^0-9]/g, '')
                
                        if (value.length > 1) {
                
                            value.split('').forEach((digit, i) => {
                
                                if (index + i < this.code.length) {
                                    this.code[index + i] = digit
                                }
                
                            })
                
                        } else {
                
                            this.code[index] = value
                
                            if (value &&
                                event.target.nextElementSibling) {
                
                                event.target
                                    .nextElementSibling
                                    .focus()
                
                            }
                
                        }
                
                    },
                
                    handlePaste(event) {
                
                        let paste =
                            event.clipboardData
                            .getData('text')
                            .replace(/[^0-9]/g, '')
                
                        paste.split('').forEach((digit, i) => {
                
                            if (i < this.code.length) {
                                this.code[i] = digit
                            }
                
                        })
                
                        event.preventDefault()
                
                    }
                
                }" x-init="startTimer()" class="flex flex-col gap-6">

                    <div class="flex justify-center gap-3">

                        <template x-for="(digit, index) in code">

                            <input type="text" maxlength="1" x-model="code[index]"
                                @input="handleInput(index,$event)" @paste="handlePaste($event)"
                                @keydown.backspace="
if (!code[index] &&
$event.target.previousElementSibling) {

$event.target
.previousElementSibling
.focus()

}
"
                                class="w-12 h-14

text-center
text-xl
font-bold

rounded-xl

border border-slate-300

bg-white

shadow-sm

transition

focus:border-[#568F7C]
focus:ring-2
focus:ring-[#85B093]/30

:class="{ 'border-[#326D6C] bg-[#ECFDF5]'
                                : code[index] }">

                        </template>

                    </div>

                    <input type="hidden" name="code" :value="code.join('')">
                    <p class="mt-4 

text-sm 

text-[#326D6C]

font-medium

flex items-center
justify-center
gap-2">

                        <i class="fa-regular fa-clock text-[#568F7C]"></i>

                        <span>

                            El código expira en

                            <strong>

                                <span x-text="minutes"></span>:
                                <span x-text="seconds.toString().padStart(2,'0')"></span>

                            </strong>

                        </span>

                    </p>


                    <button type="submit" <button type="submit" :disabled="code.join('').length !== 6"
                        class="w-full 

flex items-center 
justify-center 
gap-2

rounded-2xl 

px-6 py-4 

font-bold 

text-white 

bg-gradient-to-r 
from-[#326D6C] 
to-[#568F7C]

shadow-[0_10px_25px_rgba(50,109,108,0.35)]

transition 

hover:-translate-y-0.5 
hover:shadow-[0_14px_30px_rgba(50,109,108,0.45)] 

disabled:bg-slate-400
disabled:text-white
disabled:opacity-70">

                        <i class="fa-solid fa-check"></i>

                        Verificar código

                    </button>

                </div>
            </form>

            <div class="mt-6 flex flex-col gap-3 text-center sm:flex-row sm:justify-between">
                <a href="{{ route('password.recovery.email') }}"
                    class="text-sm font-semibold text-[#326D6C] transition hover:text-[#173C4C]">
                    Usar otro correo
                </a>
                <a href="{{ route('login') }}"
                    class="text-sm font-semibold text-slate-500 transition hover:text-slate-700">
                    Volver al login
                </a>
            </div>
        </section>
    </main>
</body>

</html>
