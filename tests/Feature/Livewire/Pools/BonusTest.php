<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Livewire\Pools\Bonus;
use App\Models\ChampionBet;
use App\Models\GroupBet;
use App\Models\Pool;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function bonusPool(): array
{
    $season = Season::factory()->create();
    $pool = Pool::factory()->public()->create(['season_id' => $season->id]);
    $a = Team::factory()->create(['name' => 'Time A1']);
    $b = Team::factory()->create(['name' => 'Time A2']);
    $season->teams()->attach($a->id, ['group_letter' => 'A', 'group_position' => null]);
    $season->teams()->attach($b->id, ['group_letter' => 'A', 'group_position' => null]);

    return [$pool, $season, $a, $b];
}

it('redirects guests to login', function (): void {
    [$pool] = bonusPool();
    get("/pools/{$pool->slug}/bonus")->assertRedirect(route('login'));
});

it('forbids a non-participant', function (): void {
    [$pool] = bonusPool();
    actingAs(User::factory()->create());
    Livewire::test(Bonus::class, ['pool' => $pool])->assertForbidden();
});

it('saves champion and group bonus predictions', function (): void {
    [$pool, $season, $a, $b] = bonusPool();
    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bonus::class, ['pool' => $pool])
        ->set('championTeamId', $a->id)
        ->set('groups.A.first', $a->id)
        ->set('groups.A.second', $b->id)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('saved', true);

    expect(ChampionBet::where('user_id', $user->id)->where('season_id', $season->id)->first()->team_id)->toBe($a->id)
        ->and(GroupBet::where('user_id', $user->id)->where('group_letter', 'A')->count())->toBe(2);
});

it('prefills existing bonus predictions', function (): void {
    [$pool, $season, $a, $b] = bonusPool();
    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);
    ChampionBet::factory()->for($season)->create(['user_id' => $user->id, 'team_id' => $a->id]);
    GroupBet::factory()->for($season)->create([
        'user_id' => $user->id, 'group_letter' => 'A', 'predicted_position' => 1, 'team_id' => $a->id,
    ]);

    Livewire::test(Bonus::class, ['pool' => $pool])
        ->assertSet('championTeamId', $a->id)
        ->assertSet('groups.A.first', $a->id);
});
