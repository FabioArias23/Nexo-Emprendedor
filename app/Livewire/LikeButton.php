<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class LikeButton extends Component
{
    public Project $project;
    public bool $isLiked;
    public int $likesCount;

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->likesCount = $this->project->likes()->count();

        // Verificamos si el usuario actual ha dado "like" a este proyecto.
        // Es importante usar Auth::check() para que no de error si un visitante no logueado ve el botón.
        $this->isLiked = Auth::check() ? Auth::user()->likes()->where('project_id', $this->project->id)->exists() : false;
    }

    // Este método se llama cuando el usuario hace clic en el botón.
    public function toggleLike()
    {
        // Solo los usuarios autenticados pueden dar "like".
        if (!Auth::check()) {
            // Si no está logueado, lo redirigimos a la página de login.
            return $this->redirect(route('login'), navigate: true);
        }

        // Si el 'like' existe, lo quita. Si no existe, lo añade.
        Auth::user()->likes()->toggle($this->project->id);

        // Actualizamos el estado del componente para que la vista reaccione.
        $this->isLiked = !$this->isLiked;
        $this->likesCount = $this->project->likes()->count(); // Recontamos desde la BD para asegurar consistencia.
    }

    public function render()
    {
        return view('livewire.like-button');
    }
}