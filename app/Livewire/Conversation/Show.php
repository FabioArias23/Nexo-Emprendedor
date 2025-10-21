<?php

namespace App\Livewire\Conversation;

use App\Models\Investment;
use App\Models\Message;
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
        $this->messageList = $this->investment->messages()->with('sender')->get();
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

        $this->reset('newMessageBody');
        $this->loadMessages();
    }

    public function render()
    {
        return view('livewire.conversation.show');
    }
}