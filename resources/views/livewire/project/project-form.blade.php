<div>
    <h2 class="text-2xl font-bold mb-6">
        {{ $project->exists ? 'Editar Proyecto' : 'Crear Nuevo Proyecto' }}
    </h2>

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="title" label="Título del Proyecto" required />
        @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <textarea wire:model="description" placeholder="Descripción detallada del proyecto" rows="5" class="block w-full border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 rounded-md shadow-sm"></textarea>
        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <div>
            <label for="category" class="block font-medium text-sm text-zinc-700 dark:text-zinc-300">Categoría</label>
            <select wire:model="category_id" id="category" class="block w-full mt-1 border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 rounded-md shadow-sm">
                <option value="">Selecciona una categoría</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <flux:input wire:model="funding_goal" label="Meta de Financiación ($)" type="number" step="0.01" required />
        @error('funding_goal') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <flux:input wire:model="min_investment" label="Inversión Mínima por Inversor ($)" type="number" step="0.01" required />
        @error('min_investment') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <textarea wire:model="business_model" placeholder="Modelo de Negocio" rows="3" class="block w-full border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 rounded-md shadow-sm"></textarea>
        @error('business_model') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <textarea wire:model="market_potential" placeholder="Potencial de Mercado" rows="3" class="block w-full border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 rounded-md shadow-sm"></textarea>
        @error('market_potential') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <flux:input wire:model="deadline" label="Fecha Límite (Opcional)" type="date" />
        @error('deadline') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <div>
            <label class="block font-medium text-sm text-zinc-700 dark:text-zinc-300">Foto del Proyecto</label>
            <input type="file" wire:model="photo" class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            @error('photo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

            <div wire:loading wire:target="photo" class="text-sm text-zinc-500 mt-1">Cargando...</div>

            @if ($photo)
            <div class="mt-2">
                <p>Previsualización:</p>
                <img src="{{ $photo->temporaryUrl() }}" class="w-48 h-auto rounded">
            </div>
            @elseif ($project->exists && $project->photos->first())
            <div class="mt-2">
                <p>Foto Actual:</p>
                <img src="{{ Storage::url($project->photos->first()->path) }}" class="w-48 h-auto rounded">
            </div>
            @endif
        </div>

        <div class="flex gap-4">
            <flux:button type="submit" variant="primary">Guardar Proyecto</flux:button>
            <a href="{{ route('dashboard') }}" wire:navigate class="px-4 py-2 text-sm rounded-lg bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                Cancelar
            </a>
        </div>
    </form>
</div>