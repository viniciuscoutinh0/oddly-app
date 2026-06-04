# Bolão — Design Spec

**Date:** 2026-06-04
**Status:** Approved

## Overview

Add the core betting-pool (bolão) domain to the Oddly app on top of the existing
football-data layer (competitions, seasons, stages, fixtures, teams). Users create
and join pools over a single season, predict match scores plus two bonus
predictions (champion and group standings), and compete on a per-pool leaderboard.
Scoring rules are configurable per pool.

Admin uses Filament v5. Players use the Flux (Livewire) frontend.

## Decisions (locked)

- **Pool model:** multiple pools (groups). Users create pools and invite others;
  pools are public or private (join by invite code). Ranking is per pool.
- **Bet types:** exact score per fixture + bonus: **Champion** and **Group
  standings**. No top-scorer (no player modeling needed).
- **Scoring:** configurable per pool (creator sets point values).
- **Auth:** single `User` model with a `role` (Admin/Player). Admin reaches
  Filament; players use Flux. Self-registration on the Flux side. No new auth
  starter kit — hand-rolled Livewire + Flux components using the `Auth` facade.
- **Bet model (approach A):** one prediction per `(user, fixture)`, reused across
  every pool the user belongs to. Pool-independent correctness flags are stored on
  the bet; each pool scores those flags with its own point values.
- **Scope of this work:** backend domain + scoring engine + Filament admin + full
  Flux player UI, delivered in three implementation phases (below).

## Default assumptions (locked unless changed)

- A pool is tied to exactly one `season` (one competition edition).
- Bet lock time = `fixtures.locked_at` if set, else `fixtures.match_date`.
- Bonus predictions (champion, group) lock at kickoff of the season's first fixture.
- Per-pool scoring values are columns on `pools` (finite, known keys — YAGNI over a
  rules table).
- Group bonus = predict 1st and 2nd place of each group.
- Match result is evaluated against regular/full-time score
  (`home_score` / `away_score`).

## Schema

### users (modify)

- Add `role` — string, cast to `App\Enums\User\Role` enum (`Admin` / `Player`),
  default `Player`.
- `canAccessPanel()` returns `role === Role::Admin`.

### pools

| Column | Type | Notes |
|--------|------|-------|
| id | id | |
| name | string | |
| slug | string, unique | URL key |
| description | text, nullable | |
| season_id | FK seasons, cascadeOnDelete | the edition being bet on |
| owner_id | FK users, cascadeOnDelete | creator |
| visibility | string | enum `Public` / `Private` |
| invite_code | string, unique, nullable | private join |
| points_exact | unsignedSmallInteger, default 10 | exact score |
| points_result | unsignedSmallInteger, default 5 | correct outcome, wrong score |
| points_champion | unsignedSmallInteger, default 25 | champion bonus |
| points_group_position | unsignedSmallInteger, default 3 | per correct group slot |
| timestamps | | |

Index: `season_id`, `visibility`.

### pool_user (participants)

| Column | Type | Notes |
|--------|------|-------|
| id | id | |
| pool_id | FK pools, cascadeOnDelete | |
| user_id | FK users, cascadeOnDelete | |
| joined_at | timestamp | |
| timestamps | | |

Unique: `(pool_id, user_id)`.

### bets (score predictions — global per user)

| Column | Type | Notes |
|--------|------|-------|
| id | id | |
| user_id | FK users, cascadeOnDelete | |
| fixture_id | FK fixtures, cascadeOnDelete | |
| home_score | unsignedTinyInteger | |
| away_score | unsignedTinyInteger | |
| is_exact | boolean, nullable | resolved when fixture finishes |
| is_correct_result | boolean, nullable | resolved when fixture finishes |
| resolved_at | timestamp, nullable | |
| timestamps | | |

Unique: `(user_id, fixture_id)`.

### champion_bets (bonus — champion)

| Column | Type | Notes |
|--------|------|-------|
| id | id | |
| user_id | FK users, cascadeOnDelete | |
| season_id | FK seasons, cascadeOnDelete | |
| team_id | FK teams, cascadeOnDelete | predicted champion |
| is_correct | boolean, nullable | resolved when season winner set |
| resolved_at | timestamp, nullable | |
| timestamps | | |

Unique: `(user_id, season_id)`.

### group_bets (bonus — group standings)

| Column | Type | Notes |
|--------|------|-------|
| id | id | |
| user_id | FK users, cascadeOnDelete | |
| season_id | FK seasons, cascadeOnDelete | |
| group_letter | string(1) | |
| team_id | FK teams, cascadeOnDelete | predicted team for the slot |
| predicted_position | unsignedTinyInteger | 1 or 2 |
| is_correct | boolean, nullable | resolved when group positions final |
| resolved_at | timestamp, nullable | |
| timestamps | | |

Unique: `(user_id, season_id, group_letter, predicted_position)`.

## Scoring engine

Correctness flags on a bet are **pool-independent** — they depend only on the
prediction vs the actual result. Points are computed by applying a pool's
configured values to those flags.

