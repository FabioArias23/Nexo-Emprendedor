<x-layouts.app :title="__('Dashboard')">
    <div class="h-full w-full flex-1 flex-col gap-4 rounded-xl">
        
        @if (auth()->user()->role === \App\Models\User::ROLE_ENTREPRENEUR)
            
            <livewire:entrepreneur-dashboard />

        @elseif (auth()->user()->role === \App\Models\User::ROLE_INVESTOR)

            <livewire:investor-dashboard />

        @else
            {{-- Opcional: un dashboard para el admin o un mensaje por defecto --}}
            <h2 class="text-2xl font-bold">Panel de Administración</h2>
            <p>Bienvenido, {{ auth()->user()->name }}.</p>
            
        @endif

    </div>
</x-layouts.app>
<div class="fixed bottom-5 right-5 z-50">
    <livewire:chatbot-widget />
</div>
