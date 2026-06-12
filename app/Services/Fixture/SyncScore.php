<?php

declare(strict_types=1);

namespace App\Services\Fixture;

use App\Enums\Fixture\Duration;
use App\Enums\Fixture\Status;
use App\Models\Fixture;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

final class SyncScore
{
    public function sync(): void
    {
        $date = today()->format('Y-m-d');

        $file = Storage::disk('local')->get('data-'.$date.'.json');

        if ($file === null) {
            return;
        }

        $matches = collect(json_decode($file, true));

        if ($matches === []) {
            return;
        }

        /** @var Collection<Fixture> */
        $fixtures = Fixture::query()
            ->whereIn('external_id', $matches->where('status', 'FINISHED')->pluck('id'))
            ->whereNot('status', Status::Finished)
            ->whereNotNull('external_id')
            ->get();

        foreach ($fixtures as $fixture) {
            $match = $matches->firstWhere('id', $fixture->external_id);

            if ($match === null) {
                continue;
            }

            if (! isset($match['score'])) {
                continue;
            }

            $score = $match['score'];

            $fixture->update([
                'status' => Status::Finished,
                'duration' => Duration::from(strtolower($score['duration'])),
                'home_score' => $score['fullTime']['home'],
                'away_score' => $score['fullTime']['away'],
                'home_score_et' => $score['halfTime']['home'],
                'away_score_et' => $score['halfTime']['away'],
            ]);
        }
    }
}
