<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SecuenciaController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CarreraController;
use App\Mail\TwoFactorCodeMail; 
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\PerfilController;


    // ================================
    // LOGIN (PÚBLICO)
    // ================================
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');

    Route::post(
    '/secuencias/upload',
    [SecuenciaController::class, 'uploadAndExtract']
)->name('secuencias.upload');

Route::post('/insertarSecuencia',[secuenciaController::class,'uploadAndExtract'])
->name('secuencias.uploadAndExtract');  

Route::post(
    '/secuencias/{id}/status',
    [SecuenciaController::class, 'updateStatus']
)->name('secuencias.updateStatus');

Route::get(
    '/secuencias',
    [SecuenciaController::class,'index']
)->name('secuencias.index');

Route::get(
    '/secuencias/create',
    [SecuenciaController::class,'create']
)->name('secuencias.create');

Route::get(
    '/secuencias/create/{id}',
    [SecuenciaController::class,'createView']
)->name('secuencias.edit');

Route::get(
'/secuencias/{id}/export-word',
[SecuenciaController::class,'exportWord']
)->name('secuencias.exportWord');




    Route::post('/login',[AuthController::class,'login'])
        ->name('login.process');
    //verificar email registro
   // 📩 Aviso de verificación
Route::get('/verify-email', function () {
    return view('auth.verify-email');
})->name('verification.notice');

// ✅ Verificar correo
Route::get('/verify-email/{id}/{hash}', function ($id, $hash) {

    $user = User::findOrFail($id);

    // Validar el hash
    if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        abort(403, 'Enlace de verificación inválido.');
    }

    // Marcar email como verificado
    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new Verified($user));
    }

    // Autenticar al usuario
    Auth::login($user);

    // Redirigir donde tú quieres
    return redirect()->route('dashboard')->with('verified', true);

})->name('verification.verify');
// 🔁 Reenviar correo de verificación
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'Correo reenviado.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
    // ================================
    // REGISTER (PÚBLICO) - DESHABILITADO TEMPORALMENTE
    // ================================
    /*
    Route::get('/register', function () {
        return view('register');
    })->name('register.view');
    */

// ================================
// LOGOUT
// ================================
Route::get('/logout',[AuthController::class,'logout'])
    ->name('logout');
    
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware('auth');

  
/*
| Rutas del Dashboard (Gráficas + Calendario)
| Solo requieren auth, NO check.permission
|--------------------------------------------------------------------------
*/



Route::middleware(['auth'])->group(function () {

    // Gráficas
    Route::get('/chart/secuencias-mes', [DashboardController::class, 'chartSecuenciasMes']);
    Route::get('/chart/especialidades', [DashboardController::class, 'chartEspecialidades']);
    Route::get('/chart/entregadas-docente', [DashboardController::class, 'chartEntregadasDocente']);
    Route::get('/chart/linea-avance', [DashboardController::class, 'chartLineaAvance']);

    // Calendario
    Route::get('/eventos', [CalendarController::class, 'index']);
    Route::post('/eventos', [CalendarController::class, 'store']);
});
    // REGISTER POST - DESHABILITADO TEMPORALMENTE
    /*
    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.process');
    */
    //restablecer contraseña - DESHABILITADO (USAR NUEVO FLUJO /password-recovery)
    /*
    // Form enviar correo con código
    Route::get('/forgot-password', function() {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('/forgot-password', [AuthController::class, 'sendResetCode'])
        ->name('password.email'); // <- este es el que te faltaba
    // Mostrar formulario para cambiar contraseña con código

    Route::get('/reset-password-code', function() {
        return view('auth.reset-password-code');
    })->name('password.code');

    // Enviar el formulario
    Route::post('/reset-password-code', [AuthController::class, 'resetPasswordWithCode'])->name('password.update.code');
    */

    // ================================
    // NUEVO FLUJO: RECUPERACIÓN DE CONTRASEÑA (3 PASOS)
    // ================================
    // Paso 1: Solicitar Email
    Route::get('/password-recovery', [AuthController::class, 'showPasswordRecovery'])->name('password.recovery.email');
    Route::post('/password-recovery', [AuthController::class, 'sendPasswordRecoveryCode'])->name('password.recovery.send');
    
    // Paso 2: Verificar Código
    Route::get('/password-recovery/verify-code', [AuthController::class, 'showVerifyCode'])->name('password.recovery.verify');
    Route::post('/password-recovery/verify-code', [AuthController::class, 'verifyRecoveryCode'])->name('password.recovery.verify.post');
    
    // Paso 3: Nueva Contraseña
    Route::get('/password-recovery/new-password', [AuthController::class, 'showNewPassword'])->name('password.recovery.new');
    Route::post('/password-recovery/new-password', [AuthController::class, 'updateRecoveryPassword'])->name('password.recovery.update');

    Route::get(
    '/secuencias/create/{id?}',
    [SecuenciaController::class, 'createView']
)->name('secuencias.create');
    // ================================
    // LOGOUT
    // ================================
    Route::get('/logout',[AuthController::class,'logout'])
        ->name('logout');
        
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('auth');

    Route::get('/2fa', function () {
        return view('auth.2fa');
    })->middleware('check.2fa');

    Route::post('/2fa', [AuthController::class, 'verify2fa'])->middleware('check.2fa');


    Route::get('/test-email', function () {
        Mail::to('siguevictor767@gmail.com')->send(new TwoFactorCodeMail(123456));
        return 'Correo enviado';
    });



