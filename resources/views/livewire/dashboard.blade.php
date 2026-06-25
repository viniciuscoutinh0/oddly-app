<div class="space-y-8">
    @if ($this->urgentFixtures->isNotEmpty())
        <section class="space-y-3">
            <div class="flex items-center gap-2">
                <flux:icon.exclamation-triangle
                    variant="solid"
                    class="w-5 h-5 text-amber-400 shrink-0"
                />

                <flux:heading size="xl">Palpites Urgentes</flux:heading>

                <flux:badge
                    color="amber"
                    size="sm"
                    rounded
                >{{ $this->urgentFixtures->count() }}</flux:badge>
            </div>

            @foreach ($this->urgentFixtures as $item)
                @php
                    $fixture = $item['fixture'];
                    $urgentPool = $item['pool'];
                @endphp

                <a
                    href="{{ route('pools.show', $urgentPool) }}"
                    wire:key="urgent-{{ $fixture->id }}-{{ $urgentPool->id }}"
                    class="group flex items-center gap-3 sm:gap-5 border border-amber-500/30 bg-amber-500/5 hover:border-amber-500/60 rounded-xl p-3 sm:p-4 transition duration-75"
                >
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        @if (filled($fixture->homeTeam?->logo_url))
                            <img
                                src="{{ $fixture->homeTeam->logo_url }}"
                                alt="{{ $fixture->homeTeam->name }}"
                                class="w-7 h-7 object-contain shrink-0"
                                loading="lazy"
                            />
                        @else
                            <div class="w-7 h-7 rounded bg-zinc-800 border border-dashed border-zinc-700 shrink-0">
                            </div>
                        @endif

                        <flux:text class="font-medium truncate text-sm">
                            {{ $fixture->homeTeam?->short_name ?? '?' }}
                        </flux:text>
                    </div>

                    <flux:text
                        variant="subtle"
                        class="shrink-0 text-sm"
                    >×</flux:text>


                    <div class="flex items-center gap-2 flex-1 min-w-0 flex-row-reverse">
                        @if (filled($fixture->awayTeam?->logo_url))
                            <img
                                src="{{ $fixture->awayTeam->logo_url }}"
                                alt="{{ $fixture->awayTeam->name }}"
                                class="w-7 h-7 object-contain shrink-0"
                                loading="lazy"
                            />
                        @else
                            <div class="w-7 h-7 rounded bg-zinc-800 border border-dashed border-zinc-700 shrink-0">
                            </div>
                        @endif

                        <flux:text class="font-medium truncate text-sm text-right">
                            {{ $fixture->awayTeam?->short_name ?? '?' }}
                        </flux:text>
                    </div>

                    <div class="hidden sm:flex items-center gap-4 shrink-0">
                        <div class="w-px h-8 bg-zinc-700/60"></div>

                        <div class="flex flex-col items-end gap-0.5">
                            <flux:text class="text-xs tabular-nums text-amber-400 font-medium">
                                {{ $fixture->match_date->format('d/m/Y H:i') }}
                            </flux:text>

                            <flux:text
                                variant="subtle"
                                class="text-xs truncate max-w-40"
                            >{{ $urgentPool->name }}</flux:text>
                        </div>
                    </div>

                    <flux:icon.arrow-right
                        class="w-4 h-4 text-zinc-500 group-hover:text-zinc-300 transition-colors shrink-0"
                    />
                </a>
            @endforeach
        </section>
    @endif

    <section>
        <div class="flex items-center justify-between mb-6">
            <flux:heading size="xl">Meus Bolões</flux:heading>

            <flux:button
                :href="route('pools.create')"
                icon="plus"
                size="sm"
                variant="subtle"
            >
                Criar Bolão
            </flux:button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @forelse ($this->pools as $pool)
                <x-pool.card
                    :$pool
                    :pending="$pool->pending_bets_count ?? 0"
                    wire:key="pool-{{ $pool->id }}"
                />
            @empty
                <div class="col-span-full">
                    <x-pool.empty />
                </div>
            @endforelse
        </div>
    </section>

    @if ($this->upcomingFixtures->isNotEmpty())
        <section>
            <flux:heading
                size="xl"
                class="mb-4"
            >Próximas Partidas</flux:heading>

            <div class="space-y-2">
                @foreach ($this->upcomingFixtures as $fixture)
                    <div
                        class="flex items-center gap-3 sm:gap-5 border border-zinc-800 bg-zinc-900 rounded-xl p-3"
                        wire:key="upcoming-{{ $fixture->id }}"
                    >

                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            @if (filled($fixture->homeTeam?->logo_url))
                                <img
                                    src="{{ $fixture->homeTeam->logo_url }}"
                                    alt="{{ $fixture->homeTeam->name }}"
                                    class="w-6 h-6 object-contain shrink-0"
                                    loading="lazy"
                                />
                            @else
                                <div class="w-6 h-6 rounded bg-zinc-800 border border-dashed border-zinc-700 shrink-0">
                                </div>
                            @endif

                            <flux:text class="text-sm truncate">
                                {{ $fixture->homeTeam?->short_name ?? '?' }}
                            </flux:text>
                        </div>


                        <div class="flex flex-col items-center shrink-0 gap-0.5">
                            <flux:text
                                variant="subtle"
                                class="text-xs tabular-nums leading-none"
                            >{{ $fixture->match_date->format('d/m') }}</flux:text>

                            <flux:text class="text-xs tabular-nums font-medium leading-none">
                                {{ $fixture->match_date->format('H:i') }}
                            </flux:text>
                        </div>


                        <div class="flex items-center gap-2 flex-1 min-w-0 flex-row-reverse">
                            @if (filled($fixture->awayTeam?->logo_url))
                                <img
                                    src="{{ $fixture->awayTeam->logo_url }}"
                                    alt="{{ $fixture->awayTeam->name }}"
                                    class="w-6 h-6 object-contain shrink-0"
                                    loading="lazy"
                                />
                            @else
                                <div class="w-6 h-6 rounded bg-zinc-800 border border-dashed border-zinc-700 shrink-0">
                                </div>
                            @endif

                            <flux:text class="text-sm truncate text-right">
                                {{ $fixture->awayTeam?->short_name ?? '?' }}
                            </flux:text>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</div>
