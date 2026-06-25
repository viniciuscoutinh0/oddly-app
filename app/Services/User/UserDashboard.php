<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\Fixture\Status;
use App\Models\Bet;
use App\Models\Fixture;
use App\Models\Pool;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final readonly class UserDashboard
{
    /** @return Collection<int, Pool> */
    public function poolsForUser(User $user): Collection
    {
        $pools = Pool::query()
            ->where(function (Builder $query) use ($user): void {
                $query
                    ->where('owner_id', $user->id)
                    ->orWhereIn('id', $user->pools()->select('pools.id'));
            })
            ->withCount('participants')
            ->with([
                'season' => fn ($query) => $query
                    ->with('competition:id,name')
                    ->withCount([
                        'fixtures',
                        'fixtures as fixtures_finished_count' => fn (Builder $query): Builder => $query->finished(),
                    ]),
            ])
            ->get(['id', 'name', 'season_id']);

        if ($pools->isEmpty()) {
            return $pools;
        }

        $this->hydratePendingBetCounts($user, $pools);

        return $pools;
    }

    /** @return Collection<int, array{fixture: Fixture, pool: Pool}> */
    public function urgentFixtures(User $user, Collection $pools): Collection
    {
        if ($pools->isEmpty()) {
            return collect();
        }

        $poolIds = $pools->pluck('id');
        $seasonIds = $pools->pluck('season_id')->unique();
        $poolsBySeasonId = $pools->groupBy('season_id');

        $fixtures = Fixture::query()
            ->whereIn('stage_id', Stage::whereIn('season_id', $seasonIds)->select('id'))
            ->where('status', Status::Scheduled->value)
            ->where('match_date', '>', now())
            ->where('match_date', '<=', now()->addHours(4))
            ->with([
                'homeTeam:id,name,short_name,logo_url',
                'awayTeam:id,name,short_name,logo_url',
                'stage:id,season_id',
            ])
            ->orderBy('match_date')
            ->get();

        if ($fixtures->isEmpty()) {
            return collect();
        }

        $bettedPairs = Bet::query()
            ->where('user_id', $user->id)
            ->whereIn('fixture_id', $fixtures->pluck('id'))
            ->whereIn('pool_id', $poolIds)
            ->select(['fixture_id', 'pool_id'])
            ->get()
            ->map(fn (Bet $bet): string => "{$bet->fixture_id}:{$bet->pool_id}")
            ->flip();

        return $fixtures
            ->flatMap(function (Fixture $fixture) use ($poolsBySeasonId, $bettedPairs): Collection {
                return ($poolsBySeasonId->get($fixture->stage->season_id) ?? collect())
                    ->filter(fn (Pool $pool): bool => ! $bettedPairs->has("{$fixture->id}:{$pool->id}"))
                    ->map(fn (Pool $pool): array => ['fixture' => $fixture, 'pool' => $pool]);
            })
            ->take(5);
    }

    /** @return Collection<int, Fixture> */
    public function upcomingFixtures(Collection $pools): Collection
    {
        if ($pools->isEmpty()) {
            return collect();
        }

        $seasonIds = $pools->pluck('season_id')->unique();

        return Fixture::query()
            ->whereIn('stage_id', Stage::whereIn('season_id', $seasonIds)->select('id'))
            ->where('status', Status::Scheduled->value)
            ->where('match_date', '>', now())
            ->with([
                'homeTeam:id,name,short_name,logo_url',
                'awayTeam:id,name,short_name,logo_url',
            ])
            ->orderBy('match_date')
            ->limit(5)
            ->get();
    }

    private function hydratePendingBetCounts(User $user, Collection $pools): void
    {
        $seasonIds = $pools->pluck('season_id')->unique();
        $poolIds = $pools->pluck('id');

        $pendingFixtures = Fixture::query()
            ->whereIn('stage_id', Stage::whereIn('season_id', $seasonIds)->select('id'))
            ->where('status', Status::Scheduled->value)
            ->where('match_date', '>', now())
            ->select(['id', 'stage_id'])
            ->with('stage:id,season_id')
            ->get();

        $placedBets = Bet::query()
            ->where('user_id', $user->id)
            ->whereIn('fixture_id', $pendingFixtures->pluck('id'))
            ->whereIn('pool_id', $poolIds)
            ->select(['fixture_id', 'pool_id'])
            ->get()
            ->map(fn (Bet $bet): string => "{$bet->fixture_id}:{$bet->pool_id}")
            ->flip();

        $fixturesBySeasonId = $pendingFixtures->groupBy(fn (Fixture $fixture): int => $fixture->stage->season_id);

        $pools->each(function (Pool $pool) use ($fixturesBySeasonId, $placedBets): void {
            $seasonFixtures = $fixturesBySeasonId->get($pool->season_id) ?? collect();
            $pool->pending_bets_count = $seasonFixtures
                ->filter(fn (Fixture $fixture): bool => ! $placedBets->has("{$fixture->id}:{$pool->id}"))
                ->count();
        });
    }
}
