<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Livewire\Pools\Bonus;
use App\Models\ChampionBet;
use App\Models\Fixture;
use App\Models\GroupBet;
use App\Models\Pool;
use App\Models\Season;
use App\Models\Stage;
use App\Models\Team;
use App\Models\User;

use function Pest\Laravel\actingAs;

function bonusPool(): array
{
    $season = Season::factory()->create();
    $pool = Pool::factory()->public()->create(['season_id' => $season->id]);
    $a = Team::factory()->create(['name' => 'Time A1']);
    $b = Team::factory()->create(['name' => 'Time A2']);
    $c = Team::factory()->create(['name' => 'Time A3']);
    $season->teams()->attach($a->id, ['group_letter' => 'A', 'group_position' => null]);
    $season->teams()->attach($b->id, ['group_letter' => 'A', 'group_position' => null]);
    $season->teams()->attach($c->id, ['group_letter' => 'A', 'group_position' => null]);

    return [$pool, $season, $a, $b, $c];
}

it('forbids a non-participant', function (): void {
    [$pool] = bonusPool();
    actingAs(User::factory()->create());
    Livewire::test(Bonus::class, ['pool' => $pool])->assertForbidden();
});

it('auto-saves the champion and the classified teams of a group', function (): void {
    [$pool, $season, $a, $b] = bonusPool();
    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bonus::class, ['pool' => $pool])
        ->set('championTeamId', $a->id)
        ->set('groups.A', [$a->id, $b->id])
        ->assertHasNoErrors();

    $groupBets = GroupBet::where('user_id', $user->id)->where('group_letter', 'A')
        ->orderBy('predicted_position')->get();

    expect(ChampionBet::where('user_id', $user->id)->where('season_id', $season->id)->first()->team_id)->toBe($a->id)
        ->and($groupBets)->toHaveCount(2)
        ->and($groupBets[0]->predicted_position)->toBe(1)
        ->and($groupBets[0]->team_id)->toBe($a->id)
        ->and($groupBets[1]->predicted_position)->toBe(2)
        ->and($groupBets[1]->team_id)->toBe($b->id);
});

it('toggles a team in and out of a group selection', function (): void {
    [$pool, $season, $a, $b] = bonusPool();
    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bonus::class, ['pool' => $pool])
        ->call('toggleGroup', 'A', $a->id)
        ->assertSet('groups.A', [$a->id])
        ->call('toggleGroup', 'A', $b->id)
        ->assertSet('groups.A', [$a->id, $b->id])
        ->call('toggleGroup', 'A', $a->id)
        ->assertSet('groups.A', [$b->id]);

    expect(GroupBet::where('user_id', $user->id)->where('group_letter', 'A')->pluck('team_id')->all())
        ->toBe([$b->id]);
});

it('does not toggle a fourth team into a group', function (): void {
    [$pool, $season, $a, $b, $c] = bonusPool();
    $d = Team::factory()->create(['name' => 'Time A4']);
    $season->teams()->attach($d->id, ['group_letter' => 'A', 'group_position' => null]);

    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bonus::class, ['pool' => $pool])
        ->call('toggleGroup', 'A', $a->id)
        ->call('toggleGroup', 'A', $b->id)
        ->call('toggleGroup', 'A', $c->id)
        ->call('toggleGroup', 'A', $d->id)
        ->assertSet('groups.A', [$a->id, $b->id, $c->id]);
});

it('keeps at most three teams per group', function (): void {
    [$pool, $season, $a, $b, $c] = bonusPool();
    $d = Team::factory()->create(['name' => 'Time A4']);
    $season->teams()->attach($d->id, ['group_letter' => 'A', 'group_position' => null]);

    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bonus::class, ['pool' => $pool])
        ->set('groups.A', [$a->id, $b->id, $c->id, $d->id])
        ->assertSet('groups.A', [$a->id, $b->id, $c->id]);

    expect(GroupBet::where('user_id', $user->id)->where('group_letter', 'A')->count())->toBe(3);
});

it('removes deselected teams when the selection shrinks', function (): void {
    [$pool, $season, $a, $b] = bonusPool();
    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bonus::class, ['pool' => $pool])
        ->set('groups.A', [$a->id, $b->id])
        ->set('groups.A', [$a->id]);

    $groupBets = GroupBet::where('user_id', $user->id)->where('group_letter', 'A')->get();

    expect($groupBets)->toHaveCount(1)
        ->and($groupBets->first()->team_id)->toBe($a->id);
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
    GroupBet::factory()->for($season)->create([
        'user_id' => $user->id, 'group_letter' => 'A', 'predicted_position' => 2, 'team_id' => $b->id,
    ]);

    Livewire::test(Bonus::class, ['pool' => $pool])
        ->assertSet('championTeamId', $a->id)
        ->assertSet('groups.A', [$a->id, $b->id]);
});

it('ignores a team that does not belong to the group', function (): void {
    [$pool, $season] = bonusPool();
    $outsider = Team::factory()->create(['name' => 'Time B1']);
    $season->teams()->attach($outsider->id, ['group_letter' => 'B', 'group_position' => null]);

    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bonus::class, ['pool' => $pool])
        ->set('groups.A', [$outsider->id])
        ->assertSet('groups.A', []);

    expect(GroupBet::where('season_id', $season->id)->exists())->toBeFalse();
});

it('does not save when the bonus is locked', function (): void {
    [$pool, $season, $a, $b] = bonusPool();
    $stage = Stage::factory()->for($season)->create();
    Fixture::factory()->for($stage)->create(['match_date' => now()->subHour()]);

    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bonus::class, ['pool' => $pool])
        ->set('championTeamId', $a->id)
        ->set('groups.A', [$a->id, $b->id]);

    expect(ChampionBet::where('season_id', $season->id)->exists())->toBeFalse()
        ->and(GroupBet::where('season_id', $season->id)->exists())->toBeFalse();
});
