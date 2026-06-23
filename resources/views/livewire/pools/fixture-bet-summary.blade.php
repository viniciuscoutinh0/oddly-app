<flux:modal
    name="fixture-bet-summary-modal"
    class="md:max-w-lg"
    flyout
    variant="floating"
>
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Palpites da partida</flux:heading>
            <flux:text class="mt-2">Veja o que cada participante apostou para esta partida.</flux:text>
        </div>

        <div class="flex items-center justify-between gap-4 rounded-xl border border-zinc-700 bg-zinc-900 p-4">
            @foreach ($this->teams as $side => $team)
                <div
                    class="flex flex-1 flex-col items-center gap-2 min-w-0"
                    wire:key="team-{{ $side }}"
                >
                    <div class="w-10 h-10 shrink-0">
                        @if (filled($team))
                            <img
                                src="{{ $team->logo_url }}"
                                alt="{{ $team->tla }}"
                                class="w-full h-full object-contain ring-1 ring-inset ring-white/10"
                            />
                        @else
                            <div class="w-full h-full rounded-md border border-dashed border-zinc-800 bg-zinc-800/15"></div>
                        @endif
                    </div>

                    <flux:text variant="strong" class="text-center text-sm truncate max-w-24">
                        {{ $team?->tla ?? 'A definir' }}
                    </flux:text>
                </div>

                @if ($loop->first)
                    <div class="flex flex-col items-center gap-1.5">
                        <flux:text class="text-xs" variant="subtle">Resultado</flux:text>

                        <div class="flex shrink-0 items-center gap-1.5">
                            <flux:heading size="xl" class="tabular-nums">
                                {{ $this->score['home'] }}
                            </flux:heading>

                            <flux:separator text="x" />

                            <flux:heading size="xl" class="tabular-nums">
                                {{ $this->score['away'] }}
                            </flux:heading>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="flex flex-col items-center gap-3">
            <div class="relative w-28 h-28">
                <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90" aria-hidden="true">
                    <circle
                        cx="50" cy="50" r="{{ $this->donut['r'] }}"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="11"
                        class="text-zinc-700"
                    />
                    @if ($this->donut['arc'] > 0)
                        <circle
                            cx="50" cy="50" r="{{ $this->donut['r'] }}"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="11"
                            class="text-green-500"
                            stroke-dasharray="{{ $this->donut['arc'] }} {{ $this->donut['circumference'] }}"
                            stroke-linecap="round"
                        />
                    @endif
                </svg>

                <div class="absolute inset-0 flex flex-col items-center justify-center gap-0.5">
                    <flux:heading size="xl" class="tabular-nums leading-none">{{ $this->bets->count() }}</flux:heading>
                    <flux:text variant="subtle" class="text-xs leading-none">palpites</flux:text>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-green-500 shrink-0"></div>
                    <flux:text class="text-xs tabular-nums">
                        {{ $this->hitsCount }} {{ $this->hitsCount !== 1 ? 'acertos' : 'acerto' }}
                    </flux:text>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-zinc-600 shrink-0"></div>
                    <flux:text class="text-xs tabular-nums">
                        {{ $this->missesCount }} {{ $this->missesCount !== 1 ? 'sem pontos' : 'sem ponto' }}
                    </flux:text>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            @forelse ($this->bets as $bet)
                <div
                    @class([
                        'flex gap-3 bg-zinc-900 border text-white rounded-xl overflow-hidden',
                        'border-green-600' => $bet->is_exact || $bet->is_correct_result,
                        'border-zinc-700' => !($bet->is_exact || $bet->is_correct_result),
                    ])
                    wire:key="bet-{{ $bet->id }}"
                >
                    <div class="flex-1 min-w-0 flex items-center gap-3 px-3 py-2.5">
                        <flux:avatar
                            circle
                            size="sm"
                            initials="{{ $bet->user->initials() }}"
                            class="shrink-0"
                        />

                        <flux:text variant="strong" class="flex-1 min-w-0 truncate">
                            {{ $bet->user->name }}
                        </flux:text>

                        <flux:text variant="strong" class="shrink-0 text-lg tabular-nums">
                            {{ $bet->home_score }}
                            <flux:text variant="subtle" class="text-xs">x</flux:text>
                            {{ $bet->away_score }}
                        </flux:text>
                    </div>

                    <div @class([
                        'flex items-center justify-center shrink-0 w-16 py-2.5',
                        'bg-green-600/15' => $bet->is_exact || $bet->is_correct_result,
                        'bg-zinc-700/15' => !($bet->is_exact || $bet->is_correct_result),
                    ])>
                        <flux:heading class="tabular-nums">
                            {{ $bet->points() }}{{ str('pt')->plural($bet->points()) }}
                        </flux:heading>
                    </div>
                </div>
            @empty
                <flux:text variant="subtle">
                    Nenhum palpite registrado para esta partida.
                </flux:text>
            @endforelse
        </div>
    </div>
</flux:modal>
