<div>
    <div class="max-w-4xl mx-auto py-12 px-4">
        {{-- Título y Categoría --}}
        <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $project->title }}</h1>
        <p class="mt-2 text-lg text-indigo-600 dark:text-indigo-400 font-semibold">{{ $project->category->name }}</p>

        {{-- Galería de Fotos (si hay) --}}
        @if($project->photos->isNotEmpty())
        <div class="mt-8">
            <img src="{{ Storage::url($project->photos->first()->path) }}" alt="{{ $project->title }}" class="w-full h-80 object-cover rounded-lg shadow-lg">
        </div>
        @endif

        {{-- Detalles Financieros --}}
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div class="p-4 bg-gray-100 dark:bg-zinc-800 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Meta de Financiación</p>
                <p class="text-2xl font-bold">${{ number_format($project->funding_goal, 0) }}</p>
            </div>
            <div class="p-4 bg-gray-100 dark:bg-zinc-800 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Inversión Mínima</p>
                <p class="text-2xl font-bold">${{ number_format($project->min_investment, 0) }}</p>
            </div>
            <div class="p-4 bg-gray-100 dark:bg-zinc-800 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Fecha Límite</p>
                <p class="text-2xl font-bold">{{ $project->deadline ? $project->deadline->format('d/m/Y') : 'Abierto' }}</p>
            </div>
        </div>

        {{-- Descripción y Detalles --}}
        <div class="mt-10 prose prose-lg dark:prose-invert max-w-none">
            <h2>Descripción</h2>
            <p>{{ $project->description }}</p>

            <h2>Modelo de Negocio</h2>
            <p>{{ $project->business_model }}</p>

            <h2>Potencial de Mercado</h2>
            <p>{{ $project->market_potential }}</p>
        </div>

        {{-- Botón de Acción (para Inversores) --}}
        <div class="mt-10 text-center">
            @if(Auth::user()->role === 'investor' && Auth::id() !== $project->user_id)

            @if($hasProposed)
            {{-- Si ya propuso, muestra un mensaje de confirmación --}}
            <div class="p-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 rounded-lg">
                <p class="font-semibold">✓ Ya has enviado una propuesta para este proyecto.</p>
                <p class="text-sm">El emprendedor ha sido notificado y puedes seguir el estado desde tu dashboard.</p>
            </div>
            @else
            {{-- Si no ha propuesto, muestra el botón --}}
            <flux:button
                variant="primary"
                wire:click="$dispatch('open-proposal-modal', { projectId: {{ $project->id }} })">
                Proponer Inversión
            </flux:button>
            @endif

            @endif
        </div>
    </div>

    {{-- Aquí vivirá nuestro modal de propuesta --}}
    <livewire:investment.proposal-modal />

    {{-- Opcional: Toast de notificación de éxito --}}
    <div
        x-data="{ show: false }"
        @proposal-sent.window="show = true; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        style="display: none;"
        class="fixed bottom-5 right-5 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg">
        ¡Propuesta enviada exitosamente!
    </div>
</div>