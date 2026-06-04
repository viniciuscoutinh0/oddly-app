<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Stage\Name;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

final class SeasonTeamSeeder extends Seeder
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

        $season = Season::query()->first(['id']);

        if ($season === null) {
            return;
        }

        $teams = Team::query()->pluck('id', 'external_id');

        $pivot = [];

        foreach ($data['matches'] as $match) {
            if (mb_strtolower($match['stage']) !== Name::GroupStage->value || $match['group'] === null) {
                continue;
            }

            $group = mb_substr($match['group'], -1);

            foreach ([$match['homeTeam']['id'], $match['awayTeam']['id']] as $externalId) {
                if ($externalId !== null && isset($teams[$externalId])) {
                    $pivot[$teams[$externalId]] = ['group_letter' => $group];
                }
            }
        }

        $season->teams()->sync($pivot);
    }
}
