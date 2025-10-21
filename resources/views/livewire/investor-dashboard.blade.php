<div>
    <div>
        {{-- MIS PROPUESTAS ENVIADAS --}}
        <div class="mb-12">
            <h2 class="text-2xl font-bold mb-6">Mis Propuestas Enviadas</h2>

            <div class="space-y-4">
                @forelse($myProposals as $proposal)
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-md p-4 flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Propuesta para:</p>
                        <a href="{{ route('project.view', $proposal->project) }}" wire:navigate class="text-lg font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ $proposal->project->title }}
                        </a>
                        <p class="text-sm mt-1">Monto: ${{ number_format($proposal->proposed_amount, 2) }}</p>
                    </div>
                    <div class="text-right">
                        <flux:badge
                            :color="$proposal->status === 'pending' ? 'yellow' : ($proposal->status === 'negotiating' ? 'blue' : ($proposal->status === 'rejected' ? 'red' : 'gray'))">
                            {{ ucfirst($proposal->status) }}
                        </flux:badge>

                        {{-- Mensaje de acción cuando la propuesta es aceptada --}}
                        @if($proposal->status === 'negotiating')
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">¡El emprendedor ha aceptado!</p>
                        <a href="{{ route('conversation.show', $proposal) }}" wire:navigate class="text-sm text-blue-500 hover:underline">
                            Iniciar Conversación
                        </a>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-gray-500 dark:text-gray-400">Aún no has enviado ninguna propuesta de inversión.</p>
                @endforelse
            </div>
        </div>

        {{-- LÍNEA SEPARADORA --}}
        <hr class="my-12 border-gray-200 dark:border-zinc-700">

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
                        <livewire:like-button :project="$project" :key="'like-'.$project->id" />
                        <a href="{{ route('project.view', $project) }}" wire:navigate class="text-blue-500 hover:underline">Ver Más</a>
                    </div>
                </div>
            </div>
            @empty
            <p>No hay proyectos disponibles en este momento.</p>
            @endforelse
        </div>
    </div>