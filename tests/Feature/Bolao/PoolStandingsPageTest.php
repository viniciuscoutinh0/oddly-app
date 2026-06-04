<?php

declare(strict_types=1);

use App\Enums\Fixture\Status;
use App\Filament\Clusters\Bolao\Resources\Pools\Pages\PoolStandingsPage;
use App\Models\Bet;
use App\Models\Fixture;
use App\Models\Pool;
use App\Models\Season;
use App\Models\Stage;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('shows ranked participants with points', function (): void {
    actingAs(User::factory()->admin()->create());

    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    $fixture = Fixture::factory()->for($stage)->create([
        'status' => Status::Finished, 'home_score' => 2, 'away_score' => 1,
    ]);
    $pool = Pool::factory()->public()->create(['season_id' => $season->id, 'points_exact' => 10]);

    $leader = User::factory()->create(['name' => 'Líder']);
    $pool->participants()->attach($leader->id, ['joined_at' => now()]);
    Bet::factory()->for($fixture)->create([
        'user_id' => $leader->id, 'home_score' => 2, 'away_score' => 1,
        'is_exact' => true, 'is_correct_result' => true,
    ]);

    livewire(PoolStandingsPage::class, ['record' => $pool->id])
        ->assertOk()
        ->assertSee('Líder')
        ->assertSee('10');
});

it('recalculates points via the header action', function (): void {
    actingAs(User::factory()->admin()->create());

    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    $fixture = Fixture::factory()->for($stage)->create([
        'status' => Status::Finished, 'home_score' => 1, 'away_score' => 0,
    ]);
    $pool = Pool::factory()->public()->create(['season_id' => $season->id, 'points_exact' => 10]);
    $user = User::factory()->create();
    $pool->participants()->attach($user->id, ['joined_at' => now()]);

    Bet::factory()->for($fixture)->create([
        'user_id' => $user->id, 'home_score' => 1, 'away_score' => 0,
        'is_exact' => null, 'is_correct_result' => null,
    ]);

    livewire(PoolStandingsPage::class, ['record' => $pool->id])
        ->callAction('recalculate')
        ->assertHasNoActionErrors();

    expect(Bet::first()->refresh()->is_exact)->toBeTrue();
});
