# Bolão Phase 3d — Bonus Predictions & Leaderboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let pool participants make champion + group-standings bonus predictions (locked at the season's first kickoff) and view the pool leaderboard; wire the Show page's "Ranking" and add a "Bônus" entry.

**Architecture:** Two place-Action classes (`PlaceChampionBetAction`, `PlaceGroupBetAction`) hold the lock rule + upsert; `Season` gains `bonusLocksAt()/bonusLocked()`. Two participant-gated class-based Livewire pages: `Pools\Bonus` (champion + per-group selects) and `Pools\Standings` (renders `PoolStandings::for`). The Show page links to both.

**Tech Stack:** Laravel 12, PHP 8.4, Livewire v4 (class), Flux + Flux Pro, Pest 4 (+ pest-plugin-livewire). Run via `docker exec oddly_php php artisan ...` (host PHP 8.3 fails). Pint on host.

---

## Conventions & notes

- **Branch `feature/bolao-phase3d` is checked out.** Do not branch/switch.
- Class-based Livewire (no Volt/SFC); `#[Layout('layouts.dashboard')]`. Flux markup mirrors `resources/views/livewire/pools/bets.blade.php`; use the `fluxui-development` skill for unfamiliar components. Tests assert behavior/visible text.
- Ignore intelephense "Undefined Livewire/auth" warnings (false positives).
- Reuse Phase 1: `ChampionBet` (unique `user,season`), `GroupBet` (unique `user,season,group_letter,predicted_position`), `Season::teams()` (pivot `group_letter`/`group_position`), `Season::fixtures()` (HasManyThrough), `App\Services\PoolStandings::for(Pool)`. `Pool::participants()`, `Pool::season()`. Dates are CarbonImmutable; `Model::unguard()` global; `preventLazyLoading` on in non-prod.
- After PHP edits: `vendor/bin/pint --dirty --format agent`.

---

## Task 1: Season bonus-lock helpers

**Files:**
- Modify: `app/Models/Season.php`
- Test: `tests/Feature/SeasonBonusLockTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Models\Fixture;
use App\Models\Season;
use App\Models\Stage;

it('has no bonus lock when the season has no fixtures', function (): void {
    $season = Season::factory()->create();

    expect($season->bonusLocksAt())->toBeNull()
        ->and($season->bonusLocked())->toBeFalse();
});

it('locks bonus at the earliest fixture kickoff', function (): void {
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    Fixture::factory()->for($stage)->create(['match_date' => now()->addDays(3)]);
    Fixture::factory()->for($stage)->create(['match_date' => now()->subHour()]);

    expect($season->bonusLocked())->toBeTrue();
});

it('is unlocked when the earliest kickoff is in the future', function (): void {
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    Fixture::factory()->for($stage)->create(['match_date' => now()->addDay()]);

    expect($season->bonusLocked())->toBeFalse();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/SeasonBonusLockTest.php`
Expected: FAIL — methods not defined.

- [ ] **Step 3: Add the methods to `app/Models/Season.php`**

Add `use Carbon\CarbonInterface;` and `use Illuminate\Support\Carbon;` at the top, then add these methods to the class:

```php
public function bonusLocksAt(): ?CarbonInterface
{
    $earliest = $this->fixtures()->min('fixtures.match_date');

    return $earliest !== null ? Carbon::parse($earliest) : null;
}

public function bonusLocked(): bool
{
    $at = $this->bonusLocksAt();

    return $at !== null && now()->gte($at);
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/SeasonBonusLockTest.php`
Expected: PASS (3 passed).

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Models/Season.php tests/Feature/SeasonBonusLockTest.php
git commit -m "feat: add season bonus-lock helpers"
```

---

## Task 2: PlaceChampionBetAction

**Files:**
- Create: `app/Actions/Bet/PlaceChampionBetAction.php`
- Test: `tests/Feature/Pools/PlaceChampionBetActionTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Bet\PlaceChampionBetAction;
use App\Models\ChampionBet;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Stage;
use App\Models\Team;
use App\Models\User;

