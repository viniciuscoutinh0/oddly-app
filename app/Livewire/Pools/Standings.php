<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Models\Pool;
use App\Services\Pool\PoolStandings;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

#[On('prize-distribution::saved')]
final class Standings extends Component
{
    #[Locked]
    public Pool $pool;

    public function mount(): void
    {
        $this->pool = $this->pool->load([
            'season',
            'participants.bets',
            'distributions',
        ]);
    }

    #[Computed]
    public function standings(): Collection
    {
        return app(PoolStandings::class)->for($this->pool);
    }

    #[Computed]
    public function leaders(): Collection
    {
        return $this->standings->take(3);
    }

    #[Computed]
    public function others(): Collection
    {
        return $this->standings->skip(3);
    }

    public function render(): View
    {
        return view('livewire.pools.standings');
    }
}
