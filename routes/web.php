<?php

use App\Http\Controllers\FaceAuthController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Project\ProjectForm;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Livewire\Project\View as ProjectView;
use App\Livewire\Conversation\Show as ConversationShow;

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

    Route::get('/projects/create', ProjectForm::class)->name('project.create');

    Route::get('/projects/{project}/edit', ProjectForm::class)->name('project.edit');

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
        
    Route::get('/projects/{project}', ProjectView::class)->name('project.view');

    Route::get('/conversation/{investment}', ConversationShow::class)->name('conversation.show');
});

require __DIR__.'/auth.php';