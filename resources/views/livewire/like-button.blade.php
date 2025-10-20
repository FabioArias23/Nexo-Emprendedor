<div class="flex items-center space-x-2">
    <button 
        wire:click="toggleLike"
        wire:loading.attr="disabled"
        class="flex items-center justify-center p-2 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        aria-label="Dar Me Gusta"
    >
        {{-- El corazón SVG cambia de estilo según si el proyecto ya tiene like o no --}}
        <svg 
            class="w-6 h-6 transition-transform transform hover:scale-110 {{ $isLiked ? 'text-red-500 fill-current' : 'text-gray-400 hover:text-red-500' }}" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 116.364 6.364L12 20.364l-7.682-7.682a4.5 4.5 0 010-6.364z"></path>
        </svg>
    </button>
    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $likesCount }}</span>
</div>