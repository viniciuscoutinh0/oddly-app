<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Competition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

final class SeasonSeeder extends Seeder
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

        $competition = Competition::query()->where('external_id', 2000)->first();

        if ($competition === null) {
            return;
        }

        $season = $data['matches'][0]['season'];

        $competition
            ->seasons()
            ->create([
                'start_date' => $season['startDate'],
                'end_date' => $season['endDate'],
                'external_id' => (int) $season['id'],
            ]);
    }
}
