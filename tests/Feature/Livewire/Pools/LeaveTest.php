<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Livewire\Pools\Leave;
use App\Models\Pool;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('renders successfully', function (): void {
    Livewire::test('pools.leave')
        ->assertStatus(200);
});

it('shows leave for a non-owner member and detaches on leave', function (): void {
    $member = User::factory()->create();

    actingAs($member);

    $pool = Pool::factory()->public()->create();

    app(JoinPoolAction::class)->handle($member, $pool);

    Livewire::test(Leave::class, ['pool' => $pool])
        ->assertSee('Sair')
        ->call('leave')
        ->assertHasNoErrors();

    expect($pool->participants()->whereKey($member->id)->exists())->toBeFalse();
});

it('does not show leave to the owner', function (): void {
    $owner = User::factory()->create();

    actingAs($owner);

    $pool = Pool::factory()->public()->create(['owner_id' => $owner->id]);

    $pool->participants()->attach($owner->id, ['joined_at' => now()]);

    Livewire::test(Leave::class, ['pool' => $pool])
        ->assertDontSee('Sair do bolão');
});

it('blocks the owner from leaving via the action', function (): void {
    $owner = User::factory()->create();

    actingAs($owner);

    $pool = Pool::factory()->public()->create(['owner_id' => $owner->id]);

    $pool->participants()->attach($owner->id, ['joined_at' => now()]);

    Livewire::test(Leave::class, ['pool' => $pool])
        ->call('leave')
        ->assertForbidden();
});
