<div class="relative rounded-xl p-px overflow-hidden w-full lg:max-w-xs shrink-0">
    <div
        class="absolute inset-[-1000%] blur-[1px] animate-[spin_3s_linear_infinite] bg-[conic-gradient(from_90deg_at_50%_50%,#f0f9ff_0%,#0ea5e9_15%,#0c4a6e_45%,#0c4a6e_55%,#0ea5e9_85%,#ffffff_100%)]">
    </div>

    <div
        class="relative flex flex-col justify-center p-4 md:p-6 gap-4 bg-accent text-white overflow-hidden rounded-t-[calc(0.75rem-1px)]">
        <flux:heading size="xl">
            Entrar com convite
        </flux:heading>

        <flux:text class="text-sm drop-shadow-sm">
            Recebeu um link ou código? Entre agora e comece a palpitar.
        </flux:text>

        <div class="absolute -top-16 -right-16 select-auto opacity-15">
            <x-heroicon-s-user-group class="w-64 h-64" />
        </div>
    </div>

    <flux:field class="p-4 md:p-6 bg-zinc-800 relative rounded-b-[calc(0.75rem-1px)]">
        <flux:label
            for="code"
            badge="Obrigátorio"
        >
            Código
        </flux:label>

        <flux:input.group>
            <flux:input
                id="code"
                class="flex-1"
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
