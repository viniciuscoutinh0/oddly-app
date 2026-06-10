<div class="space-y-8">
    <div class="mb-6">
        <flux:heading size="xl">
            Criar um novo bolão
        </flux:heading>

        <flux:text class="mt-1">
            Escolha uma competição e comece a competir com seus amigos.
        </flux:text>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-start gap-6 w-full">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 flex-1 min-w-0">
            @foreach ($this->competitions as $competition)
                <a
                    href="{{ route('pools.create', ['competition' => $competition->id]) }}"
                    class="border flex flex-col gap-4 p-4 md:p-6 items-center justify-center text-center border-zinc-800 bg-zinc-900 rounded-xl overflow-hidden transition duration-75 hover:border-accent cursor-pointer"
                    wire:key="competiton-{{ $competition->id }}"
                >
                    <div class="flex flex-col items-center gap-3">
                        <div
                            class="w-12 h-12 bg-zinc-800 rounded-md flex items-center justify-center shrink-0 border border-zinc-700/50">
                            <x-heroicon-s-trophy class="w-6 h-6 text-accent" />
                        </div>
                        <flux:heading>
                            {{ $competition->name }}
                        </flux:heading>
                    </div>

                    <flux:text variant="subtle">
                        {{ $competition->seasons_count }}
                        {{ str('Temporada')->plural($competition->seasons_count) }}
                    </flux:text>
                </a>
            @endforeach
        </div>

        <livewire:pools.join />
    </div>


    <div>
        <flux:heading
            size="xl"
            class="mb-6"
        >
            Meus bolões
        </flux:heading>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse ($this->pools as $pool)
                <x-pool.card
                    :$pool
                    wire:key="pool-{{ $pool->id }}"
                />
            @empty
                <x-pool.empty />
            @endforelse
        </div>
    </div>
</div>
