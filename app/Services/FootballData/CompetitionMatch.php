<?php

declare(strict_types=1);

namespace App\Services\FootballData;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

final class CompetitionMatch
{
    public function matchesByCompetitionId(int $id, array $query = []): array
    {
        $response = Http::baseUrl(config('services.football.url'))
            ->withHeader('X-Auth-Token', config('services.football.token'))
            ->get("/v4/competitions/{$id}/matches", $query);

        if ($response->failed()) {
            throw new RequestException($response);
        }

        return $response->json();
    }
}
