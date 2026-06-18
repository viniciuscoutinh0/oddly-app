@props(['fixture', 'group', 'beted' => false])

@php
    use App\Enums\Fixture\Duration;

    $locked = $fixture->isLocked() || $fixture->isFinished();
    $result = $fixture->finalScore();
@endphp

<div
    {{ $attributes->class([
            'ring-2 ring-offset-2 ring-offset-zinc-800 ring-accent' => $beted,
        ])->merge([
            'class' =>
                'overflow-hidden bg-zinc-900 mb-[calc(0.75rem+calc(var(--spacing)*2))] last:mb-0 border border-zinc-800 rounded-xl transition duration-75',
        ]) }}>

    <div class="flex items-center justify-between gap-2 bg-zinc-800 text-white px-3 py-1.5 border-b border-zinc-800">
        <flux:text class="text-xs text-white">
            {{ $fixture->group_letter ? 'Grupo ' . $fixture->group_letter : $group }}
        </flux:text>

        @if (!$fixture->isFinished() && filled($fixture->locked_at))
            <div
                x-data="{
                    target: {{ $fixture->locked_at->timestamp }} * 1000,
                
                    expired: false,
                
                    label: '',
                
                    tick() {
                        const diff = this.target - Date.now();
                
                        if (diff <= 0) {
                            this.expired = true;
                
                            this.label = 'Encerrado';
                
                            return;
                        }
                
                        const s = Math.floor(diff / 1000);
                
                        const d = Math.floor(s / 86400);
                
                        const h = Math.floor((s % 86400) / 3600);
                
                        const m = Math.floor((s % 3600) / 60);
                
                        const sec = s % 60;
                
                        this.label = d > 0 ?
                            `${d}d ${h}h ${m}m` :
                            `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`;
                    },
                
                    init() {
                        this.tick();
                
                        const id = setInterval(() => {
                            this.tick();
                
                            if (this.expired) clearInterval(id);
                        }, 1000);
                    },
                }"
                class="flex items-center gap-1.5"
            >
                <flux:icon.clock
                    class="w-3.5 h-3.5 shrink-0"
                    variant="micro"
                    ::class="expired ? 'text-red-400' : 'text-zinc-400'"
                />

                <flux:text
                    class="text-xs font-medium tabular-nums"
                    ::class="expired ? 'text-red-400' : 'text-white'"
                    x-text="label"
                />
            </div>
        @endif

        <flux:badge
            size="sm"
            :color="$fixture->status->fluxColor()"
        >
            {{ $fixture->status->getLabel() }}
        </flux:badge>
    </div>

    <div
        x-data="{
            home: $wire.entangle('scores.{{ $fixture->id }}.home'),
        
            away: $wire.entangle('scores.{{ $fixture->id }}.away'),
        
            save: Alpine.debounce(function() { $wire.save({{ $fixture->id }}, this.home ?? 0, this.away ?? 0) }, 250),
        
            init() {
                this.$watch('home', () => this.save())
                this.$watch('away', () => this.save())
            }
        }"
        class="grid grid-cols-3 items-center gap-2 sm:gap-6 p-3 sm:p-4"
    >

        <x-bet.team
            :team="$fixture->homeTeam"
            :disabled="$locked"
            model="home"
        />

        <div class="flex flex-col items-center justify-center gap-2">
            <div class="flex items-center gap-3">
                <flux:heading
                    size="xl"
                    x-text="home ?? 0"
                />

                <flux:text variant="subtle">
                    x
                </flux:text>

                <flux:heading
                    size="xl"
                    x-text="away ?? 0"
                />
            </div>

            <flux:text
                class="text-center text-xs"
                variant="subtle"
            >
                {{ $fixture->match_date->format('d/m/Y à\\s H:i') }}
            </flux:text>

            @if ($locked)
                <flux:modal.trigger name="edit-profile-{{ $fixture->id }}">
                    <flux:button
                        size="sm"
                        icon="rectangle-stack"
                        variant="subtle"
                    >
                        Palpites
                    </flux:button>
                </flux:modal.trigger>
            @endif
        </div>

        <x-bet.team
            :team="$fixture->awayTeam"
            :disabled="$locked"
            model="away"
        />
    </div>

    @if ($result)
        <div class="flex items-center justify-center gap-2 border-t border-zinc-800 bg-zinc-950/40 px-3 py-2">
            <flux:text class="text-sm font-medium text-white">
                Resultado: {{ $result['home'] }} x {{ $result['away'] }}

                @if ($fixture->duration !== Duration::Regular)
                    ({{ $fixture->duration->getLabel() }})
                @endif
            </flux:text>
        </div>
    @endif

    @if ($locked)
        <flux:modal
            name="edit-profile-{{ $fixture->id }}"
            class="md:max-w-lg"
            flyout
            variant="floating"
        >
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Palpites da partida</flux:heading>
                <flux:text class="mt-2">Veja o que cada participante apostou para esta partida.</flux:text>
            </div>

            <div class="flex items-center justify-center gap-4 rounded-xl border border-zinc-700 bg-zinc-900 p-4">
                @foreach ([$fixture->homeTeam, $fixture->awayTeam] as $team)
                    @if (!$loop->first)
                        <flux:separator text="x" />
                    @endif

                    <div class="flex flex-col items-center gap-2 min-w-0 shrink-0">
                        <div class="w-10 h-10 shrink-0">
                            @if (filled($team))
                                <img
                                    src="{{ $team->logo_url }}"
                                    alt="{{ $team->name }}"
                                    class="w-full h-full object-contain"
                                />
                            @else
                                <div
                                    class="w-full h-full rounded-md border border-dashed border-zinc-800 bg-zinc-800/15">
                                </div>
                            @endif
                        </div>

                        <flux:text
                            variant="strong"
                            class="text-center text-sm truncate max-w-24"
                        >
                            {{ $team?->name ?? 'A definir' }}
                        </flux:text>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between">
                <flux:text
                    variant="subtle"
                    class="text-sm"
                >Total de Palpites</flux:text>

                <flux:badge
                    size="sm"
                    class="tabular-nums"
                >
                    {{ $fixture->bets->count() }}
                </flux:badge>

            </div>

            <div class="space-y-3">
                @forelse ($fixture->bets as $bet)
                    <div
                        @class([
                            'flex items-center gap-3 bg-zinc-900 border text-white rounded-xl px-3 py-2.5',
                            'border-green-600' => $bet->is_exact || $bet->is_correct_result,
                            'border-zinc-700' => ! ($bet->is_exact || $bet->is_correct_result),
                        ])
                        wire:key="bet-{{ $bet->id }}"
                    >
                        <flux:avatar
                            circle
                            size="sm"
                            initials="{{ $bet->user->initials() }}"
                            class="shrink-0"
                        />

                        <flux:text
                            variant="strong"
                            class="flex-1 min-w-0 truncate"
                        >
                            {{ $bet->user->name }}
                        </flux:text>

                        <flux:text
                            variant="strong"
                            class="shrink-0 text-lg tabular-nums"
                        >
                            {{ $bet->home_score }} <flux:text
                                variant="subtle"
                                class="text-xs"
                            >x</flux:text> {{ $bet->away_score }}
                        </flux:text>
                    </div>
                @empty
                    <flux:text variant="subtle">
                        Nenhum palpite registrado para esta partida.
                    </flux:text>
                @endforelse
            </div>
        </div>
        </flux:modal>
    @endif
</div>
