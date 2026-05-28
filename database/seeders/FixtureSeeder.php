<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Fixture;
use App\Models\Stage;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

final class FixtureSeeder extends Seeder
{
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

        $fixtures = $data['matches'];

        foreach ($fixtures as $fixture) {
            $stage = Stage::query()->where('name', $fixture['stage'])->first(['id']);

            $homeTeam = Team::query()
                ->where('external_id', (int) $fixture['homeTeam']['id'])
                ->first(['id']);

            $awayTeam = Team::query()
                ->where('external_id', (int) $fixture['awayTeam']['id'])
                ->first(['id']);

            Fixture::query()->create([
                'stage_id' => $stage->id,
                'home_team_id' => $homeTeam?->id,
                'away_team_id' => $awayTeam?->id,
                'group_letter' => $fixture['group'] ? substr($fixture['group'], -1) : null,
                'match_day' => $fixture['matchday'],
                'match_date' => $fixture['utcDate'],
                'external_id' => $fixture['id'],
            ]);
        }
    }
}
