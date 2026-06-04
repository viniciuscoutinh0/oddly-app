<?php

declare(strict_types=1);

use App\Models\Competition;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Stage;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());
});

it('renders every resource index page', function (string $resource): void {
    get("/admin/tournament/{$resource}")->assertSuccessful();
})->with([
    'competitions',
    'seasons',
    'stages',
    'fixtures',
    'teams',
]);

it('renders create pages', function (string $resource): void {
    get("/admin/tournament/{$resource}/create")->assertSuccessful();
})->with([
    'competitions',
    'seasons',
    'stages',
    'fixtures',
]);

it('renders edit pages', function (): void {
    $competition = Competition::factory()->create();
    $season = Season::factory()->for($competition)->create();
    $stage = Stage::factory()->for($season)->create();
    $fixture = Fixture::factory()->for($stage)->create();

    get("/admin/tournament/competitions/{$competition->id}/edit")->assertSuccessful();
    get("/admin/tournament/seasons/{$season->id}/edit")->assertSuccessful();
    get("/admin/tournament/stages/{$stage->id}/edit")->assertSuccessful();
    get("/admin/tournament/fixtures/{$fixture->id}/edit")->assertSuccessful();
});
