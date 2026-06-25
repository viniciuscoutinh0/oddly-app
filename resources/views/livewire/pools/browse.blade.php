<div class="space-y-8">
    <div class="flex flex-col sm:flex-row gap-4 sm:items-center justify-between">
        <div class="flex flex-col gap-1 shrink-0">
            <flux:heading size="xl">Bolões públicos</flux:heading>

            <flux:text variant="subtle">Escolha uma competição e comece a competir.</flux:text>
        </div>

        <flux:input
            icon="magnifying-glass"
            placeholder="Pesquisar bolão..."
            autocomplete="off"
            class="sm:max-w-xs flex-1"
            wire:model.live.debounce.250ms="search"
        >
            @if (filled($search))
                <x-slot name="iconTrailing">
                    <flux:button
                        size="sm"
                        variant="subtle"
                        icon="x-mark"
                        class="-mr-1"
                        wire:click="$set('search', null)"
                    />
                </x-slot>
            @endif
        </flux:input>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-start gap-6">
        <div class="flex-1 space-y-6">
            @forelse ($this->pools as $pool)
                <x-pool.card
                    :$pool
                    wire:key="pool-{{ $pool->id }}"
                />
            @empty
                <x-pool.empty />
            @endforelse
        </div>

        <livewire:pools.join />
    </div>
</div>
