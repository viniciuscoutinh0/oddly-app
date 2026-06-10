<div x-data="{
    index: $persist(0).as('bets-index'),

    stages: @js($this->groups->keys()),

    init() { if (this.index > this.stages.length - 1) this.index = 0; },

    get currentStage() { return this.stages[this.index]; },

    get canPrevious() { return this.index > 0; },

    get canNext() { return this.index < this.stages.length - 1; },

    previous() { if (this.canPrevious) this.index -= 1; },

    next() { if (this.canNext) this.index += 1; },
}">
    <div class="flex items-center justify-between gap-3 mb-6 border bg-zinc-900 border-zinc-800 p-1.5 rounded-xl">
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
                    wire:key="fixture-{{ $fixture->id }}"
                />
            @endforeach
        </div>
    @endforeach
</div>
