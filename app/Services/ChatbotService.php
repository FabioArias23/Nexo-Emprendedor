<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    private string $groqApiKey;
    private string $groqModel;
    private string $groqApiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    
    private EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
        $this->groqApiKey = config('services.groq.api_key');
        $this->groqModel = config('services.groq.model');
    }

    public function generateResponse(string $userInput, array $history = []): string
    {
        if (empty($this->groqApiKey)) {
            return 'Lo siento, el servicio de chat no está configurado correctamente.';
        }

        // --- INGENIERÍA DE PROMPTS - TÉCNICA 1: MANEJO DE INTENCIONES ---
        $intent = $this->detectIntent($userInput);

        // Si es un saludo o una pregunta genérica, no buscamos en la BD.
        if ($intent === 'greeting' || $intent === 'meta_question') {
            $contextString = "El usuario no está preguntando por datos específicos, sino saludando o preguntando sobre ti. Responde amablemente.";
        } else {
            // Solo si es una búsqueda de datos, ejecutamos la lógica RAG.
            $questionEmbedding = $this->embeddingService->generate($userInput);

            if (!$questionEmbedding) {
                Log::warning('No se pudo generar el embedding para la pregunta del usuario.', ['input' => $userInput]);
                $contextString = 'No se pudo procesar la pregunta para buscar en la base de datos.';
            } else {
                $relevantProjects = Project::query()
                    ->with('category')
                    ->orderByRaw('embedding <=> ?', [json_encode($questionEmbedding)])
                    ->take(3)
                    ->get();
                
                $contextString = $this->buildContextString($relevantProjects);
            }
        }

        $messages = $this->buildAugmentedPrompt($userInput, $contextString, $history);
        
        try {
            $response = Http::withToken($this->groqApiKey)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->timeout(30)
                ->withoutVerifying()
                ->post($this->groqApiUrl, [
                    'model' => $this->groqModel,
                    'messages' => $messages,
                    'temperature' => 0.5, // Un poco menos creativo para respuestas más factuales
                    'max_tokens' => 400,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content', 'No pude procesar la respuesta.');
            }

            Log::error('Error en la API de Groq', ['status' => $response->status(), 'body' => $response->json()]);
            return 'Hubo un problema al contactar a nuestro asistente. Por favor, intenta de nuevo más tarde.';

        } catch (\Exception $e) {
            Log::error('Excepción en ChatbotService', ['message' => $e->getMessage()]);
            return 'Ocurrió un error inesperado en el servicio de chat.';
        }
    }

    /**
     * --- INGENIERÍA DE PROMPTS - TÉCNICA 1 (continuación) ---
     * Detección de intención simple basada en palabras clave.
     */
    private function detectIntent(string $userInput): string
    {
        $greetings = ['hola', 'buenos días', 'buenas tardes', 'qué tal', 'como estas'];
        $meta_questions = ['quién eres', 'qué puedes hacer', 'ayuda', 'cómo funcionas'];

        $lowerInput = strtolower($userInput);

        foreach ($greetings as $word) {
            if (str_contains($lowerInput, $word)) return 'greeting';
        }
        foreach ($meta_questions as $word) {
            if (str_contains($lowerInput, $word)) return 'meta_question';
        }

        return 'data_query'; // Por defecto, asumimos que es una búsqueda de datos.
    }

    private function buildContextString($projects): string
    {
        if ($projects->isEmpty()) {
            return "No se encontró información relevante en la base de datos sobre la consulta del usuario.";
        }
        $context = "Información relevante de la base de datos:\n";
        foreach ($projects as $project) {
            $context .= "- Proyecto: {$project->title} (Categoría: {$project->category->name}). Descripción: {$project->description}. Meta de Financiación: $" . number_format($project->funding_goal, 2) . ".\n";
        }
        return trim($context);
    }
    
    /**
     * --- EL CORAZÓN DE LA INGENIERÍA DE PROMPTS ---
     */
    private function buildAugmentedPrompt(string $userInput, string $contextString, array $history): array
    {
        // --- TÉCNICA 2: PERSONA Y REGLAS (SYSTEM PROMPT MEJORADO) ---
        $systemPrompt = <<<PROMPT
        Eres "NexoBot", un asistente de IA profesional, proactivo y conciso para la plataforma "Nexo Emprendedor".
        Tu misión es ayudar a los usuarios a encontrar información sobre proyectos e inversores.

        REGLAS ESTRICTAS:
        1.  **Fundamenta tus respuestas en el CONTEXTO.** Tu conocimiento se limita a la información que te proporciono en cada turno.
        2.  **Si el contexto no contiene la respuesta, di "No tengo información específica sobre eso."** y luego ofrece ayuda de forma proactiva (ej: "¿Te gustaría buscar proyectos de otra categoría?"). NO inventes información. NO te disculpes en exceso.
        3.  **Sé conciso y directo.** Ve al grano. Usa listas con viñetas para presentar datos de múltiples proyectos.
        4.  **Si el usuario saluda o pregunta por ti**, responde amablemente y re-enfoca la conversación hacia la funcionalidad de la plataforma. (ej: "¡Hola! Soy NexoBot, tu asistente virtual. ¿Estás buscando proyectos para invertir o necesitas ayuda para publicar tu idea?").
        5.  Habla siempre en español.
        PROMPT;

        // --- TÉCNICA 3: INSTRUCCIÓN Y EJEMPLO (FEW-SHOT PROMPTING) ---
        $augmentedUserPrompt = <<<PROMPT
        --- CONTEXTO PROPORCIONADO ---
        {$contextString}
        --- FIN DEL CONTEXTO ---
        
        --- EJEMPLO DE RESPUESTA IDEAL (sigue este formato) ---
        Pregunta de ejemplo: "Háblame de proyectos de tecnología"
        Respuesta ideal de ejemplo: 
        "Claro, he encontrado estos proyectos de tecnología:
        - Proyecto: EcoAgro Formosa (Categoría: Tecnología). Descripción: Una plataforma innovadora para conectar productores... Meta de Financiación: $50,000.00.
        - Proyecto: Otro Proyecto Tech...
        ¿Te gustaría profundizar en alguno de ellos?"
        --- FIN DEL EJEMPLO ---

        Basándote ESTRICTAMENTE en el contexto y siguiendo las reglas y el ejemplo, responde a la siguiente pregunta del usuario: "{$userInput}"
        PROMPT;

        $apiHistory = [];
        foreach ($history as $entry) {
            $role = $entry['sender'] === 'bot' ? 'assistant' : 'user';
            $apiHistory[] = ['role' => $role, 'content' => $entry['text']];
        }

        return array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $apiHistory,
            [['role' => 'user', 'content' => $augmentedUserPrompt]]
        );
    }
}