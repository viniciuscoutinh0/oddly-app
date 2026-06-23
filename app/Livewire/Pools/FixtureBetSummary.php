<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Models\Bet;
use App\Models\Fixture;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

final class FixtureBetSummary extends Component
{
    #[Locked]
    public int $poolId;

    #[Locked]
    public ?int $fixtureId = null;

    #[On('show')]
    public function onShow(int $fixtureId): void
    {
        $this->fixtureId = $fixtureId;

        Flux::modal('fixture-bet-summary-modal')->show();
    }

    /** @return Collection<int, Bet> */
    #[Computed]
    public function bets(): Collection
    {
        if ($this->fixtureId === null) {
            return collect();
        }

        return Bet::query()
            ->where('pool_id', $this->poolId)
            ->where('fixture_id', $this->fixtureId)
            ->with(['user:id,name', 'pool:id,points_exact,points_result'])
            ->orderBy(DB::raw('is_exact + is_correct_result'), 'DESC')
            ->get();
    }

    #[Computed]
    public function fixture(): ?Fixture
    {
        if ($this->fixtureId === null) {
            return null;
        }

        return Fixture::query()
            ->with([
                'homeTeam:id,tla,logo_url',
                'awayTeam:id,tla,logo_url',
            ])
            ->find($this->fixtureId);
    }

    /** @return array{home: \App\Models\Team, away: \App\Models\Team} */
    #[Computed]
    public function teams(): array
    {
        return [
            'home' => $this->fixture?->homeTeam,
            'away' => $this->fixture?->awayTeam,
        ];
    }

    public function render(): View
    {
        return view('livewire.pools.fixture-bet-summary');
    }
}
