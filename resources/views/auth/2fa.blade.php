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
            background:

radial-gradient(circle at 15% 20%,
rgba(86,143,124,0.15),
transparent 40%),

radial-gradient(circle at 85% 80%,
rgba(50,109,108,0.15),
transparent 45%),

linear-gradient(
145deg,
#f8fafc,
#eef2f6,
#e2e8f0
);
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
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white p-8 sm:p-10 rounded-2xl shadow-2xl transition duration-300">
        
        <!-- Encabezado -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-uth-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-gray-900">Verificación en Dos Pasos</h1>
            <p class="text-gray-500 mt-3 text-sm">
                Ingresa el código de 6 dígitos que hemos enviado a tu correo electrónico
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
       <form method="POST" action="/2fa"

x-data="{

code: ['', '', '', '', '', ''],

minutes: 4,
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
event.target.value.replace(/[^0-9]/g,'')

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
.replace(/[^0-9]/g,'')

paste.split('').forEach((digit, i) => {

if (i < this.code.length) {
this.code[i] = digit
}

})

event.preventDefault()

}

}"

x-init="startTimer()"

class="space-y-6">

@csrf

<!-- CÓDIGO EN CAJAS -->

<div>

<label 

class="block text-sm font-medium text-gray-700 mb-3">

Código de Verificación

</label>

<div class="flex justify-center gap-3">

<template x-for="(digit,index) in code">

<input

type="text"

maxlength="1"

x-model="code[index]"

@input="handleInput(index,$event)"

@paste="handlePaste($event)"

@keydown.backspace="

if (!code[index] &&
$event.target.previousElementSibling) {

$event.target.previousElementSibling.focus()

}

"

class="w-12 h-14

text-center
text-xl
font-bold

rounded-xl

border border-gray-300

bg-white

shadow-sm

transition

focus:border-[#568F7C]
focus:ring-2
focus:ring-[#85B093]/30

:class="{
'border-[#326D6C] bg-[#ECFDF5]':
code[index]
}"

>

</template>

</div>

<input
type="hidden"
name="code"
:value="code.join('')">

</div>

<!-- CONTADOR -->

<p class="text-sm 

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

<!-- BOTÓN -->

<button 

type="submit"

:disabled="code.join('').length !== 6 
|| (minutes === 0 && seconds === 0)"

class="w-full 

text-white 

font-bold 

py-3 

rounded-xl 

shadow-lg 

transition 

bg-gradient-to-r 
from-[#326D6C] 
to-[#568F7C]

hover:scale-[1.01]

disabled:bg-slate-400
disabled:opacity-70
disabled:cursor-not-allowed">

Verificar Código

</button>

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
