<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Fixture;
use App\Models\Team;
use App\Services\FootballData\CompetitionMatch;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

final class FetchMatchDataCommand extends Command
{
    protected $signature = 'app:fetch-matches {competition} {season} {stage}';

    protected $description = 'Fetch competition matches by Competition Id, Season Year and Stage.';

    public function handle(CompetitionMatch $service): int
    {
        try {
            $data = $service->matchesByCompetitionId((int) $this->argument('competition'), [
                'season' => (int) $this->argument('season'),
                'stage' => $this->normalizeStage($this->argument('stage')),
            ]);

            $matches = $data['matches'] ?? [];

            foreach($matches as $match) {
                $matchDate = CarbonImmutable::parse($match['utcDate'])->subHours(3);

                Fixture::query()
                    ->where('external_id', $match['id'])
                    ->update([
                        'home_team_id' => $this->resolveTeam($match['homeTeam']['id'])?->id,
                        'away_team_id' => $this->resolveTeam($match['awayTeam']['id'])?->id,
                        'match_date'   => $matchDate,
                        'locked_at'    => $matchDate->subMinutes(30),
                    ]);

                $this->info('Fixture ['.$match['id'].'] updated successfull');
            }
        } catch (Throwable $e) {
            $this->info('Failed fetch to API');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function normalizeStage(string $value): string
    {
        return (string) Str::of($value)->snake()->upper()->trim();
    }

    private function resolveTeam(int $teamId): Team
    {
        return Team::query()->where('external_id', $teamId)->first('id');
    }
}
