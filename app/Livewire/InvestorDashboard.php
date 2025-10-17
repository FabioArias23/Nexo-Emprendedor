<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;

class InvestorDashboard extends Component
{
    public function render()
    {
        // Obtenemos todos los proyectos, cargando de forma anticipada (eager loading)
        // las relaciones 'entrepreneur' y 'photos' para evitar consultas N+1.
        $projects = Project::with('entrepreneur', 'photos')->latest()->get();

        return view('livewire.investor-dashboard', [
            'projects' => $projects,
        ]);
    }
}