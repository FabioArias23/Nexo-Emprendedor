<?php

namespace App\Livewire;

use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class EntrepreneurDashboard extends Component
{
    public function delete(Project $project)
    {
        // Política de seguridad: asegúrate de que el usuario solo pueda borrar sus propios proyectos.
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        // Borra las fotos asociadas del almacenamiento
        foreach ($project->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }

        $project->delete();
        
        session()->flash('message', 'Proyecto eliminado exitosamente.');
    }

    public function render()
    {
        // Obtenemos solo los proyectos del usuario autenticado.
        $projects = Project::where('user_id', auth()->id())->latest()->get();

        return view('livewire.entrepreneur-dashboard', [
            'projects' => $projects,
        ]);
    }
}