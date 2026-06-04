<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Models\Pool;
use App\Models\User;
use App\Policies\PoolPolicy;

it('lets anyone view a public pool', function (): void {
    $pool = Pool::factory()->public()->create();
    $user = User::factory()->create();

    expect((new PoolPolicy)->view($user, $pool))->toBeTrue();
});

it('hides a private pool from non-members', function (): void {
    $pool = Pool::factory()->create();
    $stranger = User::factory()->create();

    expect((new PoolPolicy)->view($stranger, $pool))->toBeFalse();
});

it('lets members and the owner view a private pool', function (): void {
    $owner = User::factory()->create();
    $pool = Pool::factory()->create(['owner_id' => $owner->id]);
    $member = User::factory()->create();
    app(JoinPoolAction::class)->handle($member, $pool, $pool->invite_code);

    expect((new PoolPolicy)->view($owner, $pool))->toBeTrue()
        ->and((new PoolPolicy)->view($member->fresh(), $pool))->toBeTrue();
});

it('only lets the owner update a pool', function (): void {
    $owner = User::factory()->create();
    $pool = Pool::factory()->create(['owner_id' => $owner->id]);
    $other = User::factory()->create();

    expect((new PoolPolicy)->update($owner, $pool))->toBeTrue()
        ->and((new PoolPolicy)->update($other, $pool))->toBeFalse();
});
