<?php

declare(strict_types=1);

use App\Livewire\Pools\Browse;
use App\Models\Pool;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(fn () => actingAs(User::factory()->create()));

it('lists public pools and not private ones', function (): void {
    Pool::factory()->public()->create(['name' => 'Aberto']);
    Pool::factory()->create(['name' => 'Fechado']);

    Livewire::test(Browse::class)
        ->assertSee('Aberto')
        ->assertDontSee('Fechado');
});

it('joins a public pool and adds the user', function (): void {
    $pool = Pool::factory()->public()->create();

    Livewire::test(Browse::class)
        ->call('join', $pool->id)
        ->assertHasNoErrors();

    expect($pool->participants()->whereKey(auth()->id())->exists())->toBeTrue();
});

it('joins a private pool with the correct invite code', function (): void {
    $pool = Pool::factory()->create(['invite_code' => 'SECRET12']);

    Livewire::test(Browse::class)
        ->set('inviteCode', 'SECRET12')
        ->call('joinByCode')
        ->assertHasNoErrors();

    expect($pool->participants()->whereKey(auth()->id())->exists())->toBeTrue();
});

it('shows an error for a wrong invite code', function (): void {
    Pool::factory()->create(['invite_code' => 'SECRET12']);

    Livewire::test(Browse::class)
        ->set('inviteCode', 'WRONG')
        ->call('joinByCode')
        ->assertHasErrors('inviteCode');
});
