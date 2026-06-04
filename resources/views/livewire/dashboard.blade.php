<div>
    <flux:heading size="xl" class="mb-6">Meus bolões</flux:heading>

    @forelse ($pools as $pool)
        <flux:card class="mb-4">
            <flux:heading size="lg">{{ $pool->name }}</flux:heading>
            <flux:text>Temporada {{ $pool->season->name }}</flux:text>
            <flux:text>{{ $pool->participants_count }} participante(s)</flux:text>
        </flux:card>
    @empty
        <flux:card class="text-center space-y-4">
            <flux:heading size="lg">Você ainda não está em nenhum bolão</flux:heading>
            <div class="flex gap-3 justify-center">
                <flux:button href="#" variant="primary" color="cyan">Criar bolão</flux:button>
                <flux:button href="#" variant="ghost">Entrar em bolão</flux:button>
            </div>
        </flux:card>
    @endforelse
</div>
