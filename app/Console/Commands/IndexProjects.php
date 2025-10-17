<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Project;

class IndexProjects extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'projects:index';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Genera y guarda embeddings para todos los proyectos usando el modelo BGE.';
    
    /**
     * BAAI/bge-base-en-v1.5 es un estándar de la industria para RAG. Produce vectores de 768 dimensiones.
     */
    private const EMBEDDING_API_URL = 'https://api-inference.huggingface.co/models/BAAI/bge-base-en-v1.5';

    public function handle(): int
    {
        $this->info('🚀 Iniciando indexación de proyectos con el modelo BGE...');

        $apiToken = config('services.huggingface.api_key'); // Lo tomamos de config/services.php
        if (empty($apiToken)) {
            $this->error('CRÍTICO: La API Key de Hugging Face no está configurada en config/services.php o .env.');
            return self::FAILURE;
        }

        $projects = Project::with('category')->get(); // Obtenemos todos los proyectos de la BD

        if ($projects->isEmpty()) {
            $this->warn('No se encontraron proyectos en la base de datos. Nada que indexar.');
            return self::SUCCESS;
        }

        $this->info('1/2 - Iniciando generación de embeddings para ' . $projects->count() . ' proyectos...');
        $this->output->progressStart($projects->count());

        foreach ($projects as $project) {
            // Creamos el "documento" a partir de los campos más relevantes del proyecto.
            // Esto es nuestro "chunk" de información.
            $textToEmbed = "Título del proyecto: {$project->title}. "
                         . "Categoría: {$project->category->name}. "
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
                    
                    // Guardamos el embedding directamente en el proyecto
                    $project->embedding = $embedding;
                    $project->save();

                } else {
                    $this->error(" -> Fallo para el proyecto ID {$project->id}: " . $response->body());
                }

            } catch (\Exception $e) {
                $this->error(" -> Excepción de conexión para el proyecto ID {$project->id}: " . $e->getMessage());
            }
            
            $this->output->progressAdvance();
            usleep(250000); // Mantenemos la pausa para no saturar la API
        }

        $this->output->progressFinish();
        $this->info('2/2 - ✅ Proceso de indexación de proyectos completado.');
        return self::SUCCESS;
    }
}