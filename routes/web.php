<?php

use App\Http\Controllers\FaceAuthController; // <-- AÑADE ESTA LÍNEA AL PRINCIPIO
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ==========================================================
// RUTA PÚBLICA PARA EL LOGIN FACIAL (FUERA DEL MIDDLEWARE 'AUTH')
// ==========================================================
Route::post('/face-login', [FaceAuthController::class, 'login'])->name('face.login');


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    // ==========================================================
    // RUTA PRIVADA PARA REGISTRAR EL ROSTRO (DENTRO DEL MIDDLEWARE 'AUTH')
    // ==========================================================
    Route::post('/user/face-enroll', [FaceAuthController::class, 'enroll'])->name('face.enroll');

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
