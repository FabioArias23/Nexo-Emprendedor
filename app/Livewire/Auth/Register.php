<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\FaceRecognitionService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $faceImage = null;
    public string $role = '';

    public function register(FaceRecognitionService $faceService): void
    {
        // <-- CAMBIO CLAVE 2: La validación ahora usa las constantes en inglés del modelo User.
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in([User::ROLE_ENTREPRENEUR, User::ROLE_INVESTOR])],
        ]);

        $user = User::create($validated);

        event(new Registered($user));

        if (!empty($this->faceImage)) {
            $faceService->enroll($user, $this->faceImage);
        }

        Auth::login($user);

        Session::regenerate();

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}