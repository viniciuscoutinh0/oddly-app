<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Pool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Dashboard extends Component
{
    /**
     * @return Collection<int, Pool>
     */
    public function pools(): Collection
    {
        $userId = auth()->id();

        return Pool::query()
            ->where('owner_id', $userId)
            ->orWhereHas('participants', fn (Builder $query) => $query->whereKey($userId))
            ->withCount('participants')
            ->with('season')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.dashboard', ['pools' => $this->pools()]);
    }
}
