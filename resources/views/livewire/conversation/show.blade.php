<div class="max-w-4xl mx-auto py-12 px-4">
    <h1 class="text-3xl font-bold mb-2">Conversación sobre: {{ $investment->project->title }}</h1>
    <p class="text-gray-500 dark:text-gray-400 mb-8">Negociando una inversión de ${{ number_format($investment->proposed_amount, 2) }}</p>

    {{-- Contenedor de mensajes con auto-scroll --}}
    <div 
        wire:poll.10s="loadMessages" 
        class="space-y-6 bg-white dark:bg-zinc-800 p-6 rounded-lg shadow-md h-96 overflow-y-auto"
        
        x-data="{
            scrollToBottom() {
                // Espera al siguiente 'tick' del DOM para asegurarse de que el nuevo
                // mensaje se ha renderizado antes de calcular la altura.
                this.$nextTick(() => {
                    $el.scrollTop = $el.scrollHeight;
                });
            }
        }"
        x-init="scrollToBottom()"
        @message-sent.window="scrollToBottom()"
        @new-message-received.window="scrollToBottom()"
    >
        @forelse($messageList as $message)
            <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-lg px-4 py-2 rounded-lg {{ $message->sender_id === auth()->id() ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-800 dark:text-gray-200' }}">
                    {{-- La clase whitespace-pre-line preserva los saltos de línea --}}
                    <p class="text-sm whitespace-pre-line">{{ $message->body }}</p>
                    <p class="text-xs text-right mt-1 opacity-75">{{ $message->created_at->format('g:i A') }}</p>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500">Aún no hay mensajes. ¡Inicia la conversación!</p>
        @endforelse
    </div>

    {{-- Formulario para enviar un nuevo mensaje --}}
    <form wire:submit="sendMessage" class="mt-6">
        <textarea 
            wire:model="newMessageBody"
            placeholder="Escribe tu mensaje..."
            rows="3"
            class="w-full border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            {{-- Lógica para enviar con Enter y nueva línea con Shift+Enter --}}
            @keydown.enter="if(!$event.shiftKey) { event.preventDefault(); $wire.sendMessage() }"
        ></textarea>
        @error('newMessageBody') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        
        <div class="mt-2 text-right">
            <flux:button type="submit" variant="primary">Enviar</flux:button>
        </div>
    </form>
</div>