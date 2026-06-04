<?php

declare(strict_types=1);

use App\Models\Bet;
use App\Models\Fixture;
use App\Models\User;
use Illuminate\Database\QueryException;

it('creates a bet linked to a user and fixture', function (): void {
    $bet = Bet::factory()->create();

    expect($bet->user)->toBeInstanceOf(User::class)
        ->and($bet->fixture)->toBeInstanceOf(Fixture::class)
        ->and($bet->is_exact)->toBeNull()
        ->and($bet->is_correct_result)->toBeNull();
});

it('enforces one bet per user per fixture', function (): void {
    $bet = Bet::factory()->create();

    expect(fn () => Bet::factory()->create([
        'user_id' => $bet->user_id,
        'fixture_id' => $bet->fixture_id,
    ]))->toThrow(QueryException::class);
});
