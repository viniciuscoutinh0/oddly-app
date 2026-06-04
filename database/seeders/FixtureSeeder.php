<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Fixture\Duration;
use App\Enums\Fixture\Status;
use App\Models\Fixture;
use App\Models\Stage;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

final class FixtureSeeder extends Seeder
{
    /**
     * Map football-data.org statuses onto the application status enum.
     *
     * @var array<string, Status>
     */
    private array $statusMap = [
        'SCHEDULED' => Status::Scheduled,
        'TIMED' => Status::Scheduled,
        'IN_PLAY' => Status::InProgress,
        'PAUSED' => Status::InProgress,
        'FINISHED' => Status::Finished,
        'AWARDED' => Status::Finished,
        'POSTPONED' => Status::Postponed,
        'SUSPENDED' => Status::Postponed,
        'CANCELLED' => Status::Cancelled,
    ];

    /**
     * Map football-data.org durations onto the application duration enum.
     *
     * @var array<string, Duration>
     */
    private array $durationMap = [
        'REGULAR' => Duration::Regular,
        'EXTRA_TIME' => Duration::ExtraTime,
        'PENALTY_SHOOTOUT' => Duration::Penalties,
    ];

    public function run(): void
    {
        $contents = Storage::disk('local')->get('data.json');

        if ($contents === null) {
            return;
        }

        $data = json_decode($contents, true);

        if ($data === false) {
            return;
        }

        $stages = Stage::query()->pluck('id', 'name');
        $teams = Team::query()->pluck('id', 'external_id');

        foreach ($data['matches'] as $match) {
            $score = $match['score'];

            Fixture::query()->create([
                'stage_id' => $stages[mb_strtolower($match['stage'])],
                'home_team_id' => $teams[$match['homeTeam']['id']] ?? null,
                'away_team_id' => $teams[$match['awayTeam']['id']] ?? null,
                'home_score' => $score['fullTime']['home'],
                'away_score' => $score['fullTime']['away'],
                'duration' => $this->durationMap[$score['duration']] ?? Duration::Regular,
                'group_letter' => $match['group'] ? mb_substr($match['group'], -1) : null,
                'match_day' => $match['matchday'],
                'match_date' => $match['utcDate'],
                'status' => $this->statusMap[$match['status']] ?? Status::Scheduled,
                'external_id' => $match['id'],
            ]);
        }
    }
}
