@use(\App\Enums\Fixture\Duration)
@props(['fixture', 'group', 'beted' => false])

@php
    $locked = $fixture->isLocked() || $fixture->isFinished();
    $result = $fixture->finalScore();
@endphp

<div
    {{ $attributes->class([
            'ring-2 ring-offset-2 ring-offset-zinc-800 ring-accent' => $beted,
        ])->merge([
            'class' =>
                'overflow-hidden bg-zinc-900 mb-[calc(0.75rem+calc(var(--spacing)*2))] last:mb-0 border border-zinc-800 rounded-xl transition duration-75',
        ]) }}
    x-data="{
        target: {{ $fixture->locked_at?->timestamp }} * 1000,

        expired: false,

        label: '',

        tick() {
            const diff = this.target - Date.now();

            if (diff <= 0) {
                this.expired = true;

                this.label = 'Palpite Encerrado';

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
>
    <div class="flex items-center justify-between gap-2 bg-zinc-800 text-white px-3 py-1.5 border-b border-zinc-800">
        <flux:text class="text-xs text-white">
            {{ $fixture->group_letter ? 'Grupo ' . $fixture->group_letter : $group }}
        </flux:text>

        @if (!$fixture->isFinished() && filled($fixture->locked_at))
            <div
                x-data=""
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
                    class="tabular-nums"
                    x-text="home ?? 0"
                />

                <flux:text variant="subtle">
                    x
                </flux:text>

                <flux:heading
                    size="xl"
                    class="tabular-nums"
                    x-text="away ?? 0"
                />
            </div>

            <flux:text
                class="text-center text-xs"
                variant="subtle"
            >
                {{ $fixture->match_date->format('d/m/Y à\\s H:i') }}
            </flux:text>

            <flux:button
                size="sm"
                icon="rectangle-stack"
                variant="subtle"
                x-on:click="expired && Livewire.dispatchTo('pools.fixture-bet-summary', 'show', { fixtureId: {{ $fixture->id }} });"
                :disabled="!$locked"
            >
                Palpites
            </flux:button>
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
</div>
