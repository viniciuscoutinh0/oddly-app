<div class="space-y-8">
    <div>
        <flux:heading size="xl" class="mb-4">Bolões públicos</flux:heading>

        @forelse ($pools as $pool)
            <flux:card class="mb-4 flex items-center justify-between">
                <div>
                    <flux:heading size="lg">{{ $pool->name }}</flux:heading>
                    <flux:text>Temporada {{ $pool->season->name }} · {{ $pool->participants_count }} participante(s)</flux:text>
                </div>
                <flux:button wire:click="join({{ $pool->id }})" variant="primary" color="cyan">Entrar</flux:button>
            </flux:card>
        @empty
            <flux:text>Nenhum bolão público ainda.</flux:text>
        @endforelse
    </div>

    <div class="max-w-md">
        <flux:heading size="lg" class="mb-2">Entrar por código</flux:heading>
        <form wire:submit="joinByCode" class="flex gap-2 items-end">
            <flux:input label="Código de convite" wire:model="inviteCode" />
            <flux:button type="submit">Entrar</flux:button>
        </form>
    </div>
</div>
