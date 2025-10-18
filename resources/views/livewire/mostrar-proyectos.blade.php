<div class="px-6 py-12 max-w-7xl mx-auto">
    <h2 class="text-2xl font-semibold mb-6 text-center">🌱 Proyectos Destacados</h2>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            @forelse($proyectos as $proyecto)
                <div class="swiper-slide">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition overflow-hidden h-full flex flex-col">
                        {{-- 1. Lógica para mostrar la primera foto del proyecto --}}
                        @if ($proyecto->photos->isNotEmpty())
                            <img 
                                src="{{ Storage::url($proyecto->photos->first()->path) }}" 
                                alt="{{ $proyecto->title }}" 
                                class="w-full h-48 object-cover"
                            >
                        @else
                            {{-- Un placeholder si no hay foto --}}
                            <div class="w-full h-48 bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                                <span class="text-zinc-500">Sin Imagen</span>
                            </div>
                        @endif

                        <div class="p-4 flex-grow">
                            {{-- 2. Accede a las propiedades del modelo --}}
                            <h3 class="font-bold text-lg mb-2">{{ $proyecto->title }}</h3>
                            <p class="text-gray-600 dark:text-gray-300 text-sm">{{ Str::limit($proyecto->description, 100) }}</p>
                        </div>
                    </div>
                </div>
            @empty
                {{-- 3. Mensaje si no hay proyectos --}}
                <div class="swiper-slide">
                    <p class="text-center text-zinc-500 col-span-full">No hay proyectos disponibles en este momento.</p>
                </div>
            @endforelse
        </div>

        <div class="swiper-button-next !text-blue-600 dark:!text-blue-400"></div>
        <div class="swiper-button-prev !text-blue-600 dark:!text-blue-400"></div>
        <div class="swiper-pagination !bottom-0 mt-4"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    let swiperInstance = null;

    function initSwiper() {
        // Destruir la instancia anterior si existe
        if (swiperInstance !== null) {
            swiperInstance.destroy(true, true);
            swiperInstance = null;
        }

        const slides = document.querySelectorAll('.mySwiper .swiper-slide');
        if (slides.length === 0) return;

        swiperInstance = new Swiper('.mySwiper', {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: slides.length > 3,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                640: { slidesPerView: 2 },
                1024: { slidesPerView: 3 },
            },
        });
    }

    // Inicializar al cargar la página
    document.addEventListener('DOMContentLoaded', initSwiper);

    // Reinicializar cuando Livewire actualice el DOM
    document.addEventListener('livewire:navigated', initSwiper);
    document.addEventListener('livewire:updated', initSwiper);
</script>
</div>