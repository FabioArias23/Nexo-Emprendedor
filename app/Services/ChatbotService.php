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
        Log::info("🤖 ChatbotService - Pregunta recibida", ['pregunta' => $userInput]);

        if (empty($this->groqApiKey)) {
            return 'Lo siento, el servicio de chat no está configurado correctamente.';
        }

        // Detectar intención y obtener contexto
        $intent = $this->detectIntent($userInput);
        
        if ($intent === 'greeting' || $intent === 'meta_question') {
            $contextString = "El usuario no está preguntando por datos específicos.";
        } else {
            $questionEmbedding = $this->embeddingService->generate($userInput);

            if (!$questionEmbedding || !$this->embeddingService->isValidEmbedding($questionEmbedding)) {
                $contextString = 'No se pudo procesar la pregunta para buscar en la base de datos.';
            } else {
                $relevantProjects = $this->searchSimilarProjects($questionEmbedding);
                $contextString = $this->buildEnhancedContextString($relevantProjects, $userInput);
            }
        }

        $messages = $this->buildEnhancedPrompt($userInput, $contextString, $history, $intent);
        
        try {
            $response = Http::withToken($this->groqApiKey)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->timeout(30)
                ->withoutVerifying()
                ->post($this->groqApiUrl, [
                    'model' => $this->groqModel,
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 1500, // AUMENTADO para respuestas más largas
                    'stream' => false,
                ]);

            if ($response->successful()) {
                $responseContent = $response->json('choices.0.message.content', 'No pude procesar la respuesta.');
                
                // POST-PROCESAMIENTO MEJORADO
                $responseContent = $this->formatForTTS($responseContent);
                
                return $responseContent;
            }

            return 'Hubo un problema al contactar a nuestro asistente. Por favor, intenta de nuevo más tarde.';

        } catch (\Exception $e) {
            Log::error('💥 Excepción en ChatbotService', ['message' => $e->getMessage()]);
            return 'Ocurrió un error inesperado en el servicio de chat.';
        }
    }

    /**
     * FORMATO TTS MEJORADO - Especialmente para números y moneda argentina
     */
    private function formatForTTS(string $text): string
    {
        // 1. Eliminar markdown completamente
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        $text = preg_replace('/\*(.*?)\*/', '$1', $text);
        $text = preg_replace('/\#\#\# (.*)/', '$1', $text);
        $text = preg_replace('/\#\# (.*)/', '$1', $text);
        $text = preg_replace('/\# (.*)/', '$1', $text);
        
        // 2. Formatear números de dinero en PESOS ARGENTINOS - MEJORADO
        $text = preg_replace_callback('/\$\s*([0-9,]+(?:\.[0-9]+)?)/', function($matches) {
            $amount = $matches[1];
            
            // Remover comas y convertir a número
            $cleanAmount = str_replace(',', '', $amount);
            $number = floatval($cleanAmount);
            
            // Formatear para lectura natural en español
            if ($number == intval($number)) {
                // Número entero
                return $this->formatNumberToWords(intval($number)) . ' pesos';
            } else {
                // Número decimal - evitar "punto cero"
                $entero = intval($number);
                $decimal = round(($number - $entero) * 100);
                
                if ($decimal == 0) {
                    return $this->formatNumberToWords($entero) . ' pesos';
                } else {
                    return $this->formatNumberToWords($entero) . ' pesos con ' . $this->formatNumberToWords($decimal) . ' centavos';
                }
            }
        }, $text);
        
        // 3. Formatear números decimales genéricos (evitar "punto cero")
        $text = preg_replace_callback('/([0-9]+)\.0\b/', function($matches) {
            return $this->formatNumberToWords(intval($matches[1]));
        }, $text);
        
        $text = preg_replace_callback('/\b([0-9]+)\.([0-9]+)\b/', function($matches) {
            $entero = intval($matches[1]);
            $decimal = intval($matches[2]);
            return $this->formatNumberToWords($entero) . ' punto ' . $this->formatNumberToWords($decimal);
        }, $text);
        
        // 4. Reemplazar caracteres problemáticos
        $replacements = [
            '&' => 'y',
            '%' => ' por ciento',
            '#' => 'número',
            '~' => 'aproximadamente',
            '->' => 'hacia',
            '=>' => 'entonces',
            ' - ' => ', ',
            '•' => '-',
        ];
        
        $text = str_replace(array_keys($replacements), array_values($replacements), $text);
        
        // 5. Limpiar espacios múltiples y formato general
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/([.,!?])([A-Za-z])/', '$1 $2', $text);
        
        // 6. Acortar respuestas muy largas (para evitar truncamiento)
        if (strlen($text) > 2000) {
            $text = $this->summarizeLongResponse($text);
        }
        
        return trim($text);
    }

    /**
     * CONVERTIR NÚMEROS A PALABRAS - Especial para español
     */
    private function formatNumberToWords(int $number): string
    {
        if ($number == 0) return 'cero';
        
        $units = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
        $teens = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
        $tens = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
        $hundreds = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];
        
        if ($number < 10) return $units[$number];
        if ($number < 20) return $teens[$number - 10];
        if ($number < 100) {
            $ten = floor($number / 10);
            $unit = $number % 10;
            
            if ($ten == 2 && $unit > 0) return 'veinti' . $units[$unit];
            if ($unit == 0) return $tens[$ten];
            return $tens[$ten] . ' y ' . $units[$unit];
        }
        if ($number < 1000) {
            $hundred = floor($number / 100);
            $remainder = $number % 100;
            
            if ($hundred == 1 && $remainder == 0) return 'cien';
            if ($remainder == 0) return $hundreds[$hundred];
            return $hundreds[$hundred] . ' ' . $this->formatNumberToWords($remainder);
        }
        if ($number < 1000000) {
            $thousand = floor($number / 1000);
            $remainder = $number % 1000;
            
            $thousandText = $thousand == 1 ? 'mil' : $this->formatNumberToWords($thousand) . ' mil';
            
            if ($remainder == 0) return $thousandText;
            return $thousandText . ' ' . $this->formatNumberToWords($remainder);
        }
        if ($number < 1000000000) {
            $million = floor($number / 1000000);
            $remainder = $number % 1000000;
            
            $millionText = $million == 1 ? 'un millón' : $this->formatNumberToWords($million) . ' millones';
            
            if ($remainder == 0) return $millionText;
            return $millionText . ' ' . $this->formatNumberToWords($remainder);
        }
        
        return strval($number); // Fallback para números muy grandes
    }

    /**
     * RESUMIR RESPUESTAS MUY LARGAS para evitar truncamiento
     */
    private function summarizeLongResponse(string $text): string
    {
        // Si es muy largo, tomar los primeros 1900 caracteres y agregar indicación
        if (strlen($text) > 2000) {
            $text = substr($text, 0, 1900);
            
            // Encontrar el último punto para cortar en una oración completa
            $lastSentence = strrpos($text, '.');
            if ($lastSentence !== false && $lastSentence > 1500) {
                $text = substr($text, 0, $lastSentence + 1);
            }
            
            $text .= '... Si necesitas más detalles, pregunta sobre aspectos específicos.';
        }
        
        return $text;
    }

    /**
     * PROMPT MEJORADO - Instrucciones más específicas para formato
     */
    private function buildEnhancedPrompt(string $userInput, string $contextString, array $history, string $intent): array
    {
        $personality = $this->getPersonalityByIntent($intent);
        
        $systemPrompt = <<<PROMPT
        Eres "NexoBot", un asistente de IA especializado en inversiones y emprendimientos para la plataforma "Nexo Emprendedor".

        {$personality['role']}

        REGLAS ESTRICTAS DE FORMATO Y ESTILO:
        1. **NUNCA uses markdown** - sin negritas, cursivas, viñetas con asteriscos, o símbolos especiales
        2. **Formato de números en PESOS ARGENTINOS**:
           - En lugar de "$10,000.00" escribe "diez mil pesos"
           - En lugar de "$1,500,000" escribe "un millón quinientos mil pesos"  
           - En lugar de "$2,500.50" escribe "dos mil quinientos pesos con cincuenta centavos"
           - NUNCA escribas "10.0" o números con ".0" - siempre escribe el número en palabras
        3. **Estructura clara pero natural**:
           - Usa párrafos cortos
           - Separa ideas con puntos y seguido, no con guiones o asteriscos
           - Usa lenguaje conversacional como si estuvieras hablando
        4. **Longitud adecuada**: Sé conciso pero completo. Responde en 100-200 palabras máximo.
        5. **Evita símbolos técnicos**: No uses ->, =>, #, ~, •, - para listas
        6. **Moneda**: Siempre usa PESOS, no dólares

        REGLAS DE CONTENIDO:
        1. Fundamenta tus respuestas ESTRICTAMENTE en el CONTEXTO proporcionado
        2. Sé proactivo: ofrece ayuda adicional o preguntas de seguimiento
        3. Para comparaciones: destaca diferencias clave de manera objetiva
        4. Para recomendaciones: basa en datos concretos, no en opiniones personales
        5. Siempre habla en español

        CONTEXTO ACTUAL:
        {$contextString}
        PROMPT;

        // Historial de conversación
        $apiHistory = [];
        foreach ($history as $entry) {
            $role = $entry['sender'] === 'bot' ? 'assistant' : 'user';
            $apiHistory[] = ['role' => $role, 'content' => $entry['text']];
        }

        // Mensaje del usuario con guía específica
        $userMessage = "Pregunta del usuario: {$userInput}\n\n";
        $userMessage .= $personality['guidance'];
        $userMessage .= "\n\nRecuerda: Usa siempre palabras para los números y evita símbolos de formato.";

        return array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $apiHistory,
            [['role' => 'user', 'content' => $userMessage]]
        );
    }

    // Los demás métodos permanecen igual (detectIntent, getPersonalityByIntent, searchSimilarProjects, etc.)
    private function detectIntent(string $userInput): string
    {
        $lowerInput = strtolower(trim($userInput));
        
        $greetings = ['hola', 'buenos días', 'buenas tardes', 'buenas noches', 'qué tal', 'como estas', 'hi', 'hello', 'hey', 'saludos'];
        foreach ($greetings as $word) {
            if (str_contains($lowerInput, $word)) return 'greeting';
        }
        
        $meta_questions = ['quién eres', 'qué puedes hacer', 'ayuda', 'cómo funcionas', 'qué eres', 'para qué sirves'];
        foreach ($meta_questions as $word) {
            if (str_contains($lowerInput, $word)) return 'meta_question';
        }
        
        $comparison_keywords = ['comparar', 'comparación', 'diferencia', 'diferencias', 'vs', 'versus', 'mejor', 'peor'];
        foreach ($comparison_keywords as $word) {
            if (str_contains($lowerInput, $word)) return 'comparison';
        }
        
        $recommendation_keywords = ['recomendar', 'recomendación', 'sugerir', 'sugerencia', 'conviene', 'conveniente', 'me conviene'];
        foreach ($recommendation_keywords as $word) {
            if (str_contains($lowerInput, $word)) return 'recommendation';
        }
        
        $analysis_keywords = ['por qué', 'razón', 'razones', 'motivo', 'motivos', 'análisis', 'analizar', 'detalle', 'detalles', 'riesgo', 'riesgos'];
        foreach ($analysis_keywords as $word) {
            if (str_contains($lowerInput, $word)) return 'analysis';
        }

        return 'data_query';
    }

    private function getPersonalityByIntent(string $intent): array
    {
        $personalities = [
            'greeting' => [
                'role' => "Eres un asistente cálido y entusiasta que da la bienvenida a los usuarios.",
                'guidance' => "Saluda amablemente y presenta brevemente cómo puedes ayudar con proyectos e inversiones."
            ],
            'comparison' => [
                'role' => "Eres un analista financiero especializado en comparar proyectos de inversión.",
                'guidance' => "Compara los proyectos de manera objetiva, destacando diferencias clave de forma clara y natural."
            ],
            'recommendation' => [
                'role' => "Eres un asesor de inversiones experimentado que ayuda a elegir entre opciones.",
                'guidance' => "Proporciona recomendaciones basadas en datos concretos. Explica tus razones claramente."
            ],
            'analysis' => [
                'role' => "Eres un analista estratégico que profundiza en los detalles de los proyectos.",
                'guidance' => "Analiza en profundidad los proyectos, considerando factores clave de manera objetiva."
            ],
            'data_query' => [
                'role' => "Eres un investigador meticuloso que encuentra y presenta información relevante.",
                'guidance' => "Presenta la información de manera clara y organizada."
            ],
            'meta_question' => [
                'role' => "Eres un asistente útil que explica tus capacidades de manera clara.",
                'guidance' => "Explica qué puedes hacer y cómo puedes ayudar de forma concisa."
            ]
        ];

        return $personalities[$intent] ?? $personalities['data_query'];
    }

    private function buildEnhancedContextString($projects, string $userQuestion): string
    {
        if ($projects->isEmpty()) {
            return "No se encontró información relevante en la base de datos sobre la consulta del usuario.";
        }
        
        $context = "INFORMACIÓN DETALLADA DE PROYECTOS RELEVANTES:\n\n";
        
        foreach ($projects as $index => $project) {
            $categoryName = $project->category ? $project->category->name : 'Sin categoría';
            
            $context .= "PROYECTO " . ($index + 1) . ":\n";
            $context .= "Título: {$project->title}\n";
            $context .= "Categoría: {$categoryName}\n";
            $context .= "Descripción: {$project->description}\n";
            $context .= "Modelo de Negocio: {$project->business_model}\n";
            $context .= "Potencial de Mercado: {$project->market_potential}\n";
            $context .= "Meta de Financiación: {$project->funding_goal} pesos\n";
            $context .= "Inversión Mínima: {$project->min_investment} pesos\n";
            $context .= "---\n\n";
        }
        
        $context .= "CONTEXTO DE LA CONSULTA:\n";
        $context .= "El usuario preguntó: \"{$userQuestion}\"\n";
        $context .= "Número de proyectos encontrados: " . $projects->count() . "\n";
        
        return trim($context);
    }

    private function searchSimilarProjects(array $questionEmbedding)
    {
        try {
            return Project::query()
                ->with('category')
                ->whereNotNull('embedding')
                ->orderByRaw('embedding <=> ?', [json_encode($questionEmbedding)])
                ->take(3)
                ->get();
        } catch (\Exception $e) {
            return Project::with('category')->latest()->take(2)->get();
        }
    }
}