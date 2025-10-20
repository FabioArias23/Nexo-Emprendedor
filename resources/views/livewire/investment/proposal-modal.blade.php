<div>
    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-30 backdrop-blur-sm" x-data @keydown.escape.window="$wire.closeModal()">
        
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-xl p-6 w-full max-w-lg" @click.away="$wire.closeModal()">
            <h3 class="text-xl font-bold mb-4">Proponer Inversión para "{{ $project->title }}"</h3>
            
            <form wire:submit="save" class="space-y-4">
                <div>
                    <label for="amount" class="block text-sm font-medium">Monto a Proponer ($)</label>
                    <input type="number" wire:model="proposed_amount" id="amount" class="mt-1 block w-full border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 rounded-md shadow-sm">
                    @error('proposed_amount') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label for="message" class="block text-sm font-medium">Mensaje para el Emprendedor</label>
                    <textarea wire:model="message" id="message" rows="4" class="mt-1 block w-full border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900 rounded-md shadow-sm"></textarea>
                    @error('message') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                
                <div class="flex justify-end gap-4 pt-4">
                    <flux:button type="button" variant="filled" wire:click="closeModal">Cancelar</flux:button>
                    <flux:button type="submit" variant="primary">Enviar Propuesta</flux:button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>