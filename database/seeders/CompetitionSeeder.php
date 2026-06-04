<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Competition\Type;
use App\Models\Competition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

final class CompetitionSeeder extends Seeder
{
    public function run(): void
    {
        $contents = Storage::disk('local')->get('data.json');

        if ($contents === null) {
            return;
        }

        $contents = json_decode($contents, true);

        if ($contents === false) {
            return;
        }

        $data = $contents['competition'];

        Competition::query()->create([
            'name' => $data['name'],
            'code' => $data['code'],
            'type' => Type::from(mb_strtolower($data['type'])),
            'external_id' => (int) $data['id'],
        ]);
    }
}
