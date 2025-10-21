<div>
    {{-- SECCIÓN 1: MIS PROYECTOS --}}
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Mis Proyectos</h2>
        <a href="{{ route('project.create') }}" wire:navigate class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
            Crear Nuevo Proyecto
        </a>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-md overflow-x-auto">
        <table class="w-full text-left">
            <thead class="border-b dark:border-zinc-700">
                <tr>
                    <th class="p-4">Título</th>
                    <th class="p-4">Categoría</th>
                    <th class="p-4">Meta de Financiación</th>
                    <th class="p-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $project)
                    <tr class="border-b dark:border-zinc-700 last:border-b-0">
                        <td class="p-4">{{ $project->title }}</td>
                        <td class="p-4">{{ $project->category->name ?? 'Sin categoría' }}</td>
                        <td class="p-4">${{ number_format($project->funding_goal, 2) }}</td>
                        <td class="p-4 flex gap-2">
                            <a href="{{ route('project.edit', $project) }}" wire:navigate class="text-blue-500 hover:underline">Editar</a>
                            <button wire:click="delete({{ $project->id }})" wire:confirm="¿Estás seguro de que quieres eliminar este proyecto?" class="text-red-500 hover:underline">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-zinc-500">
                            Aún no has creado ningún proyecto. ¡Anímate a empezar!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- SECCIÓN 2: PROPUESTAS RECIBIDAS --}}
    <div class="mt-12">
        <h2 class="text-2xl font-bold mb-6">Propuestas de Inversión Recibidas</h2>

        <div class="space-y-6">
            @forelse ($proposals as $proposal)
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Propuesta para tu proyecto:</p>
                            <h3 class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ $proposal->project->title }}</h3>
                            <p class="mt-2 text-sm">
                                <span class="font-semibold">Inversor:</span> {{ $proposal->investor->name }}
                            </p>
                            <p class="text-sm">
                                <span class="font-semibold">Monto Propuesto:</span> ${{ number_format($proposal->proposed_amount, 2) }}
                            </p>
                        </div>
                        <div>
                            <flux:badge 
                                :color="$proposal->status === 'pending' ? 'yellow' : ($proposal->status === 'negotiating' ? 'blue' : ($proposal->status === 'rejected' ? 'red' : 'gray'))"
                            >
                                {{ ucfirst($proposal->status) }}
                            </flux:badge>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t dark:border-zinc-700">
                        <p class="text-sm font-semibold mb-2">Mensaje del Inversor:</p>
                        <p class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-zinc-700 p-3 rounded-md">{{ $proposal->message }}</p>
                    </div>
                    <div class="mt-6 flex justify-end gap-4">
                        {{-- Si la propuesta está pendiente, muestra los botones de acción --}}
                        @if ($proposal->status === 'pending')
                            <flux:button 
                                variant="danger"
                                wire:click="rejectProposal({{ $proposal->id }})"
                                wire:confirm="¿Estás seguro de que quieres rechazar esta propuesta?"
                            >
                                Rechazar
                            </flux:button>
                            <flux:button 
                                variant="primary"
                                wire:click="acceptProposal({{ $proposal->id }})"
                            >
                                Aceptar Contacto
                            </flux:button>
                        
                        {{-- Si la propuesta está en negociación, muestra el enlace al chat --}}
                        @elseif ($proposal->status === 'negotiating')
                            <a href="{{ route('conversation.show', $proposal) }}" wire:navigate class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                Iniciar Conversación
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-md p-6 text-center">
                    <p class="text-gray-500 dark:text-gray-400">Aún no has recibido ninguna propuesta de inversión.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>