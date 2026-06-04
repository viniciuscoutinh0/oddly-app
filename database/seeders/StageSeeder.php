<?php

declare(strict_types=1);

namespace Database\Seeders;

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

        $stages = collect($data['matches'])->groupBy('stage')->keys();

        if ($stages->isEmpty()) {
            return;
        }

        $order = 1;

        foreach ($stages as $stage) {
            Stage::query()->create([
                'season_id' => 1,
                'name' => $stage,
                'order' => $order++,
                'is_knockout' => false,
            ]);
        }
    }
}
