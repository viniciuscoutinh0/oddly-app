# Bolão Phase 3d — Bonus Predictions & Leaderboard Design Spec

**Date:** 2026-06-05
**Status:** Approved (autonomous — user authorized proceeding without per-step approval for Phase 3).
**Depends on:** Phases 1, 2, 3a, 3b, 3c (merged to `main`).
**Part of:** Phase 3 (Flux player UI) — final sub-phase.

## Overview

Complete the player experience: let participants make their **bonus predictions**
(season champion + group standings 1st/2nd per group) before the tournament starts, and
view the pool **leaderboard** (ranking). Wires the Show page's inert "Ranking" button and
adds a "Bônus" entry point. Reuses the Phase 1 bonus models/resolvers and the
`PoolStandings` service.

## Existing pieces (reuse)

- `App\Models\ChampionBet` (`user_id`,`season_id`,`team_id`; unique `(user,season)`).
- `App\Models\GroupBet` (`user_id`,`season_id`,`group_letter`,`team_id`,`predicted_position`;
  unique `(user,season,group_letter,predicted_position)`).
- `App\Models\Season`: `teams()` (belongsToMany withPivot `group_letter`,`group_position`),
  `fixtures()` (HasManyThrough), `championBets()`, `groupBets()`.
- `App\Services\PoolStandings::for(Pool): Collection<int, array{user, points}>`.
- `App\Models\Pool`: `season()`, `participants()`.
- Class-based Livewire 4, `#[Layout('layouts.dashboard')]`, Flux UI. Tests via
  `docker exec oddly_php php artisan test --compact` (+ pest-plugin-livewire).

## Decisions (locked, autonomous defaults)

- **Bonus lock:** champion & group predictions lock at the season's **first fixture
  kickoff** (`Season` first `fixtures.match_date`). Before that, editable; at/after,
  read-only. If the season has no fixtures, treat as not locked (editable).
- **Place actions** (encapsulate rules, testable):
  - `PlaceChampionBetAction::handle(User, Season, int $teamId): ChampionBet` — throws
    `RuntimeException` if bonus is locked; else `updateOrCreate` on `(user, season)`.
  - `PlaceGroupBetAction::handle(User, Season, string $groupLetter, array $positions): void`
    — `$positions = [1 => teamId, 2 => teamId]`; throws if locked; upserts the two
    `GroupBet` rows (`updateOrCreate` on `(user, season, group_letter, predicted_position)`).
- **Bonus lock helper:** add `Season::bonusLocksAt(): ?CarbonInterface` (min fixture
  `match_date`, null if none) and `Season::bonusLocked(): bool` (`bonusLocksAt` not null
  AND `<= now()`). Used by the actions and the page.
- **Bonus page** `/pools/{pool:slug}/bonus` named `pools.bonus`, component
  `App\Livewire\Pools\Bonus` — participant-gated (403 otherwise):
  - **Champion:** a select of the season's teams; prefilled from the user's `ChampionBet`.
  - **Group standings:** for each group letter (derived from `season.teams` pivot
    `group_letter`, sorted), two selects (1º / 2º) of that group's teams; prefilled from the
    user's `GroupBet` rows.
  - Disabled when `bonusLocked()`. "Salvar bônus" saves champion + all groups via the
    actions; rendered success banner. Locked → inputs disabled, save short-circuits.
- **Leaderboard page** `/pools/{pool:slug}/standings` named `pools.standings`, component
  `App\Livewire\Pools\Standings` — participant-gated; renders `PoolStandings::for($pool)`
  as a ranked table (position, player, points).
- **Wire Show buttons:** "Ranking" → `pools.standings`; add a "Bônus" button →
  `pools.bonus`. (Keep "Palpites" → `pools.bets`.)

## Components & files

### Actions
- `app/Actions/Bet/PlaceChampionBetAction.php`
- `app/Actions/Bet/PlaceGroupBetAction.php`

### Model
- `app/Models/Season.php` — add `bonusLocksAt()` + `bonusLocked()`.

### Livewire (class-based, `#[Layout('layouts.dashboard')]`)
- `App\Livewire\Pools\Standings` + `resources/views/livewire/pools/standings.blade.php`
- `App\Livewire\Pools\Bonus` + `resources/views/livewire/pools/bonus.blade.php`

### Routes (`auth` group, after `/pools/{pool:slug}/bets`)
- `Route::get('/pools/{pool:slug}/standings', Standings::class)->name('pools.standings');`
- `Route::get('/pools/{pool:slug}/bonus', Bonus::class)->name('pools.bonus');`

### Show view
- `resources/views/livewire/pools/show.blade.php` — "Ranking" → `route('pools.standings',$pool)`,
  add "Bônus" → `route('pools.bonus',$pool)`.

## Bonus component shape

- `public Pool $pool;` (route-bound), `public ?int $championTeamId = null;`,
  `public array $groups = [];` shaped `['A' => ['first' => ?int, 'second' => ?int], ...]`.
- `mount`: participant 403 guard; `loadMissing('season')`; build the group→teams map from
  `season.teams` pivot; prefill `championTeamId` from the user's ChampionBet and `groups`
  from the user's GroupBet rows.
- `save(PlaceChampionBetAction $champ, PlaceGroupBetAction $group)`: if `bonusLocked()`
  return (no-op); if `championTeamId` set → `$champ->handle(user, season, championTeamId)`;
  for each group with both first+second set → `$group->handle(user, season, $letter, [1=>first, 2=>second])`;
  set `$saved = true`.
- helpers: `teams()` (season teams), `groupTeams(string $letter)`, `locked()` →
  `season->bonusLocked()`.

## Testing (Pest + Livewire)

**PlaceChampionBetAction:** upserts champion bet (user,season); throws when locked (a past
first-fixture kickoff); editable when no fixtures / future kickoff.

**PlaceGroupBetAction:** upserts 1st & 2nd group bets; re-running updates; throws when
locked.

**Season:** `bonusLocksAt()` returns earliest fixture date (null when none);
`bonusLocked()` true when earliest is past, false when future/none.

**Standings component:** guest→login; non-participant 403; participant sees ranked players
with points (seed resolved bets so points are non-zero).

**Bonus component:** guest→login; non-participant 403; participant sees champion + group
selects; saving stores ChampionBet + GroupBet rows; prefilled from existing bets; when
locked, inputs disabled and save makes no changes.

**Show:** "Ranking" links to `pools.standings`; "Bônus" links to `pools.bonus`.

## File structure (new / changed)

```
app/Actions/Bet/PlaceChampionBetAction.php           (new)
app/Actions/Bet/PlaceGroupBetAction.php              (new)
app/Models/Season.php                                (add bonusLocksAt/bonusLocked)
app/Livewire/Pools/Standings.php                     (new)
resources/views/livewire/pools/standings.blade.php   (new)
app/Livewire/Pools/Bonus.php                         (new)
resources/views/livewire/pools/bonus.blade.php       (new)
resources/views/livewire/pools/show.blade.php        (edit: Ranking + Bônus buttons)
routes/web.php                                        (add pools.standings, pools.bonus)
tests/Feature/Pools/PlaceChampionBetActionTest.php   (new)
tests/Feature/Pools/PlaceGroupBetActionTest.php      (new)
tests/Feature/SeasonBonusLockTest.php                (new)
<generated Standings/Bonus component tests>
```

## Out of scope

- Editing bonus scoring (admin Phase 2 handles config); password reset; pagination.
- Real-time standings updates; admin already triggers recompute (Phase 2).
