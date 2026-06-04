# Bolão Phase 2 — Admin (Filament) Design Spec

**Date:** 2026-06-04
**Status:** Approved
**Depends on:** Phase 1 (domain + scoring engine, merged to `main`).

## Overview

Give administrators a read-only oversight area in the Filament panel for betting
pools: list pools, inspect a pool's configuration and participants, view its
leaderboard, and trigger a points recalculation. Pool creation/editing belongs to
players (Phase 3, Flux) — admins do not create or edit pools here.

## Decisions (locked)

- **Admin role = oversight (read-only).** No create/edit/delete of pools in Phase 2.
- **Navigation:** a new dedicated **"Bolão"** cluster, separate from the existing
  `Tournament` (football-data) cluster.
- **Leaderboard:** a custom per-pool **Standings page** rendering
  `App\Services\PoolStandings::for($pool)`, with a "Recalcular pontuação" action.
- **No individual-bet inspection** in Phase 2 (pools + participants + standings only).
- **Recalculation = single idempotent action** (approach A): resolves finished
  fixtures' bets + champion bets + group bets for the pool's season.
- **Testing:** `pestphp/pest-plugin-livewire` is installed; use Filament/Livewire
  component tests (`livewire()`), per the project's Filament testing conventions.

## Components & boundaries

### 1. Cluster
`app/Filament/Clusters/Bolao/BolaoCluster.php` — a `Cluster` with a trophy Heroicon
and a navigation label "Bolão". Mirrors the structure of the existing
`TournamentCluster`.

### 2. PoolResource (read-only)
Location: `app/Filament/Clusters/Bolao/Resources/Pools/`. `protected static ?string
$cluster = BolaoCluster::class`.

- **No create/edit/delete.** `getPages()` registers only `index`, `view`, and the
  custom `standings` page. Do not register Create/Edit pages.
- **Table** (`Tables/PoolsTable.php`):
  - `name` (searchable, sortable)
  - `season.name` labeled "Temporada" (sortable) — shows the season year
  - `owner.name` labeled "Dono"
  - `visibility` badge (uses `Visibility` enum `HasLabel`/`HasColor`)
  - `participants_count` labeled "Participantes" via `->counts('participants')`
  - `created_at` (toggleable, hidden by default)
  - Filters: `SelectFilter` on `visibility` (options `Visibility::class`);
    `SelectFilter` on `season` relationship.
  - Row action: a link/action to open the Standings page; plus the default `view`.
  - No `CreateAction`, no bulk delete.
- **View infolist** (`Schemas/PoolInfolist.php`): pool name, description, owner,
  season, visibility, invite_code, and a "Pontuação" section showing
  `points_exact`, `points_result`, `points_champion`, `points_group_position`.
- **Resource properties:** `$navigationIcon` (Heroicon, typed
  `string|BackedEnum|null`), Portuguese model labels ("Bolão"/"Bolões"),
  `$recordTitleAttribute = 'name'`.

### 3. Participants relation manager
`RelationManagers/ParticipantsRelationManager.php` on PoolResource, bound to the
`participants` relationship. Read-only table: `name`, `email`, `pivot.joined_at`
labeled "Entrou em". No attach/detach/create actions.

### 4. Standings page (custom, per record)
`Pages/PoolStandings.php` — extends Filament `Page`, registered in
`PoolResource::getPages()` as `'standings' => PoolStandings::route('/{record}/standings')`,
and surfaced in the resource record sub-navigation.

- Resolves the `Pool` from the route binding, calls
  `App\Services\PoolStandings::for($pool)`, and renders a ranked list (position,
  player name, points) ordered by points desc.
- Rendering: a dedicated Blade view (`resources/views/filament/clusters/bolao/pages/pool-standings.blade.php`)
  iterating the computed collection. (The standings collection is computed, not an
  Eloquent query, so a Blade table is simpler than a Filament table.)
- **Header action "Recalcular pontuação"**: calls
  `RecalculatePoolScoringAction`, then `Notification::make()->success()` and refreshes
  the page. Confirmation modal before running.

### 5. RecalculatePoolScoringAction
`app/Actions/Pool/RecalculatePoolScoringAction.php` — `handle(Pool $pool): void`:

1. `$season = $pool->season;`
2. Resolve finished fixtures' bets: load the season's fixtures with
   `status = Finished` (eager-loaded to avoid `preventLazyLoading`), and call
   `ResolveFixtureBetsAction::handle()` on each.
3. `ResolveChampionBetsAction::handle($season)`.
4. `ResolveGroupBetsAction::handle($season)`.

Wrapped in `DB::transaction`. Reuses the Phase 1 actions (constructor-injected). Pure
PHP, testable without Livewire. Idempotent (re-running yields the same flags).

This action also closes the Phase 1 follow-up about eager-loading on the manual
group/champion recompute path.

## Authorization

- The whole panel already requires `User::canAccessPanel()` → `role === Admin`.
- Read-only is enforced by simply not exposing create/edit/delete pages and actions.
  No new policy needed for Phase 2 (the existing `PoolPolicy` governs the
  player-facing Phase 3 flows).

## Testing (Pest + pest-plugin-livewire)

Feature tests in `tests/Feature`:

- **PoolResource access & listing:**
  - `livewire(ListPools::class)` as an admin renders and `assertCanSeeTableRecords`
    the seeded pools; search/filter by visibility works.
  - A non-admin (`User::factory()->create()`, role Player) gets denied panel access
    (HTTP 403 on the route).
- **View page:** `livewire(ViewPool::class, ['record' => $pool->id])` shows the pool's
  config and participant count.
- **Participants relation manager:** shows joined participants read-only.
- **Standings page:** `livewire(PoolStandings::class, ['record' => $pool->id])` shows
  ranked participants with correct points; invoking the "Recalcular pontuação" action
  resolves bets and updates the displayed points.
- **RecalculatePoolScoringActionTest** (pure unit-ish feature test): season + pool +
  unresolved score/champion/group bets → run the action → all flags resolved and
  `PoolStandings::for()` returns the expected totals; running twice is idempotent.

All tests run inside Docker: `docker exec oddly_php php artisan test --compact`.

## File structure (new)

```
app/Filament/Clusters/Bolao/
  BolaoCluster.php
  Resources/Pools/
    PoolResource.php
    Schemas/PoolInfolist.php
    Tables/PoolsTable.php
    Pages/ListPools.php
    Pages/ViewPool.php
    Pages/PoolStandings.php
    RelationManagers/ParticipantsRelationManager.php
app/Actions/Pool/RecalculatePoolScoringAction.php
resources/views/filament/clusters/bolao/pages/pool-standings.blade.php
tests/Feature/  (PoolResource + standings + recalc action tests)
```

## Out of scope (future phases)

- Pool creation/editing in admin (players own this — Phase 3).
- Individual bet inspection / moderation.
- Dashboard widgets, standings caching.
- Player-facing Flux UI (Phase 3).
