<div class="max-w-xl mx-auto">
    <flux:heading size="xl" class="mb-6">Criar bolão</flux:heading>

    <form wire:submit="create" class="space-y-6">
        <flux:input label="Nome" required wire:model="name" />
        <flux:textarea label="Descrição" wire:model="description" />

        <flux:select label="Temporada" wire:model="season_id">
            <flux:select.option value="">Selecione…</flux:select.option>
            @foreach ($seasons as $season)
                <flux:select.option :value="$season->id">{{ $season->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select label="Visibilidade" wire:model="visibility">
            <flux:select.option value="public">Público</flux:select.option>
            <flux:select.option value="private">Privado</flux:select.option>
        </flux:select>

        <flux:heading size="lg">Pontuação</flux:heading>
        <div class="grid grid-cols-2 gap-4">
            <flux:input type="number" label="Placar exato" wire:model="points_exact" />
            <flux:input type="number" label="Resultado" wire:model="points_result" />
            <flux:input type="number" label="Campeão" wire:model="points_champion" />
            <flux:input type="number" label="Posição no grupo" wire:model="points_group_position" />
        </div>

        <flux:button type="submit" variant="primary" color="cyan">Criar bolão</flux:button>
    </form>
</div>
