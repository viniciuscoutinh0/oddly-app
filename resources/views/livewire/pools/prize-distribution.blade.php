<flux:modal
    class="md:w-md"
    name="prize-distribution-manager"
    flyout
    variant="floating"
>
    <div
        x-data="{
            amount: @js($this->totalAward ?? 0),
        
            distributions: $wire.entangle('form.distributions'),
        
            add() {
                const keys = Object.keys(this.distributions)
                    .map(Number)
                    .filter(key => !isNaN(key) && this.distributions[key] !== undefined);
        
                const nextPosition = keys.length > 0 ? Math.max(...keys) + 1 : 1;
        
                this.distributions[nextPosition] = 0;
            },
        
            clear() {
                if (this.isEmpty) return;
        
                this.distributions = {};
            },
        
            get allocations() {
                const total = parseFloat(this.amount);
                let calculated = {};
        
                Object.keys(this.distributions).forEach(position => {
                    const percentage = parseFloat(this.distributions[position]) || 0;
                    calculated[position] = this._round((total * (percentage / 100)), 2);
                });
        
                return calculated;
            },
        
            get isEmpty() {
                return Object.keys(this.distributions ?? {}).length === 0;
            },
        
            get totalPercentage() {
                return Object.values(this.distributions)
                    .reduce((sum, value) => sum + (parseFloat(value) || 0), 0);
            },
        
            get isFullAllocated() {
                return Math.abs(this.totalPercentage - 100) < 0.0001;
            },
        
            get availableAmount() {
                const totalAllocatedInMoney = Object.values(this.allocations)
                    .reduce((sum, value) => sum + value, 0);
        
                return Math.max(0, this.amount - totalAllocatedInMoney);
            },
        
            limit(event, position) {
                let value = parseInt(event.target.value) || 0;
        
                const old = parseInt(this.distributions[position]) || 0;
        
                const total = this.totalPercentage - old;
        
                const maxAllowed = Math.max(0, 100 - total);
        
                if (value > maxAllowed) {
                    value = maxAllowed;
                }
        
                this.distributions[position] = value;
                event.target.value = value;
            },
        
            money(value) {
                return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0)
            },
        
            _round(value, decimals) {
                return Number(Math.round(value + 'e' + decimals) + 'e-' + decimals);
            }
        }"
        class="flex flex-col gap-y-6 h-full"
    >
        <div>
            <flux:heading
                size="lg"
                class="mb-6"
            >
                Definir Premiação
            </flux:heading>

            <div class="flex items-center justify-between gap-4 bg-zinc-900 rounded-xl border border-zinc-700 p-3">
                <div class="grid gap-1">
                    <flux:text class="text-sm">
                        Prêmio Total: <strong
                            class="text-accent underline underline-offset-1"
                            x-text="money(amount)"
                        ></strong>
                    </flux:text>
                    <flux:text class="text-sm">
                        Restam: <strong x-text="money(availableAmount)"></strong>
                    </flux:text>
                </div>

                <flux:button.group class="shrink-0">
                    <flux:button
                        icon="plus"
                        variant="outline"
                        size="sm"
                        x-on:click="add"
                        x-bind:disabled="isFullAllocated"
                        tooltip="Adicionar Posição"
                    />

                    <flux:button
                        icon="trash"
                        variant="outline"
                        size="sm"
                        x-on:click="clear"
                        x-bind:disabled="isEmpty"
                        tooltip="Limpar tudo"
                    />
                </flux:button.group>
            </div>
        </div>

        <div class="flex-1 min-h-0 overflow-y-auto space-y-4 pr-1.5">
            <template
                x-for="(distribution, position) in distributions"
                :key="position"
            >
                <div>
                    <flux:field class="mb-3 last:mb-0">
                        <flux:label>
                            <span x-text="`${position}º Lugar`"></span>
                            <x-slot name="trailing">
                                <flux:text
                                    variant="subtle"
                                    class="text-xs tabular-nums"
                                    x-text="money(allocations[position] || 0)"
                                />
                            </x-slot>
                        </flux:label>

                        <div class="flex items-center gap-4 -mt-1">
                            <div class="flex-1">
                                <flux:slider
                                    min="0"
                                    step="1"
                                    max="100"
                                    x-on:input="limit($event, position)"
                                    x-model="distributions[position]"
                                />
                            </div>

                            <flux:input
                                type="number"
                                min="0"
                                max="100"
                                step="1"
                                size="sm"
                                class="max-w-18 shrink-0"
                                x-model="distributions[position]"
                                x-on:input="limit($event, position)"
                            />
                        </div>

                    </flux:field>

                    <flux:separator />
                </div>
            </template>
        </div>

        <div class="flex items-center justify-between pt-2">
            <flux:text
                size="sm"
                variant="subtle"
            >
                Distribuído: <span
                    class="font-bold"
                    x-text="`${Number(totalPercentage).toFixed()}%`"
                ></span> de 100%
            </flux:text>

            <flux:button
                variant="primary"
                wire:click="save"
                x-bind:disabled="!isFullAllocated"
            >
                Salvar Premiação
            </flux:button>
        </div>
    </div>
</flux:modal>
