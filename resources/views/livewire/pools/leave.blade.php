<div>
    @can('leave', $pool)
        <flux:button
            variant="danger"
            icon="exclamation-triangle"
            wire:click="leave"
        >
            Sair do Bolão
        </flux:button>
    @endcan
</div>
