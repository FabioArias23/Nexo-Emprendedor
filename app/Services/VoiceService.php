<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoiceService
{
    private ?string $hfApiKey;
    private ?string $azureApiKey;
    private ?string $azureRegion;

    public function __construct()
    {
        $this->hfApiKey = config('services.huggingface.api_key');
        $this->azureApiKey = config('services.azure.speech_key');
        $this->azureRegion = config('services.azure.speech_region');
    }

    /**
     * Transcribe audio a texto usando Hugging Face (Whisper), que es flexible con los formatos.
     */
    public function transcribeAudio(string $audioFilePath): ?string
    {
        if (empty($this->hfApiKey)) {
            Log::error('Hugging Face API Key no está configurada para STT.');
            return null;
        }

        $model = 'openai/whisper-large-v3';
        $apiUrl = "https://api-inference.huggingface.co/models/{$model}";

        try {
            $response = Http::withToken($this->hfApiKey)
                ->timeout(60)
                ->withBody(file_get_contents($audioFilePath), 'audio/webm') // Enviamos el .webm directamente
                ->withoutVerifying()
                ->post($apiUrl, ['options' => ['wait_for_model' => true]]);

            if ($response->successful()) {
                return $response->json('text');
            }

            Log::error('HuggingFace STT Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Excepción en HuggingFace STT', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Sintetiza texto a voz usando Azure AI Text to Speech para alta calidad.
     */
    public function synthesizeSpeech(string $text): ?string
    {
        if (empty($this->azureApiKey) || empty($this->azureRegion)) {
            Log::error('Credenciales de Azure no están configuradas para TTS.');
            return null;
        }

        $endpoint = "https://{$this->azureRegion}.tts.speech.microsoft.com/cognitiveservices/v1";
        $ssml = "<speak version='1.0' xmlns='http://www.w3.org/2001/10/synthesis' xml:lang='es-ES'><voice name='es-ES-ElviraNeural'>" . htmlspecialchars($text) . "</voice></speak>";

        try {
            $response = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $this->azureApiKey,
                'Content-Type' => 'application/ssml+xml',
                'X-Microsoft-OutputFormat' => 'audio-16khz-128kbitrate-mono-mp3',
            ])
            ->withBody($ssml, 'application/ssml+xml')
            ->timeout(60)
            ->withoutVerifying()
            ->post($endpoint);

            if ($response->successful() && str_contains($response->header('Content-Type'), 'audio')) {
                return $response->body();
            }
            
            Log::error('Azure TTS Error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Excepción en Azure TTS', ['message' => $e->getMessage()]);
            return null;
        }
    }
}