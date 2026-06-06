<?php

declare(strict_types=1);

use App\Enums\Pool\Visibility;
use App\Livewire\Pools\Create;
use App\Models\Competition;
use App\Models\Pool;
use App\Models\Season;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(fn () => actingAs(User::factory()->create()));

it('requires a name, competition and season', function (): void {
    Livewire::test(Create::class)
        ->set('form.name', '')
        ->set('form.competition_id', null)
        ->set('form.season_id', null)
        ->call('create')
        ->assertHasErrors([
            'form.name' => 'required',
            'form.visibility' => 'required',
            'form.competition_id' => 'required',
            'form.season_id' => 'required',
        ]);
});

it('creates a pool and joins the owner', function (): void {
    $competition = Competition::factory()->create();
    $season = Season::factory()->for($competition, 'competition')->create();

    Livewire::test(Create::class)
        ->set('form.name', 'Bolão Top')
        ->set('form.competition_id', $competition->id)
        ->set('form.season_id', $season->id)
        ->set('form.visibility', Visibility::Public)
        ->call('create')
        ->assertHasNoErrors();

    $pool = Pool::where('name', 'Bolão Top')->first();

    expect($pool)
        ->not
        ->toBeNull()
        ->and($pool->participants()->whereKey(Auth::id())->exists())
        ->toBeTrue();
});

it('persists custom point values', function (): void {
    $competition = Competition::factory()->create();
    $season = Season::factory()->for($competition, 'competition')->create();

    Livewire::test(Create::class)
        ->set('form.name', 'Bolão Pontos')
        ->set('form.competition_id', $competition->id)
        ->set('form.season_id', $season->id)
        ->set('form.visibility', Visibility::Public)
        ->set('form.points_exact', 20)
        ->call('create')
        ->assertHasNoErrors();

    expect(Pool::where('name', 'Bolão Pontos')->first()->points_exact)->toBe(20);
});
