<div>
    @can('leave', $pool)
        <flux:button
            variant="danger"
            icon="x-mark"
            wire:click="leave"
        >
            Sair
        </flux:button>
    @endcan
</div>
