# Bolão Phase 3c — Player Score Bets Design Spec

**Date:** 2026-06-04
**Status:** Approved (autonomous — user authorized proceeding without per-step approval for this phase)
**Depends on:** Phases 1, 2, 3a, 3b (merged to `main`).
**Part of:** Phase 3 (Flux player UI). Covers **3c only** (3d = champion/group bonus + leaderboard).

## Overview

Let a pool participant enter and edit their score predictions for the fixtures of the
pool's season, on a dedicated "Palpites" page. Bets are global per `(user, fixture)`
(Phase 1 model) and reused across pools; the pool context gates access (must be a
participant) and provides scoring. Lock is enforced per fixture via the Phase 1
`PlaceBetAction`. Wires the Show page's inert "Palpites" button to this page.

## Existing pieces (reuse)

- `App\Models\Bet` (`user_id`, `fixture_id`, `home_score`, `away_score`, flags); unique
  `(user_id, fixture_id)`. `App\Actions\Bet\PlaceBetAction::handle(User, Fixture, int, int): Bet`
  — throws `RuntimeException` if the fixture is locked (`Fixture::isLocked()` =
  `locked_at ?? match_date <= now()`); otherwise updateOrCreate.
- `App\Models\Fixture`: `homeTeam()`, `awayTeam()`, `stage()`, `match_date`, `status`
  (enum), `isLocked()`, `isFinished()`. `App\Models\Season::fixtures()` (HasManyThrough via
  Stage). `Stage` has `name` (enum) + `sort_order`.
- `App\Models\Pool`: `season()`, `participants()`.
- Class-based Livewire 4, `#[Layout('layouts.dashboard')]`, Flux UI. Tests via
  `docker exec oddly_php php artisan test --compact` (+ pest-plugin-livewire).

## Decisions (locked, autonomous defaults)

- **Dedicated page** `/pools/{pool:slug}/bets` named `pools.bets`, component
  `App\Livewire\Pools\Bets`.
- **Access:** only a pool **participant** (or the owner, who auto-joins) may open it;
  others get 403. (Reuse: check `pool.participants` contains the user.)
- **Listing:** the pool's season fixtures, ordered by `stage.sort_order` then
  `match_date`, grouped by stage for readability. Each row shows the two teams (name;
  "A definir" when null) and the kickoff date.
- **Inputs:** two number inputs (home/away) per fixture, prefilled from the user's
  existing bet. **Locked** fixtures (kickoff passed / `locked_at`) are disabled and show
  the actual score when finished; not editable.
- **Save:** one "Salvar palpites" button. Iterates the editable (non-locked) fixtures
  with both scores filled and upserts via `PlaceBetAction`. Locked fixtures are skipped.
  A success notification is shown.
- **Bets are global per user** — editing here updates the user's prediction used in every
  pool they belong to (by design, Phase 1).

## Component — `App\Livewire\Pools\Bets`

- `public Pool $pool;` route-bound by slug.
- `mount(Pool $pool)`: `abort_unless($pool->participants()->whereKey(auth()->id())->exists(), 403)`;
  load the season fixtures with `homeTeam`, `awayTeam`, `stage`; preload the user's bets for
  those fixtures into a `scores` array keyed by fixture id (`['home' => ?int, 'away' => ?int]`).
- `public array $scores = [];` bound via `wire:model` per fixture
  (`wire:model="scores.{id}.home"`).
- `fixtures()`: the ordered/grouped season fixtures (eager-loaded).
- `save(PlaceBetAction $action)`: for each fixture that is NOT locked and has both
  `scores[id][home]` and `scores[id][away]` set (non-null), call
  `$action->handle(auth()->user(), $fixture, (int) home, (int) away)`. Then notify success.
  (Locked fixtures are never submitted; `PlaceBetAction` is the authority and would throw —
  the component guards first to avoid the exception.)
- `#[Layout('layouts.dashboard')]`.

## View — `resources/views/livewire/pools/bets.blade.php`

- Heading with the pool name + "Palpites".
- Fixtures grouped by stage (stage label via the enum). Each row: home team — inputs —
  away team — kickoff. Locked rows: inputs disabled; if finished, show the real score.
- "Salvar palpites" submit (`wire:submit="save"`).
- Uses Flux (`flux:input type=number`, `flux:button`, `flux:heading`, `flux:card`).

## Wire the Show page

In `resources/views/livewire/pools/show.blade.php`, change the "Palpites" button from
`href="#"` to `:href="route('pools.bets', $pool)"`. "Ranking" stays inert (3d).

## Routes

In `routes/web.php` `auth` group, after `/pools/{pool:slug}`:
`Route::get('/pools/{pool:slug}/bets', \App\Livewire\Pools\Bets::class)->name('pools.bets');`

## Testing (Pest + Livewire)

- guest → `/pools/{slug}/bets` redirects to login.
- a non-participant gets 403; a participant (and the owner) can open it.
- the page lists the pool's season fixtures (renders team names).
- saving creates/updates the participant's bets for editable fixtures (asserts `bets`
  rows with the entered scores), via `PlaceBetAction`.
- a locked fixture (kickoff in the past) is not saved (no bet row created for it).
- existing bet values are prefilled into `scores` on mount.

## File structure (new / changed)

```
routes/web.php                                       (add pools.bets route)
app/Livewire/Pools/Bets.php                          (new)
resources/views/livewire/pools/bets.blade.php        (new)
resources/views/livewire/pools/show.blade.php        (edit: Palpites button href)
<generated colocated test for Bets>                  (tests/Feature/Livewire/Pools/BetsTest.php)
```

## Out of scope (3d / later)

- Champion & group-standings bonus predictions; pool leaderboard ("Ranking" button).
- Bet entry for the bonus types; per-stage filtering/pagination of fixtures.
