<div class="rounded-sm overflow-hidden border-accent border max-w-xs w-full shrink-0">
    <div class="relative flex flex-col justify-center p-6 gap-4 bg-accent text-white overflow-hidden">
        <flux:heading size="xl">
            Entrar com convite
        </flux:heading>

        <flux:text class="text-sm">
            Recebeu um link ou código? Entre agora e comece a palpitar.
        </flux:text>

        <div class="absolute -top-16 -right-16 select-auto opacity-15">
            <x-heroicon-s-user-group class="w-64 h-64" />
        </div>
    </div>

    <flux:field class="p-6">
        <flux:label for="code">Código</flux:label>

        <flux:input.group>
            <flux:input
                id="code"
                placeholder="Exemplo: JDZ3BHUR"
                autocomplete="off"
                wire:model="code"
            />

            <flux:button
                icon="bolt"
                variant="primary"
                wire:click="join"
            >
                Entrar
            </flux:button>
        </flux:input.group>

        <flux:error name="code" />
    </flux:field>
</div>
