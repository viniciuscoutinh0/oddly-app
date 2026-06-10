<div
    class="lg:col-span-2 flex flex-col items-center gap-4 text-center border border-dashed border-zinc-800 bg-zinc-900 rounded-xl p-4 md:p-6">
    <div class="w-12 h-12 rounded-md bg-zinc-800 flex items-center justify-center shrink-0">
        <x-heroicon-m-user-group class="w-6 h-6 text-accent" />
    </div>

    <div>
        <flux:heading size="lg">Você ainda não está em nenhum bolão</flux:heading>
        <flux:text
            variant="subtle"
            class="mt-1"
        >
            Crie o seu ou entre em um existente para começar a palpitar.
        </flux:text>
    </div>

    <div class="flex flex-col sm:flex-row gap-3">
        <flux:button
            :href="route('pools.create')"
            variant="primary"
            icon="plus"
        >
            Criar bolão
        </flux:button>

        <flux:button
            :href="route('pools.index')"
            variant="filled"
            icon="user-group"
        >
            Entrar em bolão
        </flux:button>
    </div>
</div>
