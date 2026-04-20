@props(['show' => false])

@if($show)
<div class="fixed inset-0 z-50 flex items-center justify-center"
     style="background: rgba(31, 41, 55, 0.75);">

    <div class="w-full max-w-md rounded-2xl shadow-lg overflow-hidden animate-fadeIn"
         style="background:#374151; border:1px solid #6B7280;">

        {{-- HEADER --}}
        <div class="text-center p-6"
             style="background: linear-gradient(135deg, #23877E, #2FA69A);">
            <h2 class="text-2xl font-bold text-white tracking-wide">
                Verifica tu correo
            </h2>
            <p class="text-sm text-white/80 mt-1">
                Protección de tu cuenta
            </p>
        </div>

        {{-- BODY --}}
        <div class="p-6 text-center space-y-5 text-white">

            <p class="text-sm leading-relaxed" style="color:#E5E7EB;">
                Te enviamos un enlace de verificación a tu correo institucional.
                Debes confirmarlo para poder verificar que tu cuenta sea real, una vez lo confirmes seras redirigido al sistema.
            </p>

            {{-- MENSAJE --}}
            @if (session('status') == 'verification-link-sent')
                <div class="p-3 rounded-lg text-sm font-semibold"
                     style="background:rgba(47,166,154,.15); color:#2FA69A; border:1px solid #2FA69A;">
                    ✅ Enlace reenviado correctamente, por favor revisa tu bandeja de entrada.
                </div>
            @endif

            {{-- REENVIAR --}}
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="w-full py-2 rounded-xl font-semibold transition-all"
                        style="background:#2FA69A; color:#FFFFFF;">
                    Reenviar correo
                </button>
            </form>

            {{-- CERRAR SESIÓN --}}
            <a href="{{ route('logout') }}"
               class="block text-sm font-medium transition-colors"
               style="color:#E5E7EB;">
                Cerrar sesión
            </a>
        </div>
    </div>
</div>
@endif
