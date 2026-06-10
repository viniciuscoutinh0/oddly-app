<div class="space-y-12">
    <div>
        <div class="mb-6">
            <flux:heading size="xl">
                Criar um novo bolão
            </flux:heading>

            <flux:text class="mt-1">
                Escolha uma competição e comece a competir com seus amigos.
            </flux:text>
        </div>

        <div class="flex flex-col lg:flex-row lg:items-stretch gap-6 w-full">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 flex-1 min-w-0">
                @foreach ($this->competitions as $competition)
                    <a
                        href="{{ route('pools.create', ['competition' => $competition->id]) }}"
                        class="border flex flex-col gap-4 p-6 items-center justify-center text-center border-zinc-800  bg-zinc-900 rounded-xl overflow-hidden transition duration-75 hover:border-accent cursor-pointer"
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
                <a
                    href="{{ route('pools.show', $pool) }}"
                    class="border transition duration-75 focus:outline-hidden hover:border-accent cursor-pointer flex flex-col sm:flex-row border-zinc-800 bg-zinc-900 rounded-xl overflow-hidden"
                    wire:key="pool-{{ $pool->id }}"
                >
                    <div
                        class="w-full sm:w-36 shrink-0 flex flex-col gap-3 items-center justify-center bg-zinc-950/40 p-4 border-b sm:border-b-0 sm:border-r border-zinc-800 text-center">
                        <div
                            class="bg-accent text-white rounded-md p-2 w-full font-bold text-[11px] uppercase tracking-wider line-clamp-3">
                            {{ $pool->season->competition->name }}
                        </div>
                    </div>

                    <div class="p-6 flex-1 min-w-0 flex flex-col justify-center">
                        <flux:heading
                            size="xl"
                            title="{{ $pool->name }}"
                        >
                            {{ $pool->name }}
                        </flux:heading>

                        <div class="flex items-center gap-1.5 mt-1.5">
                            <x-heroicon-m-users class="w-4 h-4 text-zinc-400 shrink-0" />

                            <flux:text
                                class="text-xs"
                                variant="subtle"
                            >
                                {{ $pool->participants_count }}
                                {{ str('Participante')->plural($pool->participants_count) }}
                            </flux:text>
                        </div>
                    </div>
                </a>
            @empty
                <div class="lg:col-span-2 flex flex-col items-center gap-4 text-center border border-dashed border-zinc-800 bg-zinc-900 rounded-xl p-10">
                    <div class="w-12 h-12 rounded-md bg-zinc-800 flex items-center justify-center shrink-0">
                        <x-heroicon-m-user-group class="w-6 h-6 text-accent" />
                    </div>

                    <div>
                        <flux:heading size="lg">Você ainda não está em nenhum bolão</flux:heading>
                        <flux:text variant="subtle" class="mt-1">
                            Crie o seu ou entre em um existente para começar a palpitar.
                        </flux:text>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <flux:button :href="route('pools.create')" variant="primary" icon="plus">
                            Criar bolão
                        </flux:button>
                        <flux:button :href="route('pools.index')" variant="filled" icon="user-group">
                            Entrar em bolão
                        </flux:button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
