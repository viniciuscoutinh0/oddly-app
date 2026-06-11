<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Competition;
use App\Models\Pool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * @property Collection<Pool> $pools
 * @property Collection<Competition> $competitions
 */
#[Layout('layouts.dashboard')]
final class Dashboard extends Component
{
    #[Computed]
    public function competitions(): Collection
    {
        return Competition::query()->withCount('seasons')->get(['id', 'name']);
    }

    #[Computed]
    public function pools(): Collection
    {
        $userId = Auth::id();

        return Pool::query()
            ->where(function (Builder $query) use ($userId): void {
                $query
                    ->where('owner_id', $userId)
                    ->orWhereHas('participants', fn (Builder $query): Builder => $query->whereKey($userId));
            })
            ->withCount('participants')
            ->with('season.competition:id,name')
            ->get([
                'id',
                'name',
            ]);
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
