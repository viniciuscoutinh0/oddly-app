<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Models\Pool;
use App\Services\Pool\PoolStandings;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class Standings extends Component
{
    #[Locked]
    public Pool $pool;

    public function mount(): void
    {
        $this->pool = $this->pool->load(['season', 'participants']);
    }

    #[Computed]
    public function standings(): Collection
    {
        return app(PoolStandings::class)->for($this->pool);
    }

    public function render(): View
    {
        return view('livewire.pools.standings');
    }
}
