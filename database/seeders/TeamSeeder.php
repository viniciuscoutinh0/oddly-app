<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

final class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $contents = Storage::disk('local')->get('teams.json');

        if ($contents === null) {
            return;
        }

        $contents = json_decode($contents, true);

        foreach ($contents['teams'] ?? [] as $team) {
            Team::query()->create([
                'name' => $team['name'],
                'short_name' => $team['shortName'],
                'tla' => $team['tla'],
                'logo_url' => $team['crest'],
                'external_id' => (int) $team['id'],
            ]);
        }
    }
}
