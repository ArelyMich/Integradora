<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\LogAcceso;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Auth\Events\Registered;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;



class AuthController extends Controller
{
    // ==============================
    // LOGIN CON SEGURIDAD + 2FA
    // ==============================
     public function showLogin()
{
    // Si ya está autenticado, redirigir al dashboard
    if (Auth::check()) {
        return redirect('/dashboard');
    }

    // Mostrar el formulario de login con la clave pública de reCAPTCHA
    return view('login', [
        'recaptcha_key' => env('RECAPTCHA_PUBLIC_KEY')
    ]);
}

    public function login(Request $request)
{
    $loginInput = trim($request->username); // username o email
    $ip = $request->ip();
    $ua = $request->header('User-Agent');

    // ============================
    // VALIDAR RECAPTCHA
    // ============================
    $recaptchaToken = $request->input('g-recaptcha-response');
    if (!$recaptchaToken) {
        return back()->withErrors(['recaptcha' => 'Por favor, completa el reCAPTCHA.'])->withInput();
    }

    // Verificar el reCAPTCHA con Google
    $recaptchaUrl = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptchaResponse = file_get_contents($recaptchaUrl, false, stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query([
                'secret' => env('RECAPTCHA_SECRET_KEY'),
                'response' => $recaptchaToken
            ])
        ]
    ]));

    $recaptchaData = json_decode($recaptchaResponse);

    if (!$recaptchaData->success) {
        return back()->withErrors(['recaptcha' => 'La verificación de reCAPTCHA falló. Por favor, intenta de nuevo.'])->withInput();
    }

    // ============================
    // 1. BLOQUEO POR USER AGENT
    // ============================
    $uaSospechosos = ['bot', 'crawler', 'spider', 'curl', 'python', 'scrap'];
    foreach ($uaSospechosos as $bad) {
        if (str_contains(strtolower($ua), $bad)) {
            return back()->withErrors(['username' => 'Actividad sospechosa detectada.']);
        }
    }

    // ============================
    // 2. RATE LIMIT POR IP
    // ============================
    $intentosRapidos = LogAcceso::where('ip_address', $ip)
        ->where('created_at', '>=', now()->subSeconds(15))
        ->count();

    if ($intentosRapidos >= 10) {
        return back()->withErrors(['username' => 'Demasiados intentos en muy poco tiempo.']);
    }

    // ============================
    // 3. BLOQUEO POR FALLOS (USERNAME O EMAIL)
    // ============================
    $intentosFallidos = LogAcceso::where(function ($q) use ($loginInput) {
            $q->where('username', $loginInput)
              ->orWhere('email', $loginInput);
        })
        ->where('resultado', 'fallido')
        ->where('created_at', '>=', now()->subMinutes(30))
        ->count();

    if ($intentosFallidos >= 5) {
        return back()->withErrors(['username' => 'Cuenta bloqueada temporalmente.']);
    }

    // ============================
    // 4. DETECTAR SI ES EMAIL O USERNAME
    // ============================
    $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);

    $credentials = [
        $isEmail ? 'email' : 'username' => $loginInput,
        'password' => $request->password
    ];

    // ============================
    // 5. INTENTAR LOGIN
    // ============================
    if (!Auth::attempt($credentials)) {

        LogAcceso::create([
            'username'   => $isEmail ? null : $loginInput,
            'email'      => $isEmail ? $loginInput : null,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'resultado'  => 'fallido',
        ]);

        return back()->withErrors(['username' => 'Credenciales incorrectas.']);
    }

    $user = Auth::user();
    // ❌ BLOQUEAR SI NO HA VERIFICADO CORREO
    if (!$user->email_verified_at) {
    Auth::logout();
    return back()->withErrors([
        'username' => 'tu correo no fue verificado al registrarse.'
    ]);
    }

    // ============================
    // 6. INVALIDAR OTRAS SESIONES
    // ============================
    Auth::logoutOtherDevices($request->password);

    // ============================
    // 7. GENERAR 2FA SEGURO
    // ============================
    $code = random_int(100000, 999999);

    $user->update([
        'two_factor_code'        => $code,
        'two_factor_expires_at' => now()->addMinutes(5),
        'two_factor_enabled'    => true,
    ]);

    Mail::to($user->email)->send(new TwoFactorCodeMail($code));

    session([
        '2fa_user_id' => $user->id,
        '2fa_valid'   => false
    ]);

    Auth::logout();

    // ============================
    // 8. LOG COMO 2FA PENDIENTE
    // ============================
    LogAcceso::create([
        'username'   => $isEmail ? null : $user->username,
        'email'      => $isEmail ? $user->email : null,
        'ip_address' => $ip,
        'user_agent' => $ua,
        'resultado'  => '2fa_pendiente',
    ]);

    return redirect('/2fa');
}




    // ==============================
    // VERIFICAR CÓDIGO 2FA
   

    public function verify2fa(Request $request)
{
    $request->validate(['code' => 'required|digits:6']);

    $userId = session('2fa_user_id');
    $user = User::find($userId);

    if (!$user) {
        return redirect('/');
    }

    if (
        $user->two_factor_code == $request->code &&
        now()->lessThan($user->two_factor_expires_at)
    ) {
        $user->update([
            'two_factor_code'        => null,
            'two_factor_expires_at' => null,
        ]);

        session()->forget('2fa_user_id');

        Auth::login($user);

        // ✅ MARCAR EL LOGIN REAL COMO EXITOSO
        LogAcceso::where('resultado', '2fa_pendiente')
            ->where('ip_address', request()->ip())
            ->where(function ($q) use ($user) {
                $q->where('username', $user->username)
                  ->orWhere('email', $user->email);
            })
            ->latest()
            ->first()?->update([
                'resultado' => 'exitoso'
            ]);

        return redirect('/dashboard');
    }

    return back()->withErrors(['code' => 'Código incorrecto o expirado.']);
}

    // ==============================
    // LOGOUT
    // ==============================
 public function logout()
{
    $user = Auth::user();

    if ($user) {

        $log = LogAcceso::whereNull('logout_at')
            ->where(function ($q) use ($user) {
                $q->where('username', $user->username)
                  ->orWhere('email', $user->email);
            })
            ->orderByDesc('id')
            ->first();

        if ($log) {
            $log->update([
                'logout_at' => now()
            ]);
        }
    }

    Auth::logout();
    return redirect('/');
}


        // ==============================
    // NUEVO FLUJO: RECUPERACIÓN DE CONTRASEÑA (MEJORADO)
    // ==============================
    
    // PASO 1: Solicitar Email
    public function showPasswordRecovery()
    {
        return view('auth.password-recovery.step1-email');
    }

    public function sendPasswordRecoveryCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'No encontramos una cuenta con este correo.'
        ]);

        // Generar código de 6 dígitos
        $email = strtolower(trim($request->email));
        $code = (string) random_int(100000, 999999);

        // Guardar código con expiración
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => $code,
                'created_at' => now()
            ]
        );

        // Enviar código por Mailtrap
        Mail::to($email)->send(new ResetPasswordMail($code));

        // Guardar email en sesión para el siguiente paso
        session([
            'recovery_email' => $email,
            'recovery_code_verified' => false,
            'recovery_step' => 'verify_code'
        ]);

        return redirect()->route('password.recovery.verify')->with('success', 'Código enviado a tu correo.');
    }

    // PASO 2: Verificar Código
    public function showVerifyCode()
    {
        if (!session('recovery_email')) {
            return redirect()->route('password.recovery.email')->withErrors('Debe solicitar un código primero.');
        }

        return view('auth.password-recovery.step2-code');
    }

    public function verifyRecoveryCode(Request $request)
    {
        $email = session('recovery_email');
        
        if (!$email) {
            return redirect()->route('password.recovery.email')->withErrors('Sesión expirada.');
        }

        $request->validate([
            'code' => 'required|digits:6'
        ]);

        // Verificar código
        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (
            !$record ||
            (string) $record->token !== (string) $request->code ||
            now()->diffInMinutes($record->created_at) > 30
        ) {
            return back()->withErrors(['code' => 'Código inválido o expirado.'])->withInput();
        }

        // Marcar como verificado en sesión
        session([
            'recovery_code_verified' => true,
            'recovery_step' => 'new_password'
        ]);

        return redirect()->route('password.recovery.new');
    }

    // PASO 3: Establecer Nueva Contraseña
    public function showNewPassword()
    {
        if (!session('recovery_code_verified')) {
            return redirect()->route('password.recovery.email')->withErrors('Debe verificar el código primero.');
        }

        return view('auth.password-recovery.step3-password');
    }

    public function updateRecoveryPassword(Request $request)
    {
        $email = session('recovery_email');

        if (!$email || !session('recovery_code_verified')) {
            return redirect()->route('password.recovery.email')->withErrors('Sesión expirada.');
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$record || now()->diffInMinutes($record->created_at) > 30) {
            session()->forget(['recovery_email', 'recovery_code_verified', 'recovery_step']);

            return redirect()->route('password.recovery.email')->withErrors('El código expiró. Solicita uno nuevo.');
        }

        // Validaciones estrictas de contraseña
        $request->validate([
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/(?=.*[A-Z])/',      // Al menos una mayúscula
                'regex:/(?=.*[a-z])/',      // Al menos una minúscula
                'regex:/(?=.*[0-9])/',      // Al menos un número
                'regex:/(?=.*[@$!%*#?&])/'  // Al menos un carácter especial
            ],
            'password_confirmation' => 'required'
        ], [
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe contener: mayúscula, minúscula, número y carácter especial (@$!%*#?&).',
            'password.confirmed' => 'Las contraseñas no coinciden.'
        ]);

        // Actualizar contraseña
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return redirect()->route('password.recovery.email')->withErrors('Usuario no encontrado.');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Limpiar código de reseteo
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Limpiar sesión
        session()->forget(['recovery_email', 'recovery_code_verified', 'recovery_step']);

        return redirect()->route('login')->with('status', '✓ Contraseña actualizada correctamente. Inicia sesión con tu nueva contraseña.');
    }

    // ==============================
    // SISTEMA ANTIGUO - DESHABILITADO
    // ==============================
    /*
    public function sendPasswordEmail(Request $request)
    {
    $request->validate([
        'email' => 'required|email|exists:users,email'
    ]);

    // Generar código de 6 dígitos
$codigo = random_int(100000, 999999);

    // Guardar código en la tabla password_reset_tokens
    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        [
            'email' => $request->email,
            'token' => $codigo,
            'created_at' => now()
        ]
    );

    // Enviar el código por correo
    Mail::to($request->email)->send(new ResetPasswordMail($codigo));

    // Guardar email en sesión (opcional, si quieres prellenar)
    session(['reset_email' => $request->email]);

    // **Devolver JSON para que el fetch funcione**
    return response()->json([
        'status' => true,
        'message' => 'Código enviado. Revisa tu correo.'
    ]);
}

    // ==============================
    // CAMBIAR CONTRASEÑA CON CÓDIGO
    // ==============================
    public function resetPasswordWithCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|digits:6',
           'password' => [
                            'required',
                            'confirmed',
                            'min:8',
                            'regex:/^(?=.*[0-9])(?=.*[@$!%*#?&]).+$/'
           ],
         'password_confirmation' => 'required',

        ]);

        // Verificar que el código coincida
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return back()->withErrors(['codigo' => 'Código inválido o expirado.']);
        }

        // Actualizar contraseña del usuario
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Borrar código usado
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect('/')->with('status', 'Contraseña cambiada correctamente.');
    }
    */

    // ==============================
    // REGISTRO SEGURO
    // ==============================
 public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'apellido_paterno' => 'required|string|max:255',
        'apellido_materno' => 'required|string|max:255',
        'username' => 'required|string|min:3|max:30|unique:users,username',
        'email' => [
            'required',
            'email',
            'unique:users,email',
            'regex:/^[\w\.-]+@[\w\.-]+\.edu\.mx$/'
        ],
        'password' => [
            'required',
            'confirmed',
            'min:8',
            'regex:/[0-9]/',
            'regex:/[@$!%*#?&]/'
        ],
    ], [
        'email.regex' => 'Solo correos .edu.mx',
        'password.regex' => 'Debe tener número y carácter especial',
    ]);

    if (!str_ends_with($request->email, '.edu.mx')) {
        return back()->withErrors(['email' => 'Correo institucional inválido']);
    }

    // ✅ CREAR USUARIO
    $user = User::create([
        'name' => $request->name,
        'apellido_paterno' => $request->apellido_paterno,
        'apellido_materno' => $request->apellido_materno,
        'username' => $request->username,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'status' => 1,
        'two_factor_enabled' => true,
        'email_verified_at' => null,
    ]);

    // ✅ ASIGNAR ROL
    DB::table('user_has_role')->insert([
        'user_id' => $user->id,
        'role_id' => 5,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // ✅ LOG DE REGISTRO
    LogAcceso::create([
        'email'       => $request->email,
        'ip_address'  => $request->ip(),
        'user_agent' => $request->header('User-Agent'),
        'resultado'  => 'registro',
    ]);

    // ✅ LOGIN INTERNO SOLO PARA PODER ENVIAR VERIFICACIÓN
    Auth::login($user);

    // ✅ ENVIAR CORREO DE VERIFICACIÓN
    event(new Registered($user));

    // ✅ CERRAR SESIÓN INMEDIATAMENTE (CLAVE PARA QUE NO ENTRE AL DASHBOARD)
    Auth::logout();

    // ✅ RESPUESTA PARA FETCH (MODAL)
    if ($request->ajax()) {
        return response()->json([
            'status' => true,
            'showVerifyModal' => true
        ]);
    }

    // ✅ REDIRECCIÓN NORMAL AL LOGIN CON MODAL
    return redirect()->route('login')->with([
        'showVerifyModal' => true,
        'status' => 'verification-link-sent'
    ]);
}

}
