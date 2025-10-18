<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

class EmbeddingService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.huggingface.api_key');
    }

    public function generate(string $text): ?array
    {
        // Un modelo excelente y ligero para embeddings
        $model = 'BAAI/bge-base-en-v1.5';
        $apiUrl = "https://api-inference.huggingface.co/pipeline/feature-extraction/{$model}";

        $response = Http::withToken($this->apiKey)
            ->withoutVerifying()
            ->post($apiUrl, [
                'inputs' => $text,
                'options' => ['wait_for_model' => true]
            ]);

        if ($response->successful()) {
            return $response->json();
        }
        return null;
    }
}