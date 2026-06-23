<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Models\Bet;
use App\Models\Fixture;
use App\Models\Team;
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

    #[Computed]
    public function hitsCount(): int
    {
        return $this->bets
            ->filter(fn (Bet $bet): bool => $bet->is_exact || $bet->is_correct_result)
            ->count();
    }

    #[Computed]
    public function missesCount(): int
    {
        return $this->bets->count() - $this->hitsCount;
    }

    /** @return array{home: int, away: int} */
    #[Computed]
    public function score(): array
    {
        $score = $this->fixture?->finalScore();

        return [
            'home' => $score['home'] ?? 0,
            'away' => $score['away'] ?? 0,
        ];
    }

    /** @return array{r: int, circumference: float, arc: float} */
    #[Computed]
    public function donut(): array
    {
        $raio = 38;
        $circumference = round(2 * M_PI * $raio, 2);

        $total = $this->bets->count();

        $arc = $total > 0 ? round(($this->hitsCount / $total) * $circumference, 2) : 0.0;

        return [
            'r' => $raio,
            'circumference' => $circumference,
            'arc' => $arc,
        ];
    }

    /** @return array{home: Team|null, away: Team|null} */
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