Actions (single-purpose, in `app/Actions`):

- **ResolveFixtureBetsAction** — given a finished `Fixture` with scores, set
  `is_exact`, `is_correct_result`, `resolved_at` on every bet for that fixture.
  Idempotent: re-running after a score edit recomputes the flags.
  - `is_exact` = bet score equals result exactly.
  - `is_correct_result` = `sign(home - away)` matches (covers home win / draw / away
    win). True also when `is_exact` is true.
- **ResolveChampionBetsAction** — when `season.winner_id` is set, mark each
  champion bet `is_correct = (team_id === season.winner_id)`.
- **ResolveGroupBetsAction** — when a season's group positions are finalized
  (`season_teams.group_position`), mark each group bet correct when the team sits at
  the predicted position in that group.

Triggers:

- Eloquent observers: `FixtureObserver` (on save, when `status === Finished`,
  scores non-null, and the relevant attributes are dirty) calls
  `ResolveFixtureBetsAction`; `SeasonObserver` calls `ResolveChampionBetsAction`
  when `winner_id` becomes set.
- Manual Filament actions ("Encerrar & pontuar" on a fixture, "Recalcular
  pontuação" on a pool/season) for explicit recompute.

## Leaderboard

`PoolStandings` service computes ranked standings for a pool:

```
points(user) =
    Σ over fixtures in pool.season:
        is_exact ? pool.points_exact
      : is_correct_result ? pool.points_result
      : 0
  + (champion bet correct ? pool.points_champion : 0)
  + (correct group slots) * pool.points_group_position
```

Implemented as a single aggregate query joining `pool_user → bets` (filtered to the
pool's season fixtures) plus the bonus tallies, using the pool's point columns.
Returns participants ordered by points desc. Caching is a later optimization, not in
scope now.

## Bet locking

- A fixture is locked when `locked_at ?? match_date <= now()`
  (`Fixture::isLocked()` already exists).
- Creating or updating a bet is allowed only when the fixture is not locked.
  Enforced in a `BetPolicy` and in the bet write action.
- Bonus predictions (champion, group) lock at kickoff of the season's first fixture;
  enforced the same way.

## Auth & roles

- `users.role` (enum `Role`). Seed at least one Admin.
- `User::canAccessPanel()` → `role === Role::Admin`.
- Flux player area: hand-rolled Livewire components with Flux UI for register /
  login / logout via the `Auth` facade and Laravel's session guard. No new package.
- Throttle auth routes.

## Filament admin (phase 2)

- **PoolResource** — list/create/edit pools, configure point values, participants
  relation manager, view standings (custom page or infolist).
- Bets shown read-only (relation manager / page).
- Fixture action "Encerrar & pontuar" — set scores + status `Finished`, trigger
  `ResolveFixtureBetsAction`.
- "Recalcular pontuação" action on pool/season.

## Flux player UI (phase 3)

- Register / login / logout (Flux forms).
- Dashboard: my pools.
- Create pool (name, season, visibility, scoring config).
- Browse public pools + join by invite code.
- Pool detail: fixtures list with score inputs, save respecting lock.
- Bonus predictions: champion + group standings (before lock).
- Pool leaderboard.

## Components & boundaries

- **Enums:** `App\Enums\User\Role`, `App\Enums\Pool\Visibility`.
- **Models:** `Pool`, `PoolUser` (pivot or model), `Bet`, `ChampionBet`,
  `GroupBet`; relations on `User`, `Season`, `Fixture`, `Team`.
- **Actions:** `ResolveFixtureBetsAction`, `ResolveChampionBetsAction`,
  `ResolveGroupBetsAction`, `JoinPoolAction`, `PlaceBetAction`.
- **Services:** `PoolStandings`.
- **Policies:** `PoolPolicy`, `BetPolicy`.
- **Observers:** `FixtureObserver`, `SeasonObserver`.

Each model/action/service has one purpose and is unit-testable in isolation:
scoring actions take a resolved fixture/season and mutate bet flags; `PoolStandings`
takes a pool and returns ranked data; policies gate writes by lock + membership.

## Testing

- Pest feature/unit tests per phase.
- Scoring: datasets covering exact, correct-result-only, wrong, draws, and bonus
  resolution; idempotency on score edits.
- Lock enforcement: cannot bet after lock; can edit before.
- Leaderboard ordering with mixed results across multiple participants.
- Membership/visibility: join by code, public listing, private hidden.
- Smoke tests for admin pages and Flux pages (no JS errors).

## Implementation phases

1. **Domain + scoring engine + tests** — enums, migrations, models, relations,
   actions, observers, `PoolStandings`, policies, Pest coverage.
2. **Filament admin** — PoolResource, scoring triggers, standings view.
3. **Flux player UI + auth** — registration/login, pool browse/create/join, bet
   entry, bonus predictions, leaderboard.

Each phase can take its own plan; the implementation plan will sequence them.

## Out of scope (future)

- Core betting domain beyond the above (e.g., top-scorer bonus, money/payments).
- Standings caching / denormalized points.
- Notifications, reminders before lock.
