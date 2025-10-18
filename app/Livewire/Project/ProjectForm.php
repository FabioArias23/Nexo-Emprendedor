<?php

namespace App\Livewire\Project;

use App\Models\Project;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProjectForm extends Component
{
    use WithFileUploads;

    public Project $project; // <-- Hacemos que la propiedad no sea nullable

    // Propiedades del formulario
    public string $title = '';
    public string $description = '';
    public string $industry = '';
    public $funding_goal = '';
    public $min_investment = '';
    public string $business_model = '';
    public string $market_potential = '';

    // Propiedad para la subida de fotos
    public $photo;

    // Usamos el model binding de Livewire.
    // Para la ruta 'create', inyectará un Project vacío.
    // Para la ruta 'edit', inyectará el Project existente.
    public function mount(Project $project)
    {
        $this->project = $project;
        
        // Si el proyecto ya existe en la BD, llenamos el formulario con sus datos.
        if ($this->project->exists) {
            $this->title = $project->title;
            $this->description = $project->description;
            $this->industry = $project->industry;
            $this->funding_goal = $project->funding_goal;
            $this->min_investment = $project->min_investment;
            $this->business_model = $project->business_model;
            $this->market_potential = $project->market_potential;
        }
    }

    public function save()
    {
        // Política de seguridad: si estamos editando, nos aseguramos de que el usuario sea el dueño.
        if ($this->project->exists && $this->project->user_id !== auth()->id()) {
            abort(403);
        }

        $validatedData = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'industry' => 'required|string|max:255',
            'funding_goal' => 'required|numeric|min:0',
            'min_investment' => 'required|numeric|min:0',
            'business_model' => 'required|string',
            'market_potential' => 'required|string',
            'photo' => 'nullable|image|max:2048', // 2MB Max
        ]);

        $projectData = collect($validatedData)->except('photo')->toArray();
        $projectData['user_id'] = auth()->id();
        
        // updateOrCreate es perfecto para este caso de uso.
        $savedProject = Project::updateOrCreate(
            ['id' => $this->project->id], // Si el id es null, creará un nuevo registro.
            $projectData
        );

        if ($this->photo) {
            $path = $this->photo->store('project-photos', 'public');
            // Borramos fotos antiguas si existen para no acumular basura
            $savedProject->photos()->delete(); 
            $savedProject->photos()->create(['path' => $path]);
        }

        session()->flash('message', 'Proyecto guardado exitosamente.');
        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.project.project-form');
    }
}