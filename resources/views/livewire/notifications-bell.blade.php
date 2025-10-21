<flux:dropdown position="top" align="end">
    {{-- El botón de la campana es el 'trigger' del dropdown --}}
    <button wire:poll.30s.keep-alive="loadNotifications" class="relative p-2 rounded-full hover:bg-gray-100 dark:hover:bg-zinc-700 focus:outline-none">
        <svg class="h-6 w-6 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if($unreadCount > 0)
            <span class="absolute top-0 right-0 h-3 w-3 bg-red-500 rounded-full border-2 border-white dark:border-zinc-800"></span>
        @endif
    </button>

    {{-- El menú es un <flux:menu> que es el contenido del dropdown --}}
    <flux:menu class="w-80">
        <div class="p-4 font-bold border-b dark:border-zinc-700">Notificaciones</div>
        <div class="max-h-96 overflow-y-auto">
            @forelse($unreadNotifications as $notification)
                <a 
                    wire:click.prevent="markAsRead({{ $notification->id }})"
                    href="{{ $notification->link }}"
                    class="block px-4 py-3 hover:bg-gray-100 dark:hover:bg-zinc-700"
                >
                    <p class="text-sm text-gray-700 dark:text-gray-200">{{ $notification->message }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </a>
            @empty
                <p class="text-sm text-center text-gray-500 py-6">No tienes notificaciones nuevas.</p>
            @endforelse
        </div>
    </flux:menu>
</flux:dropdown>