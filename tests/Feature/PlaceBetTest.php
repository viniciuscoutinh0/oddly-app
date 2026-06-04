<?php

declare(strict_types=1);

use App\Actions\Bet\PlaceBetAction;
use App\Models\Fixture;
use App\Models\User;

it('creates a bet before the fixture locks', function (): void {
    $user = User::factory()->create();
    $fixture = Fixture::factory()->create([
        'match_date' => now()->addDay(),
        'locked_at' => null,
    ]);

    $bet = app(PlaceBetAction::class)->handle($user, $fixture, 2, 1);

    expect($bet->home_score)->toBe(2)->and($bet->away_score)->toBe(1);
});

it('updates an existing bet instead of duplicating', function (): void {
    $user = User::factory()->create();
    $fixture = Fixture::factory()->create([
        'match_date' => now()->addDay(),
        'locked_at' => null,
    ]);

    app(PlaceBetAction::class)->handle($user, $fixture, 2, 1);
    app(PlaceBetAction::class)->handle($user, $fixture, 0, 0);

    expect($user->bets()->count())->toBe(1)
        ->and($user->bets()->first()->home_score)->toBe(0);
});

it('rejects a bet after the fixture is locked', function (): void {
    $user = User::factory()->create();
    $fixture = Fixture::factory()->create([
        'match_date' => now()->subHour(),
        'locked_at' => null,
    ]);

    expect(fn () => app(PlaceBetAction::class)->handle($user, $fixture, 1, 0))
        ->toThrow(RuntimeException::class);
});

it('respects an explicit locked_at', function (): void {
    $user = User::factory()->create();
    $fixture = Fixture::factory()->create([
        'match_date' => now()->addDay(),
        'locked_at' => now()->subMinute(),
    ]);

    expect(fn () => app(PlaceBetAction::class)->handle($user, $fixture, 1, 0))
        ->toThrow(RuntimeException::class);
});
