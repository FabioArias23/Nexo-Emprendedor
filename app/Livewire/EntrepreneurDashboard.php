<?php

namespace App\Livewire;

use App\Models\Investment;
use App\Models\Notification;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class EntrepreneurDashboard extends Component
{
    public function delete(Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        foreach ($project->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }

        $project->delete();
        
        session()->flash('message', 'Proyecto eliminado exitosamente.');
    }

    /**
     * Acepta una propuesta de inversión, cambiando su estado a 'negotiating'.
     */
    public function acceptProposal(Investment $investment)
    {
        // Medida de seguridad: Asegurarnos de que el emprendedor solo puede
        // aceptar propuestas dirigidas a sus propios proyectos.
        if ($investment->project->user_id !== Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        $investment->status = 'negotiating';
        $investment->save();

        Notification::create([
            'user_id' => $investment->investor_id, // El inversor que hizo la propuesta
            'message' => "¡Buenas noticias! Tu propuesta para '{$investment->project->title}' ha sido aceptada. Ya puedes iniciar la conversación.",
            'link' => route('conversation.show', $investment), // Enlace directo a la conversación
        ]);

        session()->flash('message', '¡Propuesta aceptada! Ahora puedes comunicarte con el inversor.');
    }

    /**
     * Rechaza una propuesta de inversión.
     */
    public function rejectProposal(Investment $investment)
    {
        // Medida de seguridad similar.
        if ($investment->project->user_id !== Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        $investment->status = 'rejected';
        $investment->save();
        
        session()->flash('message', 'La propuesta ha sido rechazada.');
    }

    public function render()
    {
        $user = Auth::user();

        // Obtenemos los proyectos del usuario.
        $projects = $user->projects()->with('category')->latest()->get();

        // Obtenemos las propuestas recibidas a través de la nueva relación,
        // cargando también el proyecto y el inversor de cada propuesta para mostrarlos.
        $proposals = $user->proposals()
                          ->with(['project', 'investor'])
                          ->latest()
                          ->get();

        return view('livewire.entrepreneur-dashboard', [
            'projects' => $projects,
            'proposals' => $proposals,
        ]);
    }
}