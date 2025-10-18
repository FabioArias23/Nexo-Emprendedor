<div>

    <div class="fixed bottom-5 right-5 z-50">
        <!-- Chat Window -->
        <div
            x-data="{
                isOpen: @entangle('isOpen'),
                scrollToBottom() {
                    $nextTick(() => {
                        const messagesContainer = document.getElementById('messages-container');
                        if (messagesContainer) {
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        }
                    });
                }
            }"
            @play-audio.window="playAudio($event.detail.audioBase64)"
            @chat-toggled.window="scrollToBottom"
            @message-sent.window="scrollToBottom"
            x-show="isOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-4"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-4"
            class="flex flex-col w-full max-w-sm h-[32rem] bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700"
            style="display: none;"
        >
            <!-- Header, Messages Area... (Sin cambios) -->
            <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-900/50 rounded-t-xl border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Asistente Virtual</h3>
                <button wire:click="toggleChat" class="text-gray-500 hover:text-gray-800 dark:hover:text-gray-200">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div id="messages-container" class="flex-1 p-4 overflow-y-auto space-y-4">
                @foreach($messages as $message)
                    <div class="flex @if($message['sender'] === 'user') justify-end @else justify-start @endif">
                        <div class="max-w-[80%] rounded-lg px-3 py-2 @if($message['sender'] === 'user') bg-blue-600 text-white @else bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 @endif">
                            <p class="text-sm">{{ $message['text'] }}</p>
                        </div>
                    </div>
                @endforeach
                <div wire:loading wire:target="sendMessage, processAudio" class="flex justify-start">
                     <div class="max-w-[80%] rounded-lg px-3 py-2 bg-gray-200 dark:bg-gray-700">
                        <div class="flex items-center space-x-1">
                            <span class="h-1.5 w-1.5 bg-gray-500 rounded-full animate-pulse [animation-delay:-0.3s]"></span>
                            <span class="h-1.5 w-1.5 bg-gray-500 rounded-full animate-pulse [animation-delay:-0.15s]"></span>
                            <span class="h-1.5 w-1.5 bg-gray-500 rounded-full animate-pulse"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-4 border-t dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 rounded-b-xl"
                 x-data="voiceChatbot()">
                <div class="flex items-center space-x-2">
                    <input
                        type="text"
                        wire:model="currentMessage"
                        wire:keydown.enter="sendMessage"
                        placeholder="Escribe o presiona el micrófono..."
                        class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm"
                        :disabled="$wire.isLoading || isRecording"
                    >
                    <button x-show="$wire.currentMessage.trim() !== ''" wire:click="sendMessage" wire:disabled="$wire.isLoading" class="bg-blue-600 text-white rounded-md p-2 hover:bg-blue-700 disabled:bg-blue-400 disabled:cursor-not-allowed transition">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M3.105 2.289a.75.75 0 00-.826.95l1.414 4.949a.75.75 0 00.95.826L11.25 9.25v1.5L4.643 12.01a.75.75 0 00-.826.95l1.414 4.949a.75.75 0 00.95.826L16.25 12.25a.75.75 0 000-1.414L3.105 2.289z" /></svg>
                    </button>
                    
                    <button
                        x-show="$wire.currentMessage.trim() === ''"
                        @click="isRecording ? stopRecording() : startRecording()"
                        :disabled="$wire.isLoading"
                        class="text-white rounded-md p-2 transition disabled:bg-gray-400"
                        :class="isRecording ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'">
                        <svg x-show="!isRecording" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                        <svg x-show="isRecording" class="h-5 w-5 animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5z" /></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Floating Button -->
        <div x-data="{ isOpen: @entangle('isOpen') }">
            <button x-show="!isOpen" wire:click="toggleChat" class="bg-blue-600 text-white rounded-full h-16 w-16 flex items-center justify-center shadow-lg hover:bg-blue-700 transition transform hover:scale-110">
                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
            </button>
        </div>
    </div>

    <script>
        function playAudio(base64String) {
            const audioUrl = `data:audio/mpeg;base64,${base64String}`;
            const audio = new Audio(audioUrl);
            audio.play().catch(e => console.error("Error al reproducir el audio:", e));
        }

        function voiceChatbot() {
            return {
                isRecording: false,
                mediaRecorder: null,
                audioChunks: [],

                startRecording() {
                    const silentAudio = new Audio('data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA');
                    silentAudio.play().catch(() => {});

                    navigator.mediaDevices.getUserMedia({ audio: true })
                        .then(stream => {
                            this.isRecording = true;
                            this.mediaRecorder = new MediaRecorder(stream);
                            this.mediaRecorder.start();
                            this.audioChunks = [];

                            this.mediaRecorder.addEventListener("dataavailable", event => {
                                this.audioChunks.push(event.data);
                            });

                            this.mediaRecorder.addEventListener("stop", () => {
                                this.isRecording = false;                                
                                this.sendAudio();
                                stream.getTracks().forEach(track => track.stop());
                            });
                        }).catch(err => {
                            console.error("Error al acceder al micrófono:", err);
                            alert("Se necesita permiso para usar el micrófono.");
                        });
                },
                stopRecording() {
                    if (this.mediaRecorder && this.isRecording) {
                        this.mediaRecorder.stop();
                    }
                },
                sendAudio() {
                    const audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
                    const reader = new FileReader();
                    reader.readAsDataURL(audioBlob);
                    reader.onloadend = () => {
                        const base64String = reader.result.split(',')[1];
                        @this.processAudio(base64String);
                    };
                }
            }
        }
    </script>
</div>