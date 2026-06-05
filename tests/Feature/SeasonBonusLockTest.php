<?php

declare(strict_types=1);

use App\Models\Fixture;
use App\Models\Season;
use App\Models\Stage;

it('has no bonus lock when the season has no fixtures', function (): void {
    $season = Season::factory()->create();

    expect($season->bonusLocksAt())->toBeNull()
        ->and($season->bonusLocked())->toBeFalse();
});

it('locks bonus at the earliest fixture kickoff', function (): void {
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    Fixture::factory()->for($stage)->create(['match_date' => now()->addDays(3)]);
    Fixture::factory()->for($stage)->create(['match_date' => now()->subHour()]);

    expect($season->bonusLocked())->toBeTrue();
});

it('is unlocked when the earliest kickoff is in the future', function (): void {
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    Fixture::factory()->for($stage)->create(['match_date' => now()->addDay()]);

    expect($season->bonusLocked())->toBeFalse();
});
