<?php

namespace App\Livewire\Auth;

use App\Models\User;
use App\Services\FaceRecognitionService; // <-- AÑADE ESTO
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
    
    // <-- AÑADE ESTA PROPIEDAD PÚBLICA (puede ser nula)
    public ?string $faceImage = null;

    /**
     * Handle an incoming registration request.
     */
    public function register(FaceRecognitionService $faceService): void // <-- INYECTA EL SERVICIO
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in([User::ROLE_EMPRENDEDOR, User::ROLE_INVERSOR])],
 
        ]);

        $user = User::create($validated);

        event(new Registered($user));

        // <-- AÑADE ESTA LÓGICA
        // Si el usuario capturó una imagen de su rostro, la registramos.
        if (!empty($this->faceImage)) {
            $faceService->enroll($user, $this->faceImage);
            // Aquí podrías añadir un log o un flash message si falla, pero para un hackathon lo mantenemos simple.
        }

        Auth::login($user);

        Session::regenerate();

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}