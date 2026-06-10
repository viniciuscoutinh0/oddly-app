@props(['fixture', 'group'])


@php
    use App\Enums\Fixture\Duration;

    $locked = $fixture->isLocked() || $fixture->isFinished();
    $result = $fixture->finalScore();
@endphp

<div
    {{ $attributes->merge(['class' => 'overflow-hidden bg-zinc-900 mb-3 last:mb-0 border border-zinc-800 rounded-xl']) }}>

    <div class="flex items-center justify-between gap-2 bg-zinc-800 text-white px-3 py-1.5 border-b border-zinc-800">
        <flux:text class="text-xs text-white">
            {{ $fixture->group_letter ? 'Grupo ' . $fixture->group_letter : $group }}
        </flux:text>

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
        
            save: Alpine.debounce(function() { $wire.save({{ $fixture->id }}, this.home ?? 0, this.away ?? 0) }, 800),
        
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
                {{-- blade-formatter-disable --}}
                Resultado: {{ $result['home'] }} x {{ $result['away'] }}@if ($fixture->duration !== Duration::Regular) ({{ $fixture->duration->getLabel() }})@endif
                {{-- blade-formatter-enable --}}
            </flux:text>
        </div>
    @endif

</div>
