<div class="space-y-6">
    <flux:heading size="xl">{{ $pool->name }} · Bônus</flux:heading>

    @if ($saved)
        <flux:callout variant="success">Bônus salvos.</flux:callout>
    @endif

    @if ($this->locked())
        <flux:callout variant="warning">Os palpites bônus estão encerrados.</flux:callout>
    @endif

    <form wire:submit="save" class="space-y-8">
        <flux:card>
            <flux:heading size="lg" class="mb-3">Campeão</flux:heading>
            <flux:select wire:model="championTeamId" :disabled="$this->locked()">
                <flux:select.option value="">Selecione…</flux:select.option>
                @foreach ($allTeams as $team)
                    <flux:select.option :value="$team->id">{{ $team->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:card>

        @foreach ($groupLetters as $letter)
            <flux:card>
                <flux:heading size="lg" class="mb-3">Grupo {{ $letter }}</flux:heading>
                <div class="grid grid-cols-2 gap-4">
                    <flux:select label="1º" wire:model="groups.{{ $letter }}.first" :disabled="$this->locked()">
                        <flux:select.option value="">Selecione…</flux:select.option>
                        @foreach ($this->teamsInGroup($letter) as $team)
                            <flux:select.option :value="$team->id">{{ $team->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select label="2º" wire:model="groups.{{ $letter }}.second" :disabled="$this->locked()">
                        <flux:select.option value="">Selecione…</flux:select.option>
                        @foreach ($this->teamsInGroup($letter) as $team)
                            <flux:select.option :value="$team->id">{{ $team->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </flux:card>
        @endforeach

        @unless ($this->locked())
            <flux:button type="submit" variant="primary" color="cyan">Salvar bônus</flux:button>
        @endunless
    </form>
</div>