Route::middleware(['auth'])->group(function () {
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil');
    Route::post('/perfil/username', [PerfilController::class, 'actualizarUsername'])->name('perfil.actualizarUsername');
    Route::post('/perfil/password', [PerfilController::class, 'cambiarContrasena'])->name('perfil.cambiarContrasena');
});

    // ================================
    // RUTAS PROTEGIDAS CON AUTH
    // ================================
    Route::middleware(['auth', 'check.permission'])->group(function() {

        //********** USUARIOS ********** */
        Route::get('usuarios',[UserController::class,'index'])
            ->name('usuarios.index');

        Route::post('/usuarios/create', [UserController::class, 'store'])
            ->name('usuarios.store');
        
        Route::post('/usuarios/{id}/cambiar-rol', [UserController::class, 'cambiarRol'])
            ->name('usuarios.cambiarRol');

        Route::post('/usuarios/{id}/accesos', [UserController::class, 'actualizarAccesos'])
            ->name('usuarios.actualizarAccesos');

        //********** PERMISOS ********** */    
        Route::get('/permisos',[PermisoController::class,'index'])
            ->name('permisos.index');

        Route::post('/permisosCreate',[PermisoController::class,'store'])
            ->name('permisos.store'); 

        Route::post('/permisosUpdate',[PermisoController::class,'update'])
            ->name('permisos.update');

        Route::post('/permisosDesactivar',[PermisoController::class,'desactivarPermiso'])
            ->name('permisos.desactivar');
            
        Route::get('/permisosView/{id}',[PermisoController::class,'view'])
            ->name('permisos.view');

        Route::post('/permisos/asignarRoles/{permiso}',[PermisoController::class,'assignRoles'])
        ->name('permisos.assignRoles');

        //********** SECUENCIAS ********/
        Route::get('secuencias/Crear',[SecuenciaController::class,'createView'])
            ->name('secuencias.createView');

        Route::post('/secuenciasCreate',[SecuenciaController::class,'store'])
            ->name('secuencias.store');

        Route::get('/secuencias/{secuencia}', [SecuenciaController::class, 'show'])
            ->name('secuencias.show');

        Route::get('/secuencias/{secuencia}/archivo', [SecuenciaController::class, 'verArchivo'])
            ->name('secuencias.verArchivo');

        Route::get('/secuencias/{secuencia}/archivo-versiones/{version}', [SecuenciaController::class, 'verArchivoVersion'])
            ->name('secuencias.verArchivoVersion');

        Route::get('/secuencias/{secuencia}/editor', [SecuenciaController::class, 'editor'])
            ->name('secuencias.editor');

        Route::post('/secuencias/{secuencia}/ocr', [SecuenciaController::class, 'ocrArchivo'])
            ->name('secuencias.ocrArchivo');

        Route::post('/secuencias/{secuencia}/archivo', [SecuenciaController::class, 'actualizarArchivo'])
            ->name('secuencias.actualizarArchivo');

        Route::post('/secuencias/{secuencia}/archivo/anotar', [SecuenciaController::class, 'anotarArchivo'])
            ->name('secuencias.anotarArchivo');

        Route::post('/secuencias/{secuencia}/comentarios', [SecuenciaController::class, 'guardarComentario'])
            ->name('secuencias.comentarios.guardar');

        Route::put('/secuencias/{secuencia}/comentarios/{comentario}/responder', [SecuenciaController::class, 'responderComentario'])
            ->name('secuencias.comentarios.responder');

        Route::put('/secuencias/{secuencia}/comentarios/{comentario}/estado', [SecuenciaController::class, 'actualizarEstadoComentario'])
            ->name('secuencias.comentarios.estado');

        Route::put('/secuencias/{secuencia}/estado', [SecuenciaController::class, 'cambiarEstado']);
        Route::put('/secuencias/{secuencia}/estatus-academico', [SecuenciaController::class, 'actualizarEstatusAcademico'])
            ->name('secuencias.actualizarEstatusAcademico');
        Route::put('/secuencias/{id}', [SecuenciaController::class,'update'])
            ->name('secuencias.update');

        //********** MATERIAS ********* */   
        Route::get('/materias',[MateriaController::class,'index'])
            ->name('materias.index');

        Route::post('/materiasCreate',[MateriaController::class,'store'])
            ->name('materias.store');    

        Route::post('/materias/asignarMateria',[MateriaController::class,'asignarMateria'])
        ->name('materias.asignarMateria');    
        
        //********** CARRERAS ********* */    
        Route::get('/carreras',[CarreraController::class,'index'])
            ->name('carreras.index');

        Route::post('/carreraCreate',[CarreraController::class,'store'])
            ->name('carreras.store');    

        Route::post('/carreras/asignar-director', [CarreraController::class, 'asignarDirector'])
            ->name('carreras.asignarDirector');
        
        Route::post('/carreras/AsignarProfesor',[CarreraController::class,'asignarProfesores'])
            ->name('carreras.asignarProfesores');    

        Route::put('/carreras/{carrera}/estado', [CarreraController::class, 'cambiarEstado']);
   

        //********** ROLES *********/
        Route::get('/roles', [RoleController::class, 'index'])
            ->name('roles.index');

        Route::post('/rolesCreate', [RoleController::class, 'store'])
            ->name('roles.store');

        Route::put('/roles/update/{id}', [RoleController::class, 'update'])
            ->name('roles.update');

        Route::put('/roles/delete/{id}', [RoleController::class, 'destroy'])
            ->name('roles.desactivate');


    });

