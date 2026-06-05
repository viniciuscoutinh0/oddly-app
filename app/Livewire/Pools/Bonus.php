<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Bet\PlaceChampionBetAction;
use App\Actions\Bet\PlaceGroupBetAction;
use App\Models\Pool;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Bonus extends Component
{
    public Pool $pool;

    public ?int $championTeamId = null;

    /**
     * @var array<string, array{first: int|null, second: int|null}>
     */
    public array $groups = [];

    public bool $saved = false;

    public function mount(Pool $pool): void
    {
        abort_unless($pool->participants()->whereKey(auth()->id())->exists(), 403);

        $pool->loadMissing('season');
        $this->pool = $pool;

        $season = $pool->season;

        $this->championTeamId = $season->championBets()
            ->where('user_id', auth()->id())
            ->value('team_id');

        $groupBets = $season->groupBets()->where('user_id', auth()->id())->get();

        foreach ($this->groupLetters() as $letter) {
            $this->groups[$letter] = [
                'first' => $groupBets->first(fn ($bet): bool => $bet->group_letter === $letter && $bet->predicted_position === 1)?->team_id,
                'second' => $groupBets->first(fn ($bet): bool => $bet->group_letter === $letter && $bet->predicted_position === 2)?->team_id,
            ];
        }
    }

    public function save(PlaceChampionBetAction $champion, PlaceGroupBetAction $group): void
    {
        $this->saved = false;

        if ($this->pool->season->bonusLocked()) {
            return;
        }

        $user = auth()->user();
        $season = $this->pool->season;

        if ($this->championTeamId !== null) {
            $champion->handle($user, $season, (int) $this->championTeamId);
        }

        foreach ($this->groups as $letter => $positions) {
            if (! empty($positions['first']) && ! empty($positions['second'])) {
                $group->handle($user, $season, (string) $letter, [
                    1 => (int) $positions['first'],
                    2 => (int) $positions['second'],
                ]);
            }
        }

        $this->saved = true;
    }

    public function locked(): bool
    {
        return $this->pool->season->bonusLocked();
    }

    /**
     * @return Collection<int, string>
     */
    public function groupLetters(): Collection
    {
        return $this->pool->season->teams()
            ->wherePivotNotNull('group_letter')
            ->get()
            ->pluck('pivot.group_letter')
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * @return Collection<int, Team>
     */
    public function teamsInGroup(string $letter): Collection
    {
        return $this->pool->season->teams()
            ->wherePivot('group_letter', $letter)
            ->get();
    }

    /**
     * @return Collection<int, Team>
     */
    public function allTeams(): Collection
    {
        return $this->pool->season->teams()->get();
    }

    public function render(): View
    {
        return view('livewire.pools.bonus', [
            'groupLetters' => $this->groupLetters(),
            'allTeams' => $this->allTeams(),
        ]);
    }
}
