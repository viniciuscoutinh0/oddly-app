<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Models\Pool;
use App\Services\Season\GroupTable;
use App\Services\Season\ValueObjects\TeamStanding;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class GroupStandings extends Component
{
    #[Locked]
    public Pool $pool;

    public function mount(): void
    {
        $this->pool = $this->pool->loadMissing('season');
    }

    /** @return Collection<string, Collection<int,TeamStanding>> */
    #[Computed]
    public function groupedTeams(): Collection
    {
        return app(GroupTable::class)->for($this->pool->season);
    }

    public function render(): View
    {
        return view('livewire.pools.group-standings');
    }
}
