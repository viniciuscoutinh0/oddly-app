<div class="space-y-6">
    <flux:heading size="xl">{{ $pool->name }} · Palpites</flux:heading>

    <form wire:submit="save" class="space-y-8">
        @foreach ($fixtures->groupBy(fn ($fixture) => $fixture->stage->name->getLabel()) as $stageLabel => $stageFixtures)
            <flux:card>
                <flux:heading size="lg" class="mb-3">{{ $stageLabel }}</flux:heading>
                <div class="space-y-3">
                    @foreach ($stageFixtures as $fixture)
                        <div class="flex items-center gap-3">
                            <div class="flex-1 text-right">{{ $fixture->homeTeam?->name ?? 'A definir' }}</div>
                            <flux:input type="number" min="0" class="w-16" wire:model="scores.{{ $fixture->id }}.home" :disabled="$fixture->isLocked()" />
                            <span>x</span>
                            <flux:input type="number" min="0" class="w-16" wire:model="scores.{{ $fixture->id }}.away" :disabled="$fixture->isLocked()" />
                            <div class="flex-1">{{ $fixture->awayTeam?->name ?? 'A definir' }}</div>
                            <div class="w-40 text-sm text-zinc-500">
                                @if ($fixture->isLocked()) Encerrado @else {{ $fixture->match_date->format('d/m H:i') }} @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @endforeach

        <flux:button type="submit" variant="primary" color="cyan">Salvar palpites</flux:button>
    </form>
</div>