it('upserts the champion bet for the user and season', function (): void {
    $user = User::factory()->create();
    $season = Season::factory()->create();
    $a = Team::factory()->create();
    $b = Team::factory()->create();

    app(PlaceChampionBetAction::class)->handle($user, $season, $a->id);
    app(PlaceChampionBetAction::class)->handle($user, $season, $b->id);

    expect(ChampionBet::where('user_id', $user->id)->where('season_id', $season->id)->count())->toBe(1)
        ->and(ChampionBet::where('user_id', $user->id)->where('season_id', $season->id)->first()->team_id)->toBe($b->id);
});

it('throws when the bonus is locked', function (): void {
    $user = User::factory()->create();
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    Fixture::factory()->for($stage)->create(['match_date' => now()->subHour()]);
    $team = Team::factory()->create();

    expect(fn () => app(PlaceChampionBetAction::class)->handle($user, $season, $team->id))
        ->toThrow(RuntimeException::class);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Pools/PlaceChampionBetActionTest.php`
Expected: FAIL — action not found.

- [ ] **Step 3: Implement the action**

```php
<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\ChampionBet;
use App\Models\Season;
use App\Models\User;
use RuntimeException;

final class PlaceChampionBetAction
{
    public function handle(User $user, Season $season, int $teamId): ChampionBet
    {
        if ($season->bonusLocked()) {
            throw new RuntimeException('Os palpites bônus estão encerrados.');
        }

        return ChampionBet::updateOrCreate(
            ['user_id' => $user->id, 'season_id' => $season->id],
            ['team_id' => $teamId],
        );
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Pools/PlaceChampionBetActionTest.php`
Expected: PASS (2 passed).

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Bet/PlaceChampionBetAction.php tests/Feature/Pools/PlaceChampionBetActionTest.php
git commit -m "feat: add place champion bet action"
```

---

## Task 3: PlaceGroupBetAction

**Files:**
- Create: `app/Actions/Bet/PlaceGroupBetAction.php`
- Test: `tests/Feature/Pools/PlaceGroupBetActionTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Bet\PlaceGroupBetAction;
use App\Models\Fixture;
use App\Models\GroupBet;
use App\Models\Season;
use App\Models\Stage;
use App\Models\Team;
use App\Models\User;

it('upserts first and second group bets', function (): void {
    $user = User::factory()->create();
    $season = Season::factory()->create();
    $first = Team::factory()->create();
    $second = Team::factory()->create();

    app(PlaceGroupBetAction::class)->handle($user, $season, 'A', [1 => $first->id, 2 => $second->id]);

    expect(GroupBet::where('user_id', $user->id)->where('season_id', $season->id)->where('group_letter', 'A')->count())->toBe(2);

    // Re-run updates rather than duplicating.
    $other = Team::factory()->create();
    app(PlaceGroupBetAction::class)->handle($user, $season, 'A', [1 => $other->id, 2 => $second->id]);

    expect(GroupBet::where('user_id', $user->id)->where('season_id', $season->id)->where('group_letter', 'A')->count())->toBe(2)
        ->and(GroupBet::where('user_id', $user->id)->where('season_id', $season->id)->where('group_letter', 'A')->where('predicted_position', 1)->first()->team_id)->toBe($other->id);
});

it('throws when the bonus is locked', function (): void {
    $user = User::factory()->create();
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    Fixture::factory()->for($stage)->create(['match_date' => now()->subHour()]);
    $team = Team::factory()->create();

    expect(fn () => app(PlaceGroupBetAction::class)->handle($user, $season, 'A', [1 => $team->id, 2 => $team->id]))
        ->toThrow(RuntimeException::class);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Pools/PlaceGroupBetActionTest.php`
Expected: FAIL — action not found.

- [ ] **Step 3: Implement the action**

```php
<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\GroupBet;
use App\Models\Season;
use App\Models\User;
use RuntimeException;

final class PlaceGroupBetAction
{
    /**
     * @param  array<int, int>  $positions  Map of predicted_position => team_id.
     */
    public function handle(User $user, Season $season, string $groupLetter, array $positions): void
    {
        if ($season->bonusLocked()) {
            throw new RuntimeException('Os palpites bônus estão encerrados.');
        }

        foreach ($positions as $position => $teamId) {
            GroupBet::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'season_id' => $season->id,
                    'group_letter' => $groupLetter,
                    'predicted_position' => $position,
                ],
                ['team_id' => $teamId],
            );
        }
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Pools/PlaceGroupBetActionTest.php`
Expected: PASS (2 passed).

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Bet/PlaceGroupBetAction.php tests/Feature/Pools/PlaceGroupBetActionTest.php
git commit -m "feat: add place group bet action"
```

---

## Task 4: Standings (leaderboard) page + wire Ranking

**Files:**
- Create: `app/Livewire/Pools/Standings.php`
- Create: `resources/views/livewire/pools/standings.blade.php`
- Modify: `routes/web.php`, `resources/views/livewire/pools/show.blade.php`
- Test: generated colocated test (replace body)

- [ ] **Step 1: Scaffold**

Run: `docker exec oddly_php php artisan make:livewire Pools/Standings --class --no-interaction`. Create the test manually if none generated (`tests/Feature/Livewire/Pools/StandingsTest.php`).

- [ ] **Step 2: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Enums\Fixture\Status;
use App\Livewire\Pools\Standings;
use App\Models\Bet;
use App\Models\Fixture;
use App\Models\Pool;
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

    $leader = User::factory()->create(['name' => 'Campeão']);
    actingAs($leader);
    app(JoinPoolAction::class)->handle($leader, $pool);
    Bet::factory()->for($fixture)->create([
        'user_id' => $leader->id, 'home_score' => 2, 'away_score' => 1,
        'is_exact' => true, 'is_correct_result' => true,
    ]);

    Livewire::test(Standings::class, ['pool' => $pool])
        ->assertOk()
        ->assertSee('Campeão')
        ->assertSee('10');
})->uses();
```

Note: add `use App\Models\Season;` to the imports (the test references it). Remove the stray `->uses()` if your Pest version rejects it — it's not needed; the canonical form is just the closure. Use:
```php
});
```
to close the last test (no `->uses()`).

- [ ] **Step 3: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Livewire/Pools/StandingsTest.php`
Expected: FAIL.

- [ ] **Step 4: Implement the component**

`app/Livewire/Pools/Standings.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Models\Pool;
use App\Services\PoolStandings;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Standings extends Component
{
    public Pool $pool;

    public function mount(Pool $pool): void
    {
        abort_unless($pool->participants()->whereKey(auth()->id())->exists(), 403);

        $this->pool = $pool;
    }

    /**
     * @return Collection<int, array{user: \App\Models\User, points: int}>
     */
    public function standings(): Collection
    {
        return app(PoolStandings::class)->for($this->pool);
    }

    public function render(): View
    {
        return view('livewire.pools.standings', ['standings' => $this->standings()]);
    }
}
```

- [ ] **Step 5: Implement the view**

`resources/views/livewire/pools/standings.blade.php`:

```blade
<div class="space-y-6">
    <flux:heading size="xl">{{ $pool->name }} · Ranking</flux:heading>

    <flux:card>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-zinc-500">
                    <th class="px-3 py-2">#</th>
                    <th class="px-3 py-2">Jogador</th>
                    <th class="px-3 py-2 text-right">Pontos</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($standings as $index => $row)
                    <tr class="border-t border-zinc-100 dark:border-white/10">
                        <td class="px-3 py-2">{{ $index + 1 }}</td>
                        <td class="px-3 py-2">{{ $row['user']->name }}</td>
                        <td class="px-3 py-2 text-right font-semibold">{{ $row['points'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-3 py-4 text-center text-zinc-500">Sem participantes.</td></tr>
                @endforelse
            </tbody>
        </table>
    </flux:card>
</div>
```

- [ ] **Step 6: Register the route + wire the Ranking button**

In `routes/web.php`, in the `auth` group after `/pools/{pool:slug}/bets`:
```php
Route::get('/pools/{pool:slug}/standings', \App\Livewire\Pools\Standings::class)->name('pools.standings');
```

In `resources/views/livewire/pools/show.blade.php`, change the "Ranking" button from `href="#"` to `:href="route('pools.standings', $pool)"`.

- [ ] **Step 7: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Livewire/Pools/StandingsTest.php`
Expected: PASS (3 passed). Then full suite.

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Pools/Standings.php resources/views/livewire/pools/standings.blade.php routes/web.php resources/views/livewire/pools/show.blade.php tests/Feature/Livewire/Pools/StandingsTest.php
git commit -m "feat: add pool leaderboard page and wire Ranking button"
```

---

## Task 5: Bonus predictions page + wire Bônus

**Files:**
- Create: `app/Livewire/Pools/Bonus.php`
- Create: `resources/views/livewire/pools/bonus.blade.php`
- Modify: `routes/web.php`, `resources/views/livewire/pools/show.blade.php`
- Test: generated colocated test (replace body)

- [ ] **Step 1: Scaffold**

Run: `docker exec oddly_php php artisan make:livewire Pools/Bonus --class --no-interaction`. Create the test manually if none generated (`tests/Feature/Livewire/Pools/BonusTest.php`).

- [ ] **Step 2: Write the failing test**

```php
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
```

- [ ] **Step 3: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Livewire/Pools/BonusTest.php`
Expected: FAIL.

- [ ] **Step 4: Implement the component**

`app/Livewire/Pools/Bonus.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Bet\PlaceChampionBetAction;
use App\Actions\Bet\PlaceGroupBetAction;
use App\Models\Pool;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Bonus extends Component
{
    public Pool $pool;

    public ?int $championTeamId = null;

    /**
     * @var array<string, array{first: int|null, second: int|null}>
     */
    public array $groups = [];

    public bool $saved = false;

    public function mount(Pool $pool): void
    {
        abort_unless($pool->participants()->whereKey(auth()->id())->exists(), 403);

        $pool->loadMissing('season');
        $this->pool = $pool;

        $season = $pool->season;

        $this->championTeamId = $season->championBets()
            ->where('user_id', auth()->id())
            ->value('team_id');

        $groupBets = $season->groupBets()
            ->where('user_id', auth()->id())
            ->get();

        foreach ($this->groupLetters() as $letter) {
            $this->groups[$letter] = [
                'first' => $groupBets->firstWhere(fn ($b) => $b->group_letter === $letter && $b->predicted_position === 1)?->team_id,
                'second' => $groupBets->firstWhere(fn ($b) => $b->group_letter === $letter && $b->predicted_position === 2)?->team_id,
            ];
        }
    }

    public function save(PlaceChampionBetAction $champion, PlaceGroupBetAction $group): void
    {
        $this->saved = false;

        if ($this->pool->season->bonusLocked()) {
            return;
        }

        $user = auth()->user();
        $season = $this->pool->season;

        if ($this->championTeamId !== null) {
            $champion->handle($user, $season, (int) $this->championTeamId);
        }

        foreach ($this->groups as $letter => $positions) {
            if (! empty($positions['first']) && ! empty($positions['second'])) {
                $group->handle($user, $season, (string) $letter, [
                    1 => (int) $positions['first'],
                    2 => (int) $positions['second'],
                ]);
            }
        }

        $this->saved = true;
    }

    public function locked(): bool
    {
        return $this->pool->season->bonusLocked();
    }

    /**
     * @return Collection<int, string>
     */
    public function groupLetters(): Collection
    {
        return $this->pool->season->teams()
            ->wherePivotNotNull('group_letter')
            ->get()
            ->pluck('pivot.group_letter')
            ->unique()
            ->sort()
            ->values();
    }

    /**
     * @return Collection<int, \App\Models\Team>
     */
    public function teamsInGroup(string $letter): Collection
    {
        return $this->pool->season->teams()
            ->wherePivot('group_letter', $letter)
            ->get();
    }

    /**
     * @return Collection<int, \App\Models\Team>
     */
    public function allTeams(): Collection
    {
        return $this->pool->season->teams()->get();
    }

    public function render(): View
    {
        return view('livewire.pools.bonus', [
            'groupLetters' => $this->groupLetters(),
            'allTeams' => $this->allTeams(),
        ]);
    }
}
```

- [ ] **Step 5: Implement the view**

`resources/views/livewire/pools/bonus.blade.php`:

```blade
<div class="space-y-6">
    <flux:heading size="xl">{{ $pool->name }} · Bônus</flux:heading>

    @if ($saved)
        <flux:callout variant="success">Bônus salvos.</flux:callout>
    @endif

    @if ($this->locked())
        <flux:callout variant="warning">Os palpites bônus estão encerrados.</flux:callout>
    @endif

    <form wire:submit="save" class="space-y-8">
        <flux:card>
            <flux:heading size="lg" class="mb-3">Campeão</flux:heading>
            <flux:select wire:model="championTeamId" :disabled="$this->locked()">
                <flux:select.option value="">Selecione…</flux:select.option>
                @foreach ($allTeams as $team)
                    <flux:select.option :value="$team->id">{{ $team->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </flux:card>

        @foreach ($groupLetters as $letter)
            <flux:card>
                <flux:heading size="lg" class="mb-3">Grupo {{ $letter }}</flux:heading>
                <div class="grid grid-cols-2 gap-4">
                    <flux:select label="1º" wire:model="groups.{{ $letter }}.first" :disabled="$this->locked()">
                        <flux:select.option value="">Selecione…</flux:select.option>
                        @foreach ($this->teamsInGroup($letter) as $team)
                            <flux:select.option :value="$team->id">{{ $team->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select label="2º" wire:model="groups.{{ $letter }}.second" :disabled="$this->locked()">
                        <flux:select.option value="">Selecione…</flux:select.option>
                        @foreach ($this->teamsInGroup($letter) as $team)
                            <flux:select.option :value="$team->id">{{ $team->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </flux:card>
        @endforeach

        @unless ($this->locked())
            <flux:button type="submit" variant="primary" color="cyan">Salvar bônus</flux:button>
        @endunless
    </form>
</div>
```

- [ ] **Step 6: Register the route + wire the Bônus button**

In `routes/web.php`, in the `auth` group after `/pools/{pool:slug}/standings`:
```php
Route::get('/pools/{pool:slug}/bonus', \App\Livewire\Pools\Bonus::class)->name('pools.bonus');
```

In `resources/views/livewire/pools/show.blade.php`, add a "Bônus" button next to Palpites/Ranking:
```blade
<flux:button :href="route('pools.bonus', $pool)" variant="ghost">Bônus</flux:button>
```

- [ ] **Step 7: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Livewire/Pools/BonusTest.php`
Expected: PASS (4 passed). Then full suite.

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Pools/Bonus.php resources/views/livewire/pools/bonus.blade.php routes/web.php resources/views/livewire/pools/show.blade.php tests/Feature/Livewire/Pools/BonusTest.php
git commit -m "feat: add bonus predictions page and wire Bônus button"
```

---

## Task 6: Full suite green + wrap-up

**Files:** none (verification)

- [ ] **Step 1: Full suite**

Run: `docker exec oddly_php php artisan test --compact`
Expected: all PASS (Phases 1–3c + 3d).

- [ ] **Step 2: Migrations + seed clean**

Run: `docker exec oddly_php php artisan migrate:fresh --seed --no-interaction`
Expected: clean.

- [ ] **Step 3: Formatting**

Run: `vendor/bin/pint --dirty --format agent`
Expected: clean.

- [ ] **Step 4: Final commit if needed**

```bash
git add -A
git commit -m "chore: phase 3d green" || echo "nothing to commit"
```

---

## Self-Review (completed during authoring)

- **Spec coverage:** Season bonus-lock helpers (T1); PlaceChampionBetAction w/ lock (T2); PlaceGroupBetAction w/ lock (T3); Standings page + Ranking wiring (T4); Bonus page (champion + group selects, prefilled, lock-aware, saved banner) + Bônus wiring (T5); full green (T6). Both pages participant-gated.
- **Type/name consistency:** routes `pools.standings`/`pools.bonus`; components `App\Livewire\Pools\{Standings,Bonus}`; actions `PlaceChampionBetAction::handle(User,Season,int):ChampionBet`, `PlaceGroupBetAction::handle(User,Season,string,array):void`; `Season::bonusLocksAt()/bonusLocked()`; Bonus props `championTeamId`, `groups[letter][first|second]`, `saved`. Tests match.
- **Placeholder scan:** no TBD/TODO; full code in every step. The Standings test has an explicit note to drop the stray `->uses()` and import `Season`. Flux uncertainties point at the `fluxui-development` skill; tests assert behavior/visible text.
- **Lock + lazy-load:** actions throw via `Season::bonusLocked()`; Bonus `save()` short-circuits when locked (so disabled inputs can't write); `mount` does `loadMissing('season')`; group/team reads use the `teams()` relation query (pivot eager via the query). `championBets`/`groupBets` relations exist (Phase 1).
- **PoolStandings reuse:** Standings renders the service collection shape `['user'=>User,'points'=>int]` exactly as the service returns.
```
