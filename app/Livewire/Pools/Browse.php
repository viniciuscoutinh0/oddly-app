<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Pool\JoinPoolAction;
use App\Enums\Pool\Visibility;
use App\Models\Pool;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Browse extends Component
{
    public function join(int $poolId, JoinPoolAction $action): void
    {
        $pool = Pool::where('visibility', Visibility::Public)->findOrFail($poolId);

        $action->handle(Auth::user(), $pool);

        $this->redirectRoute('pools.show', $pool);
    }

    /**
     * @return Collection<int, Pool>
     */
    public function pools(): Collection
    {
        return Pool::query()
            ->where('visibility', Visibility::Public)
            ->withCount('participants')
            ->with('season')
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.pools.browse', ['pools' => $this->pools()]);
    }
}
