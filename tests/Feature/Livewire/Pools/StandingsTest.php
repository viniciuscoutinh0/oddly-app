<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Enums\Fixture\Status;
use App\Livewire\Pools\Standings;
use App\Models\Bet;
use App\Models\Fixture;
use App\Models\Pool;
use App\Models\Season;
use App\Models\Stage;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function (): void {
    $pool = Pool::factory()->public()->create();
    get("/pools/{$pool->slug}/standings")->assertRedirect(route('login'));
});

it('forbids a non-participant', function (): void {
    $pool = Pool::factory()->public()->create();
    actingAs(User::factory()->create());
    Livewire::test(Standings::class, ['pool' => $pool])->assertForbidden();
});

it('shows ranked participants with points', function (): void {
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    $fixture = Fixture::factory()->for($stage)->create([
        'status' => Status::Finished, 'home_score' => 2, 'away_score' => 1,
    ]);
    $pool = Pool::factory()->public()->create(['season_id' => $season->id, 'points_exact' => 10]);

    $leader = User::factory()->create(['name' => 'Campeao']);
    actingAs($leader);
    app(JoinPoolAction::class)->handle($leader, $pool);
    Bet::factory()->for($fixture)->create([
        'user_id' => $leader->id, 'home_score' => 2, 'away_score' => 1,
        'is_exact' => true, 'is_correct_result' => true,
    ]);

    Livewire::test(Standings::class, ['pool' => $pool])
        ->assertOk()
        ->assertSee('Campeao')
        ->assertSee('10');
});
