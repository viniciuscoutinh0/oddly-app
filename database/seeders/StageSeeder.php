<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Stage\Name;
use App\Models\Season;
use App\Models\Stage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

final class StageSeeder extends Seeder
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

        $stages = collect($data['matches'])
            ->pluck('stage')
            ->unique()
            ->map(fn (string $stage): Name => Name::from(mb_strtolower($stage)));

        $sortOrder = 1;

        foreach ($stages as $stage) {
            Stage::query()->create([
                'season_id' => $season->id,
                'name' => $stage,
                'sort_order' => $sortOrder++,
                'is_knockout' => $stage->isKnockout(),
            ]);
        }
    }
}
