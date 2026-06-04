<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bet;
use App\Models\ChampionBet;
use App\Models\GroupBet;
use App\Models\Pool;
use App\Models\User;
use Illuminate\Support\Collection;

final class PoolStandings
{
    /**
     * @return Collection<int, array{user: User, points: int}>
     */
    public function for(Pool $pool): Collection
    {
        $season = $pool->season;
        $fixtureIds = $season->fixtures()->pluck('fixtures.id');
        $participants = $pool->participants()->get();
        $participantIds = $participants->pluck('id');

        $betPoints = Bet::query()
            ->whereIn('fixture_id', $fixtureIds)
            ->whereIn('user_id', $participantIds)
            ->get()
            ->groupBy('user_id')
            ->map(fn (Collection $bets): int => $bets->sum(fn (Bet $bet): int => match (true) {
                $bet->is_exact === true => $pool->points_exact,
                $bet->is_correct_result === true => $pool->points_result,
                default => 0,
            }));

        $championPoints = ChampionBet::query()
            ->where('season_id', $season->id)
            ->whereIn('user_id', $participantIds)
            ->where('is_correct', true)
            ->pluck('user_id')
            ->mapWithKeys(fn (int $userId): array => [$userId => $pool->points_champion]);

        $groupPoints = GroupBet::query()
            ->where('season_id', $season->id)
            ->whereIn('user_id', $participantIds)
            ->where('is_correct', true)
            ->get()
            ->groupBy('user_id')
            ->map(fn (Collection $bets): int => $bets->count() * $pool->points_group_position);

        return $participants
            ->map(fn ($user): array => [
                'user' => $user,
                'points' => (int) ($betPoints[$user->id] ?? 0)
                    + (int) ($championPoints[$user->id] ?? 0)
                    + (int) ($groupPoints[$user->id] ?? 0),
            ])
            ->sortByDesc('points')
            ->values();
    }
}
