<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Models\Pool;
use App\Models\User;

it('joins a public pool without a code', function (): void {
    $pool = Pool::factory()->public()->create();
    $user = User::factory()->create();

    app(JoinPoolAction::class)->handle($user, $pool);

    expect($pool->participants()->whereKey($user->id)->exists())->toBeTrue();
});

it('joins a private pool with the correct code', function (): void {
    $pool = Pool::factory()->create(['invite_code' => 'SECRET12']);
    $user = User::factory()->create();

    app(JoinPoolAction::class)->handle($user, $pool, 'SECRET12');

    expect($pool->participants()->whereKey($user->id)->exists())->toBeTrue();
});

it('rejects a private pool with a wrong code', function (): void {
    $pool = Pool::factory()->create(['invite_code' => 'SECRET12']);
    $user = User::factory()->create();

    expect(fn () => app(JoinPoolAction::class)->handle($user, $pool, 'WRONG'))
        ->toThrow(InvalidArgumentException::class);
});

it('is idempotent and does not duplicate membership', function (): void {
    $pool = Pool::factory()->public()->create();
    $user = User::factory()->create();

    app(JoinPoolAction::class)->handle($user, $pool);
    app(JoinPoolAction::class)->handle($user, $pool);

    expect($pool->participants()->whereKey($user->id)->count())->toBe(1);
});

it('rejects a private pool when no code is given', function (): void {
    $pool = Pool::factory()->create(['invite_code' => 'SECRET12']);
    $user = User::factory()->create();

    expect(fn () => app(JoinPoolAction::class)->handle($user, $pool))
        ->toThrow(InvalidArgumentException::class);
});

it('exposes the inverse pools relation after joining', function (): void {
    $pool = Pool::factory()->public()->create();
    $user = User::factory()->create();

    app(JoinPoolAction::class)->handle($user, $pool);

    expect($user->pools()->whereKey($pool->id)->exists())->toBeTrue()
        ->and($user->pools->first()->getRelationValue('pivot')->joined_at)->not->toBeNull();
});
