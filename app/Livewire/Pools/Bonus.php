<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Bet\PlaceChampionBetAction;
use App\Actions\Bet\SyncGroupBetsAction;
use App\Models\Pool;
use App\Models\Team;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Bonus extends Component
{
    private const TEAMS_PER_GROUP = 2;

    #[Locked]
    public Pool $pool;

    public ?int $championTeamId = null;

    public array $groups = [];

    #[Locked]
    public $points = [];

    public function mount(Pool $pool): void
    {
        abort_unless(Gate::allows('bet', $pool), code: 403);

        $this->pool = $pool->loadMissing('season');

        $this->fillExistingPredictions();
    }

    public function updatedChampionTeamId(): void
    {
        $this->saveChampion();
    }

    public function updatedGroups(mixed $value, string $key): void
    {
        $this->saveGroup($key);
    }

    public function toggleGroup(string $letter, int $teamId): void
    {
        if ($this->locked) {
            return;
        }

        $current = $this->groups[$letter] ?? [];

        if (in_array($teamId, $current, true)) {
            $current = array_values(array_filter($current, fn (int $id): bool => $id !== $teamId));
        } elseif (count($current) < self::TEAMS_PER_GROUP) {
            $current[] = $teamId;
        } else {
            return;
        }

        $this->groups[$letter] = $current;

        $this->saveGroup($letter);
    }

    public function saveChampion(): void
    {
        if ($this->locked) {
            return;
        }

        $season = $this->pool->season;

        if ($this->championTeamId === null) {
            $season->championBets()->where('user_id', Auth::id())->delete();

            return;
        }

        if (! $this->allTeams->contains('id', $this->championTeamId)) {
            return;
        }

        app(PlaceChampionBetAction::class)->handle(Auth::user(), $season, $this->championTeamId);
    }

    public function saveGroup(string $letter): void
    {
        if ($this->locked) {
            return;
        }

        $valid = $this->teamsInGroup($letter)->pluck('id');

        $teamIds = collect($this->groups[$letter] ?? [])
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $valid->contains($id))
            ->unique()
            ->take(self::TEAMS_PER_GROUP)
            ->values()
            ->all();

        $this->groups[$letter] = $teamIds;

        app(SyncGroupBetsAction::class)->handle(Auth::user(), $this->pool->season, $letter, $teamIds);
    }

    #[Computed]
    public function locked(): bool
    {
        return $this->pool->season->bonusLocked();
    }

    #[Computed]
    public function allTeams(): Collection
    {
        return $this->pool->season->teams()->get();
    }

    #[Computed]
    public function teamsByGroup(): Collection
    {
        return $this->allTeams
            ->filter(fn (Team $team): bool => filled($team->pivot->group_letter))
            ->groupBy(fn (Team $team): string => (string) $team->pivot->group_letter)
            ->sortKeys();
    }

    #[Computed]
    public function groupLetters(): Collection
    {
        return $this->teamsByGroup->keys();
    }

    public function teamsInGroup(string $letter): Collection
    {
        return $this->teamsByGroup->get($letter, collect())->values();
    }

    public function render(): View
    {
        return view('livewire.pools.bonus');
    }

    private function fillExistingPredictions(): void
    {
        $season = $this->pool->season;

        $this->championTeamId = $season
            ->championBets()
            ->where('user_id', Auth::id())
            ->value('team_id');

        $groupBets = $season
            ->groupBets()
            ->where('user_id', Auth::id())
            ->get()
            ->groupBy('group_letter');

        $this->points = $groupBets->map(
            fn (Collection $bets) => $bets->sum(fn ($bet): int => $bet->is_correct ? $this->pool->points_group_position : 0)
        );

        foreach ($this->groupLetters as $letter) {
            $this->groups[$letter] = $groupBets
                ->get($letter, collect())
                ->sortBy('predicted_position')
                ->pluck('team_id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all();
        }
    }
}
