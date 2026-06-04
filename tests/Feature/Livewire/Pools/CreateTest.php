<?php

declare(strict_types=1);

use App\Enums\Pool\Visibility;
use App\Livewire\Pools\Create;
use App\Models\Pool;
use App\Models\Season;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(fn () => actingAs(User::factory()->create()));

it('requires a name and season', function (): void {
    Livewire::test(Create::class)
        ->set('name', '')
        ->set('season_id', null)
        ->call('create')
        ->assertHasErrors(['name' => 'required', 'season_id' => 'required']);
});

it('creates a pool and joins the owner', function (): void {
    $season = Season::factory()->create();

    Livewire::test(Create::class)
        ->set('name', 'Bolão Top')
        ->set('season_id', $season->id)
        ->set('visibility', Visibility::Public->value)
        ->call('create')
        ->assertHasNoErrors();

    $pool = Pool::where('name', 'Bolão Top')->first();
    expect($pool)->not->toBeNull()
        ->and($pool->participants()->whereKey(auth()->id())->exists())->toBeTrue();
});

it('persists custom point values', function (): void {
    $season = Season::factory()->create();

    Livewire::test(Create::class)
        ->set('name', 'Bolão Pontos')
        ->set('season_id', $season->id)
        ->set('visibility', Visibility::Private->value)
        ->set('points_exact', 20)
        ->call('create')
        ->assertHasNoErrors();

    expect(Pool::where('name', 'Bolão Pontos')->first()->points_exact)->toBe(20);
});
