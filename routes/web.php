<?php

// CAMBIO 1: Asegúrate de importar los controladores y componentes que usarás.
use App\Http\Controllers\FaceAuthController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Project\ProjectForm; // <-- Esta línea es nueva y crucial.
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::post('/face-login', [FaceAuthController::class, 'login'])->name('face.login');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::post('/user/face-enroll', [FaceAuthController::class, 'enroll'])->name('face.enroll');

    // ==========================================================
    // INICIO DEL CÓDIGO AÑADIDO - RUTAS PARA PROYECTOS
    // ==========================================================
    // Esta es la ruta que tu botón "Crear Nuevo Proyecto" está buscando.
    // Le dice a Laravel que, cuando un usuario vaya a '/projects/create',
    // debe renderizar el componente de Livewire 'ProjectForm'.
    Route::get('/projects/create', ProjectForm::class)->name('project.create');

    // Esta ruta manejará la edición de un proyecto existente.
    Route::get('/projects/{project}/edit', ProjectForm::class)->name('project.edit');
    // ==========================================================
    // FIN DEL CÓDIGO AÑADIDO
    // ==========================================================

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

require __DIR__.'/auth.php';