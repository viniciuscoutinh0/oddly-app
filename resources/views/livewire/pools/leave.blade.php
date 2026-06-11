<div>
    @can('leave', $pool)
        <flux:button
            variant="danger"
            icon="exclamation-triangle"
            wire:click="leave"
            wire:confirm="Você realmente deseja sair do bolão?"
        >
            Sair do Bolão
        </flux:button>
    @endcan
</div>
