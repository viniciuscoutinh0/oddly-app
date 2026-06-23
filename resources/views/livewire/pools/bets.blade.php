<div x-data="{
    index: $persist(0).as('bets-index'),

    current: '{{ $this->current }}',

    stages: @js($this->groups->keys()),

    init() {
        if (this.current) {
            const index = this.stages.findIndex(s => s === this.current);

            if (!index) return;

            this.index = index;
        }

        if (this.index > this.stages.length - 1) this.index = 0;
    },

    get currentStage() { return this.stages[this.index]; },

    get canPrevious() { return this.index > 0; },

    get canNext() { return this.index < this.stages.length - 1; },

    previous() { if (this.canPrevious) this.index -= 1; },

    next() { if (this.canNext) this.index += 1; },
}">
    <div
        class="flex items-center justify-between gap-3 mb-6 border bg-zinc-900/75 backdrop-blur-sm border-zinc-800 p-1.5 rounded-xl sticky z-50 inset-0">
        <flux:button
            icon="arrow-left"
            variant="filled"
            x-on:click="previous()"
            x-bind:disabled="!canPrevious"
        />

        <flux:heading x-text="currentStage ?? 'N/A'" />

        <flux:button
            icon="arrow-right"
            variant="filled"
            x-on:click="next()"
            x-bind:disabled="!canNext"
        />
    </div>

    <flux:callout
        color="sky"
        icon="information-circle"
        heading="Você já deu palpite em {{ $this->bets->count() }} de {{ $this->fixtures->count() }} {{ str('partida')->plural($this->fixtures->count()) }}."
        class="mb-6"
    />

    @foreach ($this->groups as $group => $fixtures)
        <div
            wire:key="group-{{ str($group)->slug() }}"
            x-show="currentStage === @js($group)"
            x-cloak
        >
            @foreach ($fixtures as $fixture)
                <x-bet
                    :$fixture
                    :$group
                    :beted="isset($this->bets[$fixture->id])"
                    wire:key="fixture-{{ $fixture->id }}"
                />
            @endforeach
        </div>
    @endforeach
</div>
