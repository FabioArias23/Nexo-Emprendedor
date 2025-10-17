<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Mis Proyectos</h2>
        <a href="{{ route('project.create') }}">
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
                    <th class="p-4">Industria</th>
                    <th class="p-4">Meta de Financiación</th>
                    <th class="p-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $project)
                    <tr class="border-b dark:border-zinc-700 last:border-b-0">
                        <td class="p-4">{{ $project->title }}</td>
                        <td class="p-4">{{ $project->industry }}</td>
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
</div>