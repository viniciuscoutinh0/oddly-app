<?php

declare(strict_types=1);

namespace App\Services\Season;

use App\Models\Season;
use App\Models\Team;
use App\Services\Season\ValueObjects\TeamStanding;
use Illuminate\Support\Collection;

final readonly class GroupTable
{
    /** @return Collection<string, Collection<int, TeamStanding>> */
    public function for(Season $season): Collection
    {
        $teams = $season
            ->teams()
            ->orderByPivot('group_letter')
            ->orderByPivot('group_position')
            ->get();

        $fixtures = $season
            ->fixtures()
            ->finished()
            ->whereNotNull('group_letter')
            ->get([
                'home_team_id',
                'away_team_id',
                'home_score',
                'away_score',
            ]);

        return $teams
            ->map(fn (Team $team): TeamStanding => $this->buildStanding($team, $fixtures))
            ->groupBy(fn (TeamStanding $standing): string => $standing->groupLetter);
    }

    private function buildStanding(Team $team, Collection $fixtures): TeamStanding
    {
        $matchesPlayed = 0;
        $wins = 0;
        $draws = 0;
        $losses = 0;
        $goalsFor = 0;
        $goalsAgainst = 0;

        foreach ($fixtures as $fixture) {
            if ($fixture->home_team_id !== $team->id && $fixture->away_team_id !== $team->id) {
                continue;
            }

            $isHome = $fixture->home_team_id === $team->id;
            $scored = (int) ($isHome ? $fixture->home_score : $fixture->away_score);
            $conceded = (int) ($isHome ? $fixture->away_score : $fixture->home_score);

            $matchesPlayed++;
            $goalsFor += $scored;
            $goalsAgainst += $conceded;

            if ($scored > $conceded) {
                $wins++;
            } elseif ($scored === $conceded) {
                $draws++;
            } else {
                $losses++;
            }
        }

        return new TeamStanding(
            id: $team->id,
            name: $team->name,
            shortName: $team->short_name,
            logoUrl: $team->logo_url,
            groupLetter: $team->pivot->group_letter,
            groupPosition: (int) $team->pivot->group_position,
            matchesPlayed: $matchesPlayed,
            wins: $wins,
            draws: $draws,
            losses: $losses,
            goalsFor: $goalsFor,
            goalsAgainst: $goalsAgainst,
            goalDifference: $goalsFor - $goalsAgainst,
            points: ($wins * 3) + $draws,
        );
    }
}
