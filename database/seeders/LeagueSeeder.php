<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\League\Type;
use App\Models\Fixture;
use App\Models\League;
use App\Models\Stage;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class LeagueSeeder extends Seeder
{
    public function run(): void
    {
        $cup = League::query()->create([
            'name' => 'FIFA World Cup',
            'slug' => Str::slug('fifa world cup'),
            'type' => Type::Tournament,
            'logo' => 'https://crests.football-data.org/wm26.png',
        ]);

        $cup->sessions()->create([
            'start_date' => '2026-06-11',
            'end_date' => '2026-07-19',
        ]);

        /** @var \App\Models\Session */
        $session = $cup->sessions->first();

        $contents = Storage::get('matches.json');

        if (! $contents) {
            return;
        }

        $contents = json_decode($contents, true) ?? [];

        if ($contents === []) {
            return;
        }

        $contents = collect($contents['matches']);

        $stages = $contents
            ->groupBy('stage')
            ->keys()
            ->map(fn (string $key): array => [
                'name' => $key,
                'external_id' => $key,
            ]);

        $session->stages()->createMany($stages);

        $contents
            ->map(function (array $fixture) {
                $stage = Stage::query()->where('external_id', $fixture['stage'])->first();

                $home = Team::query()->where('external_id', $fixture['homeTeam']['id'])->first();
                $away = Team::query()->where('external_id', $fixture['awayTeam']['id'])->first();

                return [
                    'stage_id' => $stage->id,
                    'home_team_id' => $home?->id,
                    'away_team_id' => $away?->id,
                    'match_date' => $fixture['utcDate'],
                    'external_id' => $fixture['id'],
                ];
            })
            ->each(function (array $fixture): void {
                Fixture::query()->create($fixture);
            });
    }
}
