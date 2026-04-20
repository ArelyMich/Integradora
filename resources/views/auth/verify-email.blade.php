@props(['show' => false])

@if($show)
<div class="fixed inset-0 z-50 flex items-center justify-center"
     style="background: rgba(31, 41, 55, 0.75);">

    <div class="w-full max-w-md rounded-2xl shadow-lg overflow-hidden animate-fadeIn"
         style="background:#374151; border:1px solid #6B7280;">

        {{-- HEADER --}}
        <div class="text-center p-6"
             style="background: linear-gradient(135deg, #23877E, #2FA69A);">
            <h2 class="text-2xl font-bold text-white">
                Verifica tu correo
            </h2>
        </div>

        {{-- BODY --}}
        <div class="p-6 text-center space-y-5 text-white">

            <p class="text-sm" style="color:#E5E7EB;">
                Te enviamos un enlace a tu correo institucional.
                Revisa tu bandeja y confirma tu cuenta.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="p-3 rounded-lg text-sm"
                     style="background:rgba(47,166,154,.15); color:#2FA69A;">
                    ✅ Enlace reenviado correctamente
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="w-full py-2 rounded-xl font-semibold"
                        style="background:#2FA69A; color:#FFFFFF;">
                    Reenviar correo
                </button>
            </form>

            <a href="{{ route('logout') }}"
               class="block text-sm underline"
               style="color:#E5E7EB;">
                Cerrar sesión
            </a>
        </div>
    </div>
</div>
@endif
