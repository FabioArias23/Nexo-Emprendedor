<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexo-Emprendedores | Emprendedores e Inversores</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Estilos para animación -->
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
    </style>
</head>
<body class="bg-white text-gray-800 dark:bg-gray-900 dark:text-gray-100 transition-colors duration-300">
  <div id="main-content"></div>
    <!-- Navbar -->
    <header class="flex justify-between items-center px-6 py-4 shadow-sm dark:shadow-gray-800">
        <img 
            src="{{ asset('images/nexo.png') }}" 
            alt="Logo" 
            class="w-40 h-20 dark:drop-shadow-[0_0_14px_rgba(255,255,255,1.95)] transition duration-300"
        />

        <nav class="flex items-center space-x-4">
            @auth
                <a href="{{ url('/dashboard') }}" class="text-sm hover:underline">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 text-sm rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    Iniciar Sesión
                </a>
                <a href="{{ route('register') }}" class="px-4 py-2 text-sm rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
                    Registrarse
                </a>
            @endauth

            <!-- Botón modo oscuro (sincronizado con localStorage) -->
            <button 
                x-data="{ dark: localStorage.theme === 'dark' }"
                @click="dark = !dark; localStorage.theme = dark ? 'dark' : 'light'"
                class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                aria-label="Alternar modo oscuro"
            >
                <svg x-show="!dark" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a.75.75 0 01.75.75V4a.75.75 0 01-1.5 0V2.75A.75.75 0 0110 2zM10 16a.75.75 0 01.75.75V18a.75.75 0 01-1.5 0v-1.25A.75.75 0 0110 16zM4 9.25a.75.75 0 000 1.5H2.75a.75.75 0 000-1.5H4zM17.25 9.25a.75.75 0 000 1.5H19a.75.75 0 000-1.5h-1.75zM4.22 4.22a.75.75 0 011.06 0L6.5 5.44a.75.75 0 11-1.06 1.06L4.22 5.28a.75.75 0 010-1.06zM14.56 14.56a.75.75 0 011.06 0l1.22 1.22a.75.75 0 01-1.06 1.06l-1.22-1.22a.75.75 0 010-1.06zM14.56 5.44a.75.75 0 010-1.06l1.22-1.22a.75.75 0 111.06 1.06L15.62 5.44a.75.75 0 01-1.06 0zM4.22 15.78a.75.75 0 010-1.06l1.22-1.22a.75.75 0 111.06 1.06L5.28 15.78a.75.75 0 01-1.06 0z"></path>
                </svg>
                <svg x-show="dark" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707 8 8 0 1017.293 13.293z"></path>
                </svg>
            </button>
        </nav>
    </header>

    <!-- Hero -->
    <section class="relative text-center py-16 px-6 min-h-[60vh] flex flex-col justify-center items-center overflow-hidden">
        <!-- Video de fondo -->
        <video 
            autoplay 
            muted 
            loop 
            playsinline
            class="absolute inset-0 w-full h-full object-cover z-0"
        >
            <source src="{{ asset('images/video.mp4') }}" type="video/mp4">
            Tu navegador no soporta videos.
        </video>

        <!-- Capa de oscurecimiento -->
        <div class="absolute inset-0 bg-white/40 z-10"></div>

        <!-- Contenido -->
        <div class="relative z-20 max-w-3xl">
            <img 
                src="{{ asset('images/nexo.png') }}" 
                alt="Logo" 
                class="w-80 h-40 mx-auto drop-shadow-[0_0_14px_rgba(255,255,255,1.8)] animate-fade-in-up"
            />
            <h1 class="text-3xl md:text-5xl font-bold mb-4 text-black">
                Conectando Emprendedores e Inversores en la Zona NEA 🇦🇷
            </h1>
            <p class="max-w-4xl mx-auto text-white mb-6 drop-shadow-lg">
                Un ecosistema digital donde las ideas innovadoras encuentran el apoyo que necesitan para crecer.
            </p>
            @guest
                <a href="{{ route('register') }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg text-lg hover:bg-blue-700 transition">
                    Comenzar Ahora
                </a>
            @endguest
        </div>
    </section>

    <!-- Proyectos estáticos (opcional: podrías eliminar esta sección si usas solo el componente) -->
    <section class="px-6 py-12 max-w-7xl mx-auto">
        <h2 class="text-2xl font-semibold mb-8 text-center">🌱 Proyectos Destacados</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach ([
                ['nombre'=>'EcoAgro Formosa', 'img'=>'/images/proyecto1.jpg', 'desc'=>'Plataforma para conectar productores agroecológicos con mercados locales.'],
                ['nombre'=>'SolarNEA', 'img'=>'/images/proyecto2.jpg', 'desc'=>'Energía renovable accesible para comunidades rurales.'],
                ['nombre'=>'Turismo Vivo', 'img'=>'/images/proyecto3.jpg', 'desc'=>'Experiencias turísticas sostenibles en la región NEA.'],
            ] as $proyecto)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-lg transition overflow-hidden">
                    <img src="{{ $proyecto['img'] }}" alt="{{ $proyecto['nombre'] }}" class="w-full h-40 object-cover">
                    <div class="p-4">
                        <h3 class="font-bold text-lg mb-2">{{ $proyecto['nombre'] }}</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm">{{ $proyecto['desc'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Componentes Livewire -->
    <section>@livewire('mostrar-proyectos')</section>
    <section>@livewire('cotizaciones')</section>
    <section>@livewire('noticias-economia')</section>

    <footer class="text-center py-6 text-sm border-t dark:border-gray-700">
        © {{ date('Y') }} Zona NEA – Formosa. Todos los derechos reservados.
    </footer>
</div>
   
</body>
 <!-- Widget de accesibilidad (al final del body) -->
    @livewire('accesibilidad-widget')

    @livewireScripts
    @stack('scripts')
</html>