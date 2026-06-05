<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Models\Pool;
use App\Models\User;
use App\Services\PoolStandings;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Standings extends Component
{
    public Pool $pool;

    public function mount(Pool $pool): void
    {
        abort_unless($pool->participants()->whereKey(auth()->id())->exists(), 403);

        $this->pool = $pool;
    }

    /**
     * @return Collection<int, array{user: User, points: int}>
     */
    public function standings(): Collection
    {
        return app(PoolStandings::class)->for($this->pool);
    }

    public function render(): View
    {
        return view('livewire.pools.standings', ['standings' => $this->standings()]);
    }
}
