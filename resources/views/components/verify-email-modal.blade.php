@props(['show' => false])

@if($show)
<div class="fixed inset-0 z-50 flex items-center justify-center"
     style="background: rgba(0,0,9,0.75);">

    <div class="w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-fadeIn"
         style="background:#07142B; border:1px solid #173C4C;">

        {{-- HEADER --}}
        <div class="text-center p-6"
             style="background: linear-gradient(135deg, #326D6C, #568F7C);">
            <h2 class="text-2xl font-bold text-white tracking-wide">
                Verifica tu correo
            </h2>
            <p class="text-sm text-white/80 mt-1">
                Protección de tu cuenta
            </p>
        </div>

        {{-- BODY --}}
        <div class="p-6 text-center space-y-5 text-white">

            <p class="text-sm leading-relaxed" style="color:#85B093;">
                Te enviamos un enlace de verificación a tu correo institucional.
                Debes confirmarlo para poder verificar que tu cuenta sea real, una vez lo confirmes seras redirigido al sistema.
            </p>

            {{-- MENSAJE --}}
            @if (session('status') == 'verification-link-sent')
                <div class="p-3 rounded-lg text-sm font-semibold"
                     style="background:rgba(133,176,147,.15); color:#85B093; border:1px solid #568F7C;">
                    ✅ Enlace reenviado correctamente, por favor revisa tu bandeja de entrada.
                </div>
            @endif

            {{-- REENVIAR --}}
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="w-full py-2 rounded-xl font-semibold transition-all"
                        style="background:#568F7C; color:#000009;">
                    Reenviar correo
                </button>
            </form>

            {{-- CERRAR SESIÓN --}}
            <a href="{{ route('logout') }}"
               class="block text-sm font-medium transition-colors"
               style="color:#85B093;">
                Cerrar sesión
            </a>
        </div>
    </div>
</div>
@endif
