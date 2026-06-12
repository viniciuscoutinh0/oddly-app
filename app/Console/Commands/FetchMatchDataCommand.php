<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Fixture\Status;
use App\Models\Competition;
use App\Services\Fixture\SyncScore;
use App\Services\FootballData\CompetitionMatch;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

final class FetchMatchDataCommand extends Command
{
    protected $signature = 'app:fetch-matches {year?}';

    protected $description = 'Fetch competition matches';

    public function handle(CompetitionMatch $service)
    {
        $competitions = Competition::query()
            ->whereHas('seasons', function (Builder $query): void {
                $query->whereHas('fixtures', function (Builder $query) {
                    $query->whereNot('status', Status::Finished);
                });
            })
            ->whereNotNull('external_id')
            ->get([
                'id',
                'external_id',
            ]);

        foreach ($competitions as $competition) {
            $response = $service->matchesByCompetitionId(
                id: $competition->external_id,
                query: [
                    'season' => $this->argument('year') ?? today()->year,
                ],
            );

            if (! is_array($response)) {
                continue;
            }

            Storage::disk('local')->put(
                path: 'data-'.today()->format('Y-m-d').'.json',
                contents: json_encode($response['matches'] ?? []),
            );
        }
    }
}
