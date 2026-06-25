<div
    class="pb-20 sm:pb-0"
    x-data="{
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
    }"
>
    <div
        class="flex items-center justify-between gap-3 border bg-zinc-900/75 backdrop-blur-sm border-zinc-800 z-50
               fixed bottom-0 inset-x-0 rounded-t-xl border-b-0 p-1.5 px-4
               sm:sticky sm:top-0 sm:bottom-auto sm:inset-x-auto sm:rounded-xl sm:border-b sm:mb-6 sm:px-1.5">
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

    <div
        class="overflow-hidden relative bg-zinc-900 mb-6 border border-zinc-800 rounded-xl transition duration-75 p-4 ring-accent">
        <flux:field class="z-10 relative">
            <flux:label>Seus palpites</flux:label>

            <flux:progress
                :value="round(($this->bets->count() / $this->fixtures->count()) * 100)"
                color="blue"
            />

            <flux:description>
                Você palpitou em {{ $this->bets->count() }} de {{ $this->fixtures->count() }}
                {{ str('jogo')->plural($this->fixtures->count()) }}.
            </flux:description>
        </flux:field>

        <div class="absolute -top-4 -right-16 select-none opacity-5">
            <x-heroicon-s-trophy class="w-32 h-32" />
        </div>
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
                    :beted="isset($this->bets[$fixture->id])"
                    :point="$points[$fixture->id]"
                    wire:key="fixture-{{ $fixture->id }}"
                />
            @endforeach
        </div>
    @endforeach
</div>
