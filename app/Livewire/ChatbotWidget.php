<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\ChatbotService;
use App\Services\VoiceService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ChatbotWidget extends Component
{
    public bool $isOpen = false;
    public string $currentMessage = '';
    public array $messages = [];
    public bool $isLoading = false;

    public function mount()
    {
        $this->messages[] = [
            'sender' => 'bot',
            'text' => '¡Hola! Soy tu asistente virtual. Puedes escribirme o usar el micrófono para hablar. ¿Cómo puedo ayudarte?'
        ];
    }

    public function toggleChat()
    {
        $this->isOpen = !$this->isOpen;
        $this->dispatch('chat-toggled', isOpen: $this->isOpen);
    }

    public function sendMessage(ChatbotService $chatbotService, VoiceService $voiceService)
    {
        if (trim($this->currentMessage) === '') {
            return;
        }
        $this->messages[] = ['sender' => 'user', 'text' => $this->currentMessage];
        $this->isLoading = true;
        $userMessage = $this->currentMessage;
        $this->reset('currentMessage');
        
        $history = array_slice($this->messages, -6);
        $botResponseText = $chatbotService->generateResponse($userMessage, $history);

        $this->messages[] = ['sender' => 'bot', 'text' => $botResponseText];
        $this->isLoading = false;
        $this->dispatch('message-sent');

        if ($botResponseText && trim($botResponseText) !== '') {
            $this->sendAudioResponse($botResponseText, $voiceService);
        }
    }

    /**
     * Procesa el audio grabado en el frontend.
     */
    public function processAudio(string $audioBase64, VoiceService $voiceService, ChatbotService $chatbotService)
    {
        $this->isLoading = true;

        try {
            $audioData = base64_decode($audioBase64);
            $tempDir = storage_path('app/temp');
            if (!File::isDirectory($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }
            $tempPath = $tempDir . '/' . Str::random(16) . '.webm';
            File::put($tempPath, $audioData);

            $transcribedText = $voiceService->transcribeAudio($tempPath);
            File::delete($tempPath);

            $botResponseText = '';
            if (!$transcribedText || trim($transcribedText) === '') {
                $botResponseText = 'Lo siento, no pude entender lo que dijiste. ¿Podrías intentarlo de nuevo?';
                $this->messages[] = ['sender' => 'bot', 'text' => $botResponseText];
            } else {
                $this->messages[] = ['sender' => 'user', 'text' => $transcribedText];
                $this->dispatch('message-sent');
                
                $history = array_slice($this->messages, -6);
                $botResponseText = $chatbotService->generateResponse($transcribedText, $history);
                $this->messages[] = ['sender' => 'bot', 'text' => $botResponseText];
            }
            
            $this->isLoading = false;
            $this->dispatch('message-sent');

            if ($botResponseText && trim($botResponseText) !== '') {
                $this->sendAudioResponse($botResponseText, $voiceService);
            }
        } catch (\Exception $e) {
            Log::error('Error en processAudio', ['message' => $e->getMessage()]);
            $this->messages[] = ['sender' => 'bot', 'text' => 'Ocurrió un error procesando el audio.'];
            $this->isLoading = false;
            $this->dispatch('message-sent');
        }
    }

    private function sendAudioResponse(string $text, VoiceService $voiceService)
    {
        $audioContent = $voiceService->synthesizeSpeech($text);
        if ($audioContent) {
            $this->dispatch('play-audio', audioBase64: base64_encode($audioContent));
        } else {
            Log::warning("No se pudo sintetizar audio para la respuesta.");
        }
    }

    public function render()
    {
        return view('livewire.chatbot-widget');
    }
}