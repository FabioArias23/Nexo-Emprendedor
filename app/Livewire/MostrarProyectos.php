<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Component;

class MostrarProyectos extends Component
{
    public function render()
    {
        // 2. Consulta la base de datos para obtener los proyectos.
        // Usamos 'with' para cargar las fotos y evitar consultas N+1 (más eficiente).
        // Ordenamos por los más recientes.
        $proyectos = Project::with('photos')->latest()->get();

        // 3. Pasamos la colección de proyectos a la vista.
        return view('livewire.mostrar-proyectos', [
            'proyectos' => $proyectos
        ]);
    }
}