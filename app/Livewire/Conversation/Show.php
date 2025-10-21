<?php

namespace App\Livewire\Conversation;

use App\Models\Investment;
use App\Models\Message;
use App\Models\Notification; 
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Investment $investment;
    public $messageList;
    public string $newMessageBody = '';

    public function mount(Investment $investment)
    {
        $isParticipant = ($investment->investor_id === Auth::id() || $investment->project->user_id === Auth::id());
        if (!$isParticipant) {
            abort(403, 'No tienes permiso para ver esta conversación.');
        }

        $this->investment = $investment->load('project');
        $this->loadMessages();
    }

    public function loadMessages()
    {
        // 1. Contamos cuántos mensajes hay actualmente en la pantalla (si hay alguno).
        $previousMessageCount = $this->messageList ? $this->messageList->count() : 0;

        // 2. Obtenemos la lista actualizada de mensajes desde la base de datos.
        $this->messageList = $this->investment->messages()->with('sender')->get();

        // 3. Comparamos. Si el nuevo conteo es mayor que el anterior, significa que llegó un nuevo mensaje.
        if ($this->messageList->count() > $previousMessageCount) {
            // 4. Disparamos un nuevo evento SOLO si hay mensajes nuevos.
            $this->dispatch('new-message-received');
        }
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessageBody' => 'required|string|min:1',
        ]);

        $receiverId = ($this->investment->investor_id === Auth::id())
            ? $this->investment->project->user_id
            : $this->investment->investor_id;

        Message::create([
            'investment_id' => $this->investment->id,
            'sender_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'body' => $this->newMessageBody,
        ]);

        Notification::create([
            'user_id' => $receiverId,
            'message' => "Has recibido un nuevo mensaje de " . Auth::user()->name . " sobre el proyecto '{$this->investment->project->title}'.",
            'link' => route('conversation.show', $this->investment),
        ]);

        $this->reset('newMessageBody');
        $this->loadMessages();

        $this->dispatch('message-sent');
    }

    public function render()
    {
        return view('livewire.conversation.show');
    }
}
