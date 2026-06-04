<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Actions\Pool\LeavePoolAction;
use App\Models\Pool;
use App\Models\User;

it('detaches a member from the pool', function (): void {
    $pool = Pool::factory()->public()->create();
    $member = User::factory()->create();
    app(JoinPoolAction::class)->handle($member, $pool);

    app(LeavePoolAction::class)->handle($member, $pool);

    expect($pool->participants()->whereKey($member->id)->exists())->toBeFalse();
});

it('does not let the owner leave', function (): void {
    $owner = User::factory()->create();
    $pool = Pool::factory()->public()->create(['owner_id' => $owner->id]);

    expect(fn () => app(LeavePoolAction::class)->handle($owner, $pool))
        ->toThrow(RuntimeException::class);
});
