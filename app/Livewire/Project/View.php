<?php

namespace App\Livewire\Project;

use App\Models\Investment;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class View extends Component
{
    public Project $project;
    public bool $hasProposed = false;

    public function mount(Project $project)
    {
        $this->project = $project->load('category', 'photos', 'entrepreneur');

        // Verificar si el usuario ya ha hecho una propuesta
        if (Auth::check() && Auth::user()->role === 'investor') {
            $this->hasProposed = Investment::where('project_id', $this->project->id)
                                           ->where('investor_id', Auth::id())
                                           ->exists();
        }
    }

    public function render()
    {
        return view('livewire.project.view');
    }
}