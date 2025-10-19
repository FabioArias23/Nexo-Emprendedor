<?php

namespace App\Observers;

use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProjectObserver
{
    private const EMBEDDING_API_URL = 'https://api-inference.huggingface.co/models/BAAI/bge-base-en-v1.5';

    /**
     * Handle the Project "created" event.
     * Se dispara justo después de que un proyecto es creado.
     */
    public function created(Project $project): void
    {
        $this->generateAndSaveEmbedding($project);
    }

    /**
     * Handle the Project "updated" event.
     * Se dispara justo después de que un proyecto es actualizado.
     */
    public function updated(Project $project): void
    {
        // Solo regeneramos el embedding si los campos de texto importantes han cambiado.
        // Esto evita llamadas innecesarias a la API si solo se cambia, por ejemplo, el monto.
        if ($project->isDirty(['title', 'description', 'category_id', 'business_model', 'market_potential'])) {
            $this->generateAndSaveEmbedding($project);
        }
    }

    /**
     * Método centralizado para generar y guardar el embedding.
     */
    private function generateAndSaveEmbedding(Project $project): void
    {
        $apiToken = config('services.huggingface.api_key');
        if (empty($apiToken)) {
            Log::error('Observer: La API Key de Hugging Face no está configurada.');
            return;
        }

        // Carga la relación 'category' si no está ya cargada.
        $project->loadMissing('category');

        $categoryName = $project->category?->name ?? 'No especificada';

        $textToEmbed = "Título del proyecto: {$project->title}. "
                     . "Categoría: {$categoryName}. "
                     . "Descripción: {$project->description}. "
                     . "Modelo de negocio: {$project->business_model}. "
                     . "Potencial de mercado: {$project->market_potential}.";

        try {
            $response = Http::withToken($apiToken)
                            ->timeout(60)
                            ->post(self::EMBEDDING_API_URL, [
                                'inputs' => $textToEmbed,
                                'options' => ['wait_for_model' => true]
                            ]);

            if ($response->successful()) {
                $embedding = $response->json();
                
                // Usamos saveQuietly() para guardar el modelo sin disparar
                // de nuevo los eventos 'updated', evitando un bucle infinito.
                $project->embedding = $embedding;
                $project->saveQuietly();

            } else {
                Log::error("Observer: Fallo al generar embedding para el proyecto ID {$project->id}: " . $response->body());
            }

        } catch (\Exception $e) {
            Log::error("Observer: Excepción para el proyecto ID {$project->id}: " . $e->getMessage());
        }
    }
}