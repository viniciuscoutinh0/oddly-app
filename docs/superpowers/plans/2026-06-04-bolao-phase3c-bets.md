# Bolão Phase 3c — Player Score Bets Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let a pool participant enter/edit their score predictions for the pool's season fixtures on a dedicated "Palpites" page, lock-aware via `PlaceBetAction`.

**Architecture:** A class-based Livewire page `Pools\Bets` (route `/pools/{pool:slug}/bets`, participant-gated) renders the season's fixtures grouped by stage, binds a `scores[fixtureId]` array prefilled from the user's existing global bets, and on save upserts each editable (non-locked) fixture through the Phase 1 `PlaceBetAction`. The Show page's "Palpites" button links here.

**Tech Stack:** Laravel 12, PHP 8.4, Livewire v4 (class), Flux + Flux Pro, Pest 4 (+ pest-plugin-livewire). Run via `docker exec oddly_php php artisan ...` (host PHP 8.3 fails). Pint on host.

---

## Conventions & notes

- **Branch `feature/bolao-phase3c` is checked out.** Do not branch/switch.
- Class-based Livewire (no Volt/SFC); `#[Layout('layouts.dashboard')]`. Flux markup mirrors `resources/views/livewire/pools/show.blade.php`; use the `fluxui-development` skill for unfamiliar components. Tests assert behavior/visible text, not Flux internals.
- Ignore intelephense "Undefined type/method Livewire/auth" warnings (false positives — vendor not indexed); rely on the test run.
- Reuse: `App\Actions\Bet\PlaceBetAction::handle(User,Fixture,int,int): Bet` (throws `RuntimeException` if `Fixture::isLocked()`); `App\Models\Bet` (unique user+fixture); `App\Models\Fixture` (`homeTeam`,`awayTeam`,`stage`,`match_date`,`status`,`isLocked()`,`isFinished()`); `App\Models\Season::fixtures()` (HasManyThrough); `Stage` `name` (enum w/ `getLabel()`) + `sort_order`; `Pool` `season()`,`participants()`. `preventLazyLoading` is on in non-prod — eager-load.
- After PHP edits: `vendor/bin/pint --dirty --format agent`.

---

## Task 1: Bets page (component + view + route + tests)

**Files:**
- Create: `app/Livewire/Pools/Bets.php`
- Create: `resources/views/livewire/pools/bets.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Livewire/Pools/BetsTest.php`

- [ ] **Step 1: Scaffold the component**

Run: `docker exec oddly_php php artisan make:livewire Pools/Bets --class --no-interaction`. If it doesn't generate a test, create `tests/Feature/Livewire/Pools/BetsTest.php` manually in Step 2.

- [ ] **Step 2: Write the failing test**

`tests/Feature/Livewire/Pools/BetsTest.php`:

```php
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
use function Pest\Laravel\get;

function poolWithFixture(callable $fixtureState = null): array
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
    ], $fixtureState ? $fixtureState($home, $away) : []));

    return [$pool, $fixture];
}

it('redirects guests to login', function (): void {
    [$pool] = poolWithFixture();

    get("/pools/{$pool->slug}/bets")->assertRedirect(route('login'));
});

it('forbids a non-participant', function (): void {
    [$pool] = poolWithFixture();
    actingAs(User::factory()->create());

    Livewire::test(Bets::class, ['pool' => $pool])->assertForbidden();
});

it('lets a participant open it and lists the fixtures', function (): void {
    [$pool, $fixture] = poolWithFixture();
    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bets::class, ['pool' => $pool])
        ->assertOk()
        ->assertSee('Brasil')
        ->assertSee('Argentina');
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
        ->set("scores.{$fixture->id}.home", 2)
        ->set("scores.{$fixture->id}.away", 0)
        ->call('save')
        ->assertHasNoErrors();

    expect(Bet::where('user_id', $user->id)->where('fixture_id', $fixture->id)->first())
        ->not->toBeNull()
        ->home_score->toBe(2)
        ->away_score->toBe(0);
});

it('does not save a locked fixture', function (): void {
    [$pool, $fixture] = poolWithFixture(fn () => ['match_date' => now()->subHour()]);
    $user = User::factory()->create();
    actingAs($user);
    app(JoinPoolAction::class)->handle($user, $pool);

    Livewire::test(Bets::class, ['pool' => $pool])
        ->set("scores.{$fixture->id}.home", 2)
        ->set("scores.{$fixture->id}.away", 0)
        ->call('save')
        ->assertHasNoErrors();

    expect(Bet::where('fixture_id', $fixture->id)->exists())->toBeFalse();
});
```

