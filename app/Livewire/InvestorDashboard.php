<?php

namespace App\Livewire;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class InvestorDashboard extends Component
{
    public function render()
    {
        // 1. Proyectos disponibles para invertir
        $projects = Project::with('category', 'photos', 'likes')->latest()->get();

        // 2. Propuestas que el inversor actual ha enviado
        $myProposals = Auth::user()->investments()
                                   ->with('project') // Carga el proyecto de cada propuesta
                                   ->latest()
                                   ->get();

        return view('livewire.investor-dashboard', [
            'projects' => $projects,
            'myProposals' => $myProposals, // Pasamos las propuestas a la vista
        ]);
    }
}