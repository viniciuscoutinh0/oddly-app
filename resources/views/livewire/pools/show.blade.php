<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ $pool->name }}</flux:heading>
        @if ($this->canLeave())
            <flux:button wire:click="leave" variant="danger">Sair do bolão</flux:button>
        @endif
    </div>

    <flux:text>Temporada {{ $pool->season->name }} · {{ $pool->visibility->getLabel() }}</flux:text>

    @if ($this->canSeeInviteCode())
        <flux:callout>
            Código de convite: <strong>{{ $pool->invite_code }}</strong>
        </flux:callout>
    @endif

    <flux:card>
        <flux:heading size="lg">Pontuação</flux:heading>
        <flux:text>Placar exato: {{ $pool->points_exact }} · Resultado: {{ $pool->points_result }} · Campeão: {{ $pool->points_champion }} · Grupo: {{ $pool->points_group_position }}</flux:text>
    </flux:card>

    <flux:card>
        <flux:heading size="lg" class="mb-2">Participantes</flux:heading>
        <ul class="space-y-1">
            @foreach ($pool->participants as $participant)
                <li>{{ $participant->name }}</li>
            @endforeach
        </ul>
    </flux:card>

    <div class="flex gap-3">
        <flux:button :href="route('pools.bets', $pool)" variant="ghost">Palpites</flux:button>
        <flux:button href="#" variant="ghost">Ranking</flux:button>
    </div>
</div>
