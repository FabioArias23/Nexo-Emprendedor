<?php

namespace App\Livewire\Investment;

use App\Models\Investment;
use App\Models\Project;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class ProposalModal extends Component
{
    public bool $showModal = false;
    public ?Project $project = null;

    public $proposed_amount = '';
    public string $message = '';

    // Este método escucha el evento que disparamos desde el botón
    #[On('open-proposal-modal')]
    public function openModal($projectId)
    {
        $this->project = Project::find($projectId);
        
        // Sugerimos el monto mínimo como valor por defecto.
        $this->proposed_amount = $this->project->min_investment;
        $this->message = ''; // Reseteamos el mensaje
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function save()
    {
        if (!Auth::check() || Auth::user()->role !== 'investor') {
            return; // Medida de seguridad
        }

        $this->validate([
            'proposed_amount' => 'required|numeric|min:' . $this->project->min_investment,
            'message' => 'required|string|min:20|max:1000',
        ]);

        Investment::create([
            'project_id' => $this->project->id,
            'investor_id' => Auth::id(),
            'proposed_amount' => $this->proposed_amount,
            'message' => $this->message,
            'status' => 'pending', // estado inicial
        ]);

        Notification::create([
            'user_id' => $this->project->user_id, // El dueño del proyecto
            'message' => "Has recibido una nueva propuesta de inversión de " . Auth::user()->name . " para tu proyecto '{$this->project->title}'.",
            'link' => route('dashboard'), // Lo lleva a su dashboard para que vea la propuesta
        ]);

        $this->closeModal();

        // Enviamos un evento para notificar al usuario que todo salió bien
        $this->dispatch('proposal-sent'); 
    }

    public function render()
    {
        return view('livewire.investment.proposal-modal');
    }
}