- [ ] **Step 3: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Livewire/Pools/BetsTest.php`
Expected: FAIL — component/route missing.

- [ ] **Step 4: Implement the component**

`app/Livewire/Pools/Bets.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Bet\PlaceBetAction;
use App\Models\Bet;
use App\Models\Pool;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Bets extends Component
{
    public Pool $pool;

    /**
     * @var array<int, array{home: int|string|null, away: int|string|null}>
     */
    public array $scores = [];

    private ?Collection $fixturesCache = null;

    public function mount(Pool $pool): void
    {
        abort_unless(
            $pool->participants()->whereKey(auth()->id())->exists(),
            403,
        );

        $pool->loadMissing('season');
        $this->pool = $pool;

        $fixtures = $this->fixtures();

        $bets = Bet::query()
            ->where('user_id', auth()->id())
            ->whereIn('fixture_id', $fixtures->pluck('id'))
            ->get()
            ->keyBy('fixture_id');

        foreach ($fixtures as $fixture) {
            $this->scores[$fixture->id] = [
                'home' => $bets[$fixture->id]->home_score ?? null,
                'away' => $bets[$fixture->id]->away_score ?? null,
            ];
        }
    }

    public function save(PlaceBetAction $action): void
    {
        $user = auth()->user();

        foreach ($this->fixtures() as $fixture) {
            if ($fixture->isLocked()) {
                continue;
            }

            $home = $this->scores[$fixture->id]['home'] ?? null;
            $away = $this->scores[$fixture->id]['away'] ?? null;

            if ($home === null || $home === '' || $away === null || $away === '') {
                continue;
            }

            $action->handle($user, $fixture, (int) $home, (int) $away);
        }

        \Filament\Notifications\Notification::make()
            ->title('Palpites salvos.')
            ->success()
            ->send();
    }

    /**
     * @return Collection<int, \App\Models\Fixture>
     */
    public function fixtures(): Collection
    {
        return $this->fixturesCache ??= $this->pool->season->fixtures()
            ->with(['homeTeam', 'awayTeam', 'stage'])
            ->get()
            ->sortBy(fn ($fixture): string => sprintf(
                '%03d-%s',
                $fixture->stage->sort_order,
                $fixture->match_date->format('YmdHis'),
            ))
            ->values();
    }

    public function render(): View
    {
        return view('livewire.pools.bets', ['fixtures' => $this->fixtures()]);
    }
}
```

Note: `Notification` is Filament's; it works app-wide. If sending a Filament notification from this non-Filament Livewire page causes an issue in tests, replace the notification block with `session()->flash('status', 'Palpites salvos.');` and render it in the view — keep the save behavior. The tests do not assert the notification.

- [ ] **Step 5: Implement the view**

`resources/views/livewire/pools/bets.blade.php` (adjust Flux components to the installed version via the `fluxui-development` skill if needed; keep team names + inputs rendered):

```blade
<div class="space-y-6">
    <flux:heading size="xl">{{ $pool->name }} · Palpites</flux:heading>

    <form wire:submit="save" class="space-y-8">
        @foreach ($fixtures->groupBy(fn ($fixture) => $fixture->stage->name->getLabel()) as $stageLabel => $stageFixtures)
            <flux:card>
                <flux:heading size="lg" class="mb-3">{{ $stageLabel }}</flux:heading>

                <div class="space-y-3">
                    @foreach ($stageFixtures as $fixture)
                        <div class="flex items-center gap-3">
                            <div class="flex-1 text-right">{{ $fixture->homeTeam?->name ?? 'A definir' }}</div>

                            <flux:input
                                type="number"
                                min="0"
                                class="w-16"
                                wire:model="scores.{{ $fixture->id }}.home"
                                :disabled="$fixture->isLocked()"
                            />
                            <span>x</span>
                            <flux:input
                                type="number"
                                min="0"
                                class="w-16"
                                wire:model="scores.{{ $fixture->id }}.away"
                                :disabled="$fixture->isLocked()"
                            />

                            <div class="flex-1">{{ $fixture->awayTeam?->name ?? 'A definir' }}</div>

                            <div class="w-40 text-sm text-zinc-500">
                                @if ($fixture->isLocked())
                                    Encerrado
                                @else
                                    {{ $fixture->match_date->format('d/m H:i') }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        @endforeach

        <flux:button type="submit" variant="primary" color="cyan">Salvar palpites</flux:button>
    </form>
</div>
```

- [ ] **Step 6: Register the route**

In `routes/web.php`, inside the `auth` group, AFTER `/pools/{pool:slug}`:

```php
Route::get('/pools/{pool:slug}/bets', \App\Livewire\Pools\Bets::class)->name('pools.bets');
```

- [ ] **Step 7: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Livewire/Pools/BetsTest.php`
Expected: PASS (6 passed). Then full suite `docker exec oddly_php php artisan test --compact` → all pass.

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Pools/Bets.php resources/views/livewire/pools/bets.blade.php routes/web.php tests/Feature/Livewire/Pools/BetsTest.php
git commit -m "feat: add player score bets page"
```

---

## Task 2: Wire the Show page's "Palpites" button

**Files:**
- Modify: `resources/views/livewire/pools/show.blade.php`
- Test: `tests/Feature/Livewire/Pools/ShowTest.php` (add an assertion)

- [ ] **Step 1: Add a test**

Append to `tests/Feature/Livewire/Pools/ShowTest.php`:

```php
it('links the palpites button to the bets page', function (): void {
    $member = App\Models\User::factory()->create();
    actingAs($member);
    $pool = Pool::factory()->public()->create();
    app(App\Actions\Pool\JoinPoolAction::class)->handle($member, $pool);

    Livewire::test(Show::class, ['pool' => $pool])
        ->assertSee(route('pools.bets', $pool));
});
```

(Reuse the file's existing imports; `actingAs`, `Pool`, `Show` are already imported.)

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Livewire/Pools/ShowTest.php`
Expected: FAIL — the "Palpites" button still uses `href="#"`.

- [ ] **Step 3: Update the view**

In `resources/views/livewire/pools/show.blade.php`, change the "Palpites" button from
`<flux:button href="#" variant="ghost">Palpites</flux:button>` to:

```blade
<flux:button :href="route('pools.bets', $pool)" variant="ghost">Palpites</flux:button>
```

Leave the "Ranking" button as `href="#"` (3d).

- [ ] **Step 4: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Livewire/Pools/ShowTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add resources/views/livewire/pools/show.blade.php tests/Feature/Livewire/Pools/ShowTest.php
git commit -m "feat: wire pool Palpites button to the bets page"
```

---

## Task 3: Full suite green + wrap-up

**Files:** none (verification)

- [ ] **Step 1: Full suite**

Run: `docker exec oddly_php php artisan test --compact`
Expected: all PASS (Phases 1–3b + 3c).

- [ ] **Step 2: Migrations + seed clean**

Run: `docker exec oddly_php php artisan migrate:fresh --seed --no-interaction`
Expected: clean.

- [ ] **Step 3: Formatting**

Run: `vendor/bin/pint --dirty --format agent`
Expected: clean.

- [ ] **Step 4: Final commit if needed**

```bash
git add -A
git commit -m "chore: phase 3c green" || echo "nothing to commit"
```

---

## Self-Review (completed during authoring)

- **Spec coverage:** Bets page (participant-gated mount → 403; lists season fixtures grouped by stage; prefilled scores; lock-aware save via PlaceBetAction skipping locked) — T1; Show "Palpites" button wired — T2; full green — T3. Bonus/leaderboard correctly out of scope.
- **Type/name consistency:** route `pools.bets`; component `App\Livewire\Pools\Bets`; methods `mount(Pool)`, `save(PlaceBetAction)`, `fixtures(): Collection`; `scores[fixtureId][home|away]`. Tests reference these exactly.
- **Placeholder scan:** no TBD/TODO; full code in every step. The Filament `Notification` use has a session-flash fallback noted (tests don't assert it). Flux uncertainties point at the skill with a behavioral fallback.
- **Lock handling:** `save()` guards `isLocked()` before calling `PlaceBetAction` (which would otherwise throw); the "does not save a locked fixture" test proves no bet is created for a past-kickoff fixture.
- **Lazy-load:** `mount` does `loadMissing('season')`; fixtures eager-load homeTeam/awayTeam/stage; `fixturesCache` avoids a second query within the request.
