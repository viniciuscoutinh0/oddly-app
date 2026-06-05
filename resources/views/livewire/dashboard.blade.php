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

        <div class="flex items-stretch gap-6 w-full">
            <div class="grid grid-cols-2 gap-6 flex-1 min-w-0">
                @foreach ($this->competitions as $competition)
                    <a
                        href="{{ route('pools.create', ['competition' => $competition->id]) }}"
                        class="border flex flex-col gap-4 p-6 items-center justify-center text-center border-zinc-800  bg-zinc-900 rounded-sm overflow-hidden transition duration-75 hover:border-accent cursor-pointer"
                        wire:key="competiton-{{ $competition->id }}"
                    >
                        <div class="flex flex-col items-center gap-3">
                            <div
                                class="w-12 h-12 bg-zinc-800 rounded-sm flex items-center justify-center shrink-0 border border-zinc-700/50">
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

        <div class="grid grid-cols-2 gap-6">
            @foreach ($this->pools as $pool)
                <a
                    href="{{ route('pools.show', $pool) }}"
                    class="border transition duration-75 focus:outline-hidden hover:border-accent cursor-pointer flex border-zinc-800 bg-zinc-900 rounded-sm overflow-hidden"
                    wire:key="pool-{{ $pool->id }}"
                >
                    <div
                        class="w-36 shrink-0 flex flex-col gap-3 items-center justify-center bg-zinc-950/40 p-4 border-r border-zinc-800 text-center">
                        <div
                            class="bg-accent text-white rounded-sm p-2 w-full font-bold text-[11px] uppercase tracking-wider line-clamp-3">
                            {{ $pool->season->competition->name }}
                        </div>
                    </div>

                    <div class="p-6 flex-1 min-w-0 flex flex-col justify-between">
                        <div class="flex justify-between items-start mb-5 w-full gap-4">
                            <div class="shrink-0 min-w-0 flex-1">
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

                            <div class="text-end shrink-0">
                                <flux:heading
                                    size="xl"
                                    accent
                                    class="leading-none"
                                >
                                    3
                                </flux:heading>
                                <flux:text
                                    class="text-[10px] mt-1 block"
                                    variant="subtle"
                                >
                                    Sua Posição
                                </flux:text>
                            </div>
                        </div>

                        <flux:field class="space-y-1">
                            <flux:label class="text-xs">
                                Grupos
                                <x-slot name="trailing">
                                    75% Completo
                                </x-slot>
                            </flux:label>
                            <flux:progress value="75" />
                        </flux:field>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
