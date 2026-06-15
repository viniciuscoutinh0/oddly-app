<flux:modal
    name="prize-distribution-manager"
    class="md:w-md"
>
    <div class="space-y-8">
        <flux:heading size="lg">Gerenciar Premiação</flux:heading>

        <div class="flex items-center justify-between">
            <flux:text class="mt-2">
                Total disponível {{ Number::currency($this->rest) }}
            </flux:text>
            <flux:button
                icon="plus"
                variant="outline"
                size="sm"
                wire:click="addPosition"
            >
                Adicionar posição
            </flux:button>
        </div>

        @foreach ($distributions as $index => $distribution)
            <flux:field
                class="mb-3 last:mb-0"
                wire:key="distribution-{{ $distribution['position'] }}"
            >
                <flux:label>
                    {{ $distribution['position'] }}º Posição

                    <x-slot name="trailing">
                        <span class="tabular-nums">
                            {{ Number::currency(($distribution['percentage'] * $pool->entry_fee) / 100) }}
                        </span>

                    </x-slot>
                </flux:label>
                <div class="flex items-center gap-3 -mt-2">
                    <flux:slider wire:model.live.blur="distributions.{{ $index }}.percentage" />
                    <flux:input
                        wire:model.live.blur="distributions.{{ $index }}.percentage"
                        type="number"
                        size="sm"
                        class="max-w-18"
                    />
                </div>
            </flux:field>
        @endforeach

        <div class="flex">
            <flux:spacer />
            <flux:button
                variant="primary"
                wire:click="save"
            >Save changes</flux:button>
        </div>
    </div>
</flux:modal>
