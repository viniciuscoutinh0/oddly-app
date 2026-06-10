<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Enums\Fixture\Status;
use App\Livewire\Pools\Bets;
use App\Models\Bet;
use App\Models\Fixture;
use App\Models\Pool;
use App\Models\Stage;
use App\Models\Team;
use App\Models\User;

use function Pest\Laravel\actingAs;

function poolWithFixture(?callable $fixtureState = null): array
{
    $pool = Pool::factory()->public()->create();
    $stage = Stage::factory()->for($pool->season)->create();
    $home = Team::factory()->create(['name' => 'Brasil']);
    $away = Team::factory()->create(['name' => 'Argentina']);
    $fixture = Fixture::factory()->for($stage)->create(array_merge([
        'home_team_id' => $home->id,
        'away_team_id' => $away->id,
        'match_date' => now()->addDay(),
        'locked_at' => null,
        'status' => Status::Scheduled,
    ], $fixtureState ? $fixtureState() : []));

    return [$pool, $fixture];
}

it('forbids a non-participant', function (): void {
    [$pool] = poolWithFixture();
    actingAs(User::factory()->create());
    Livewire::test(Bets::class, ['pool' => $pool])->assertForbidden();
});

it('lets a participant open it and lists the fixtures', function (): void {
    [$pool] = poolWithFixture();
    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bets::class, ['pool' => $pool])
        ->assertOk()->assertSee('Brasil')->assertSee('Argentina');
});

it('prefills existing bet values', function (): void {
    [$pool, $fixture] = poolWithFixture();
    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);
    Bet::factory()->for($fixture)->create(['user_id' => $user->id, 'home_score' => 3, 'away_score' => 1]);

    Livewire::test(Bets::class, ['pool' => $pool])
        ->assertSet("scores.{$fixture->id}.home", 3)
        ->assertSet("scores.{$fixture->id}.away", 1);
});

it('saves bets for editable fixtures', function (): void {
    [$pool, $fixture] = poolWithFixture();
    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bets::class, ['pool' => $pool])
        ->call('save', $fixture->id, 2, 0)
        ->assertHasNoErrors();

    expect(Bet::where('user_id', $user->id)->where('fixture_id', $fixture->id)->first())
        ->not->toBeNull()->home_score->toBe(2)->away_score->toBe(0);
});

it('does not save a locked fixture', function (): void {
    [$pool, $fixture] = poolWithFixture(fn () => ['match_date' => now()->subHour()]);
    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bets::class, ['pool' => $pool])
        ->call('save', $fixture->id, 2, 0)
        ->assertHasNoErrors();

    expect(Bet::where('fixture_id', $fixture->id)->exists())->toBeFalse();
});

it('shows the real result on a finished fixture', function (): void {
    [$pool, $fixture] = poolWithFixture(fn () => [
        'match_date' => now()->subHour(),
        'status' => Status::Finished,
        'home_score' => 2,
        'away_score' => 1,
    ]);
    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bets::class, ['pool' => $pool])
        ->assertSee('Resultado: 2 x 1');
});
