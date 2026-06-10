<?php

declare(strict_types=1);

use App\Livewire\Pools\Browse;
use App\Models\Pool;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

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

    expect($pool->participants()->whereKey(Auth::id())->exists())->toBeTrue();
});
