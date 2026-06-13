<flux:modal
    name="prize-distribution-manager"
    class="md:w-96"
>
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Update profile</flux:heading>
            <flux:text class="mt-2">Total disponível {{ $this->totalPercentage }}</flux:text>
        </div>

        @for ($i = 0; $i < 3; $i++)
            <div
                class="flex"
                wire:key="position-{{ $i + 1 }}"
            >

                <flux:slider
                    wire:model.live="distributions.{{ $i }}.percentage"
                    :min="0"
                    :max="100"
                >
                    <flux:label>
                        {{ $i + 1 }}º
                        <x-slot name="trailing">
                            <span
                                wire:text="{{ $this->totalPercentage }}"
                                class="tabular-nums"
                            ></span>
                        </x-slot>
                    </flux:label>

                </flux:slider>
            </div>
        @endfor

        <div class="flex">
            <flux:spacer />
            <flux:button
                type="submit"
                variant="primary"
            >Save changes</flux:button>
        </div>
    </div>
</flux:modal>
