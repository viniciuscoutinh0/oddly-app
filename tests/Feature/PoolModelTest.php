<?php

declare(strict_types=1);

use App\Enums\Pool\Visibility;
use App\Models\Pool;
use App\Models\Season;
use App\Models\User;

it('creates a pool with defaults and relations', function (): void {
    $pool = Pool::factory()->create();

    expect($pool->visibility)->toBeInstanceOf(Visibility::class)
        ->and($pool->points_exact)->toBe(10)
        ->and($pool->points_result)->toBe(5)
        ->and($pool->points_champion)->toBe(25)
        ->and($pool->points_group_position)->toBe(3)
        ->and($pool->season)->toBeInstanceOf(Season::class)
        ->and($pool->owner)->toBeInstanceOf(User::class);
});

it('relates pools back to season and owner', function (): void {
    $pool = Pool::factory()->create();

    expect($pool->season->pools->contains($pool))->toBeTrue()
        ->and($pool->owner->ownedPools->contains($pool))->toBeTrue();
});
