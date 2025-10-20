<?php

namespace App\Livewire\Project;

use App\Models\Project;
use Livewire\Component;

class View extends Component
{
    public Project $project;

    // Gracias al route-model binding, Laravel inyecta automáticamente
    // el objeto Project que corresponde al ID en la URL.
    public function mount(Project $project)
    {
        // Cargamos todas las relaciones que vamos a mostrar para optimizar las consultas.
        $this->project = $project->load('category', 'photos', 'entrepreneur');
    }

    public function render()
    {
        return view('livewire.project.view');
    }
}