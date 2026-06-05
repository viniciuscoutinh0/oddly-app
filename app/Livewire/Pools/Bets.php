<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Bet\PlaceBetAction;
use App\Models\Bet;
use App\Models\Fixture;
use App\Models\Pool;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Bets extends Component
{
    public Pool $pool;

    /**
     * @var array<int, array{home: int|string|null, away: int|string|null}>
     */
    public array $scores = [];

    public bool $saved = false;

    private ?Collection $fixturesCache = null;

    public function mount(Pool $pool): void
    {
        abort_unless($pool->participants()->whereKey(auth()->id())->exists(), 403);

        $pool->loadMissing('season');
        $this->pool = $pool;

        $fixtures = $this->fixtures();

        $bets = Bet::query()
            ->where('user_id', auth()->id())
            ->whereIn('fixture_id', $fixtures->pluck('id'))
            ->get()
            ->keyBy('fixture_id');

        foreach ($fixtures as $fixture) {
            $this->scores[$fixture->id] = [
                'home' => $bets[$fixture->id]->home_score ?? null,
                'away' => $bets[$fixture->id]->away_score ?? null,
            ];
        }
    }

    public function save(PlaceBetAction $action): void
    {
        $this->saved = false;

        $user = auth()->user();

        foreach ($this->fixtures() as $fixture) {
            if ($fixture->isLocked()) {
                continue;
            }

            $home = $this->scores[$fixture->id]['home'] ?? null;
            $away = $this->scores[$fixture->id]['away'] ?? null;

            if ($home === null || $home === '' || $away === null || $away === '') {
                continue;
            }

            $action->handle($user, $fixture, (int) $home, (int) $away);
        }

        $this->saved = true;
    }

    /**
     * @return Collection<int, Fixture>
     */
    protected function fixtures(): Collection
    {
        return $this->fixturesCache ??= $this->pool->season->fixtures()
            ->with(['homeTeam', 'awayTeam', 'stage'])
            ->get()
            ->sortBy(fn ($fixture): string => sprintf(
                '%03d-%s',
                $fixture->stage->sort_order,
                $fixture->match_date->format('YmdHis'),
            ))
            ->values();
    }

    public function render(): View
    {
        return view('livewire.pools.bets', ['fixtures' => $this->fixtures()]);
    }
}
