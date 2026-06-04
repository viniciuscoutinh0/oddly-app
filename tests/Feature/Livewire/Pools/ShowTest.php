<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Livewire\Pools\Show;
use App\Models\Pool;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('lets the owner view a private pool and see the invite code', function (): void {
    $owner = User::factory()->create();
    actingAs($owner);
    $pool = Pool::factory()->create(['owner_id' => $owner->id, 'invite_code' => 'CODE1234']);
    $pool->participants()->attach($owner->id, ['joined_at' => now()]);

    Livewire::test(Show::class, ['pool' => $pool])
        ->assertOk()->assertSee($pool->name)->assertSee('CODE1234');
});

it('forbids a stranger from a private pool', function (): void {
    actingAs(User::factory()->create());
    $pool = Pool::factory()->create();

    Livewire::test(Show::class, ['pool' => $pool])->assertForbidden();
});

it('lets any authenticated user view a public pool but hides the invite code from non-members', function (): void {
    actingAs(User::factory()->create());
    $pool = Pool::factory()->public()->create(['invite_code' => 'PUBCODE1']);

    Livewire::test(Show::class, ['pool' => $pool])
        ->assertOk()
        ->assertSee($pool->name)
        ->assertDontSee('PUBCODE1');
});

it('blocks the owner from leaving via the action', function (): void {
    $owner = User::factory()->create();
    actingAs($owner);
    $pool = Pool::factory()->public()->create(['owner_id' => $owner->id]);
    $pool->participants()->attach($owner->id, ['joined_at' => now()]);

    Livewire::test(Show::class, ['pool' => $pool])
        ->call('leave')
        ->assertForbidden();
});

it('shows leave for a non-owner member and detaches on leave', function (): void {
    $member = User::factory()->create();
    actingAs($member);
    $pool = Pool::factory()->public()->create();
    app(JoinPoolAction::class)->handle($member, $pool);

    Livewire::test(Show::class, ['pool' => $pool])
        ->assertSee('Sair do bolão')
        ->call('leave')
        ->assertHasNoErrors();

    expect($pool->participants()->whereKey($member->id)->exists())->toBeFalse();
});

it('does not show leave to the owner', function (): void {
    $owner = User::factory()->create();
    actingAs($owner);
    $pool = Pool::factory()->public()->create(['owner_id' => $owner->id]);
    $pool->participants()->attach($owner->id, ['joined_at' => now()]);

    Livewire::test(Show::class, ['pool' => $pool])->assertDontSee('Sair do bolão');
});
