@props(['show' => false])

@if($show)
<div class="fixed inset-0 z-50 flex items-center justify-center"
     style="background: rgba(0,0,9,0.75);">

    <div class="w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-fadeIn"
         style="background:#07142B; border:1px solid #173C4C;">

        {{-- HEADER --}}
        <div class="text-center p-6"
             style="background: linear-gradient(135deg, #326D6C, #568F7C);">
            <h2 class="text-2xl font-bold text-white">
                Verifica tu correo
            </h2>
        </div>

        {{-- BODY --}}
        <div class="p-6 text-center space-y-5 text-white">

            <p class="text-sm" style="color:#85B093;">
                Te enviamos un enlace a tu correo institucional.
                Revisa tu bandeja y confirma tu cuenta.
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="p-3 rounded-lg text-sm"
                     style="background:rgba(133,176,147,.15); color:#85B093;">
                    ✅ Enlace reenviado correctamente
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="w-full py-2 rounded-xl font-semibold"
                        style="background:#568F7C; color:#000009;">
                    Reenviar correo
                </button>
            </form>

            <a href="{{ route('logout') }}"
               class="block text-sm underline"
               style="color:#85B093;">
                Cerrar sesión
            </a>
        </div>
    </div>
</div>
@endif
