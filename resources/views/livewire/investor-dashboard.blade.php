<div>
    <h2 class="text-2xl font-bold mb-6">Proyectos Buscando Inversión</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($projects as $project)
            <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-md overflow-hidden transition-transform hover:scale-105">
                {{-- Usamos la primera foto como portada --}}
                @if ($project->photos->first())
                    <img src="{{ Storage::url($project->photos->first()->path) }}" alt="{{ $project->title }}" class="w-full h-48 object-cover">
                @else
                    {{-- Un placeholder si no hay foto --}}
                    <div class="w-full h-48 bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                        <span class="text-zinc-500">Sin Imagen</span>
                    </div>
                @endif

                <div class="p-4">
                    <h3 class="text-lg font-bold">{{ $project->title }}</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">{{ $project->industry }}</p>
                    <p class="text-sm mb-4">{{ Str::limit($project->description, 100) }}</p>
                    
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-semibold">Meta: ${{ number_format($project->funding_goal, 2) }}</span>
                        <a href="#" class="text-blue-500 hover:underline">Ver Más</a>
                    </div>
                </div>
            </div>
        @empty
            <p>No hay proyectos disponibles en este momento.</p>
        @endforelse
    </div>
</div>