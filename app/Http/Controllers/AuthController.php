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

    // Mostrar el formulario de login
    return view('login');
}

    public function login(Request $request)
{
    $request->validate([
        'username' => 'required|string|max:255',
        'password' => 'required|string|min:8',
    ]);

    $loginInput = trim($request->username); // username o email
    $ip = $request->ip();
    $ua = $request->header('User-Agent');

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

    if (!($user instanceof User)) {
        Auth::logout();
        return back()->withErrors(['username' => 'No se pudo iniciar sesion correctamente.']);
    }

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
    // ENVIAR CÓDIGO PARA RESTABLECER CONTRASEÑA
    // ==============================
   public function sendResetCode(Request $request)
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

    // ==============================
    // REGISTRO SEGURO
    // ==============================
 public function register(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255|regex:/^[\pL\s]+$/u',
        'apellido_paterno' => 'required|string|max:255|regex:/^[\pL\s]+$/u',
        'apellido_materno' => 'required|string|max:255|regex:/^[\pL\s]+$/u',
        'username' => 'required|string|min:3|max:30|regex:/^[A-Za-z0-9_.-]+$/|unique:users,username',
        'email' => [
            'required',
            'email',
            'unique:users,email',
            'regex:/^[\w\.-]+@[\w\.-]+\.edu\.mx$/'
        ],
        'role_id' => 'nullable|in:4,5',
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
        'name.regex' => 'El nombre solo puede contener letras y espacios.',
        'apellido_paterno.regex' => 'El apellido paterno solo puede contener letras y espacios.',
        'apellido_materno.regex' => 'El apellido materno solo puede contener letras y espacios.',
        'username.regex' => 'El username solo puede contener letras, números, punto, guion y guion bajo.',
    ]);

    if (!str_ends_with(strtolower($request->email), '.edu.mx')) {
        return back()->withErrors(['email' => 'Correo institucional inválido']);
    }

    // Por defecto, el registro público se considera como Docente.
    $roleId = (int) $request->input('role_id', 4);

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
        'role_id' => $roleId,
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
