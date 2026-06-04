<?php

declare(strict_types=1);

use App\Actions\Pool\CreatePoolAction;
use App\Enums\Pool\Visibility;
use App\Models\Season;
use App\Models\User;

it('creates a public pool with a unique slug, no invite code, owner joined', function (): void {
    $owner = User::factory()->create();
    $season = Season::factory()->create();

    $pool = app(CreatePoolAction::class)->handle($owner, [
        'name' => 'Bolão da Firma', 'description' => null, 'season_id' => $season->id,
        'visibility' => Visibility::Public, 'points_exact' => 10, 'points_result' => 5,
        'points_champion' => 25, 'points_group_position' => 3,
    ]);

    expect($pool->slug)->toStartWith('bolao-da-firma')
        ->and($pool->invite_code)->toBeNull()
        ->and($pool->owner_id)->toBe($owner->id)
        ->and($pool->participants()->whereKey($owner->id)->exists())->toBeTrue();
});

it('generates an invite code for a private pool', function (): void {
    $owner = User::factory()->create();
    $season = Season::factory()->create();

    $pool = app(CreatePoolAction::class)->handle($owner, [
        'name' => 'Bolão Secreto', 'description' => null, 'season_id' => $season->id,
        'visibility' => Visibility::Private, 'points_exact' => 10, 'points_result' => 5,
        'points_champion' => 25, 'points_group_position' => 3,
    ]);

    expect($pool->invite_code)->not->toBeNull()->and(mb_strlen($pool->invite_code))->toBe(8);
});

it('makes unique slugs for duplicate names', function (): void {
    $owner = User::factory()->create();
    $season = Season::factory()->create();
    $data = [
        'name' => 'Mesmo Nome', 'description' => null, 'season_id' => $season->id,
        'visibility' => Visibility::Public, 'points_exact' => 10, 'points_result' => 5,
        'points_champion' => 25, 'points_group_position' => 3,
    ];

    $a = app(CreatePoolAction::class)->handle($owner, $data);
    $b = app(CreatePoolAction::class)->handle($owner, $data);

    expect($a->slug)->not->toBe($b->slug);
});
