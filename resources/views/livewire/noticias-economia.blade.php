<div class="px-6 py-10 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold mb-6 text-center text-gray-800 dark:text-white">📰 Noticias de Economía - Argentina</h2>

    @if($loading)
        <div class="text-center py-6 text-gray-500">Cargando noticias...</div>
    @elseif($error)
        <div class="bg-red-100 text-red-700 p-4 rounded text-center mb-6">{{ $error }}</div>
    @elseif(empty($noticias))
        <div class="text-center py-6 text-gray-500">No hay noticias disponibles.</div>
    @else
        <div class="relative">
            <button 
                onclick="document.querySelector('.news-scroll').scrollBy({left: -300, behavior: 'smooth'})"
                class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 bg-white dark:bg-gray-800 p-2 rounded-full shadow-md hover:bg-gray-100 dark:hover:bg-gray-700"
                aria-label="Anterior"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <div class="news-scroll overflow-x-auto flex space-x-4 pb-4 pt-2 px-4
                        [&::-webkit-scrollbar]:hidden
                        [-ms-overflow-style:none]
                        [scrollbar-width:none]">
                @foreach($noticias as $n)
                    <div class="flex-shrink-0 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden h-full flex flex-col">
                        <img 
                            src="{{ !empty($n['image_url']) ? $n['image_url'] : asset('images/logo.png') }}" 
                            alt="{{ $n['title'] ?? 'Noticia' }}"
                            class="w-full h-32 object-cover"
                            loading="lazy"
                            onerror="this.src='{{ asset('images/nexo.png') }}'"
                        >
                        <div class="p-4 flex-grow flex flex-col">
                            <span class="text-xs text-blue-600 dark:text-blue-400 font-medium mb-1">
                                {{ $n['creator'][0] ?? 'Fuente' }}
                            </span>
                            <h3 class="font-bold text-lg mb-2 line-clamp-2">{{ $n['title'] ?? 'Sin título' }}</h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-3 line-clamp-3">
                                {{ Str::limit(strip_tags($n['description'] ?? ''), 120) }}
                            </p>
                            <a 
                                href="{{ $n['link'] }}" 
                                target="_blank"
                                class="mt-auto text-blue-600 hover:underline text-sm font-medium"
                            >
                                Leer más →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <button 
                onclick="document.querySelector('.news-scroll').scrollBy({left: 300, behavior: 'smooth'})"
                class="absolute right-0 top-1/2 transform -translate-y-1/2 z-10 bg-white dark:bg-gray-800 p-2 rounded-full shadow-md hover:bg-gray-100 dark:hover:bg-gray-700"
                aria-label="Siguiente"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    @endif
</div>