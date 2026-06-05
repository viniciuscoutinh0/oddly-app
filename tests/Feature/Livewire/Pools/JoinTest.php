<?php

declare(strict_types=1);

use App\Livewire\Pools\Join;
use App\Models\Pool;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(fn () => actingAs(User::factory()->create()));

it('renders successfully', function (): void {
    Livewire::test('pools.join')->assertStatus(200);
});

it('joins a private pool with the correct invite code', function (): void {
    $pool = Pool::factory()->create(['invite_code' => 'SECRET12']);

    Livewire::test(Join::class)
        ->set('code', 'SECRET12')
        ->call('join')
        ->assertHasNoErrors()
        ->assertSet('code', null);

    expect($pool->participants()->whereKey(Auth::id())->exists())->toBeTrue();
});

it('shows an error for a wrong invite code', function (): void {
    Pool::factory()->create(['invite_code' => 'SECRET12']);

    Livewire::test(Join::class)
        ->set('code', 'WRONG')
        ->call('join')
        ->assertHasErrors('code');
});
