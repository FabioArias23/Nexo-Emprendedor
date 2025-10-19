<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.huggingface.api_key');
    }

    public function generate(string $text): ?array
    {
        $model = 'BAAI/bge-base-en-v1.5';
        $apiUrl = "https://api-inference.huggingface.co/models/{$model}";

        try {
            Log::info("🔧 Generando embedding para texto", [
                'text_length' => strlen($text),
                'text_preview' => substr($text, 0, 100) . '...',
                'model' => $model,
                'url' => $apiUrl
            ]);

            $response = Http::withToken($this->apiKey)
                ->timeout(60)
                ->withoutVerifying()
                ->post($apiUrl, [
                    'inputs' => $text,
                    'options' => [
                        'wait_for_model' => true,
                        'use_cache' => true
                    ]
                ]);

            if ($response->successful()) {
                $embedding = $response->json();
                
                Log::info("✅ Embedding generado exitosamente", [
                    'dimensions' => count($embedding),
                    'first_few_values' => array_slice($embedding, 0, 5)
                ]);
                
                return $embedding;
            }

            Log::error('❌ Error en EmbeddingService - Respuesta no exitosa', [
                'status' => $response->status(),
                'body' => $response->body(),
                'text_preview' => substr($text, 0, 100),
                'model' => $model,
                'url' => $apiUrl
            ]);

            // Intentar con modelo alternativo si el principal falla
            if ($response->status() === 404) {
                return $this->tryAlternativeModel($text);
            }

        } catch (\Exception $e) {
            Log::error('💥 Excepción en EmbeddingService', [
                'message' => $e->getMessage(),
                'text_preview' => substr($text, 0, 100)
            ]);
        }

        return null;
    }

    /**
     * Método de respaldo con modelo alternativo
     */
    private function tryAlternativeModel(string $text): ?array
    {
        Log::info("🔄 Intentando con modelo alternativo...");
        
        $alternativeModel = 'sentence-transformers/all-mpnet-base-v2';
        $apiUrl = "https://api-inference.huggingface.co/models/{$alternativeModel}";

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(60)
                ->withoutVerifying()
                ->post($apiUrl, [
                    'inputs' => $text,
                    'options' => [
                        'wait_for_model' => true,
                        'use_cache' => true
                    ]
                ]);

            if ($response->successful()) {
                $embedding = $response->json();
                Log::info("✅ Embedding generado con modelo alternativo", [
                    'model' => $alternativeModel,
                    'dimensions' => count($embedding)
                ]);
                return $embedding;
            }

            Log::error('❌ Modelo alternativo también falló', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

        } catch (\Exception $e) {
            Log::error('💥 Excepción en modelo alternativo', [
                'message' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Verifica si el embedding tiene el formato correcto
     */
    public function isValidEmbedding($embedding): bool
    {
        return is_array($embedding) && 
               count($embedding) > 100 && // Al menos 100 dimensiones (los modelos varían)
               is_numeric($embedding[0]);
    }
}