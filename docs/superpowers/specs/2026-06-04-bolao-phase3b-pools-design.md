# Bolão Phase 3b — Player Pools (create / browse / join) Design Spec

**Date:** 2026-06-04
**Status:** Approved
**Depends on:** Phases 1, 2, 3a (merged to `main`).
**Part of:** Phase 3 (Flux player UI). This spec covers **3b only** (3c = bets, 3d = bonus & leaderboard).

## Overview

Let players create pools, browse public pools and join them (public directly, private
by invite code), and view a pool's detail page (info, scoring, participants). Wires the
3a dashboard's placeholder buttons to the new pool screens. Score betting and the
leaderboard are out of scope (3c/3d).

## Existing conventions & pieces (follow / reuse)

- **Class-based Livewire 4** components (`config/livewire.php` `component.type=class`):
  classes in `app/Livewire/`, views in `resources/views/livewire/`. Generate with
  `php artisan make:livewire <Name> --class --no-interaction` (this build auto-creates a
  colocated test; note its path). No Volt, no SFC.
- **Flux UI** (mirror existing `resources/views/livewire/auth/register.blade.php` and
  `resources/views/livewire/dashboard.blade.php`; use the `fluxui-development` skill for
  exact component names/props).
- **Authenticated shell:** `#[Layout('layouts.dashboard')]` (the Flux navbar shell from
  3a). All 3b pages use it.
- **From Phase 1:** `App\Models\Pool` (`name`, `slug` unique, `description`,
  `season_id`, `owner_id`, `visibility` cast `App\Enums\Pool\Visibility`, `invite_code`
  unique nullable, integer `points_exact/points_result/points_champion/points_group_position`;
  relations `season()`, `owner()`, `participants()` via `pool_user` withPivot `joined_at`;
  `isPrivate()`). `App\Actions\Pool\JoinPoolAction::handle(User, Pool, ?string)` (validates
  invite code for private; idempotent). `App\Policies\PoolPolicy` (`before()` grants admins;
  `view` allows public OR owner/participant for private; `update`/`delete` owner-only).
  `App\Enums\Pool\Visibility` (`Public`/`Private`, HasLabel/HasColor). `Season` has a
  `name` accessor (year).
- **Run tests:** `docker exec oddly_php php artisan test --compact` (host PHP 8.3 fails).
  Pint on host. `pest-plugin-livewire` installed.

## Decisions (locked)

- **Create form scoring:** show the 4 point fields prefilled with defaults
  (`exact=10`, `result=5`, `champion=25`, `group_position=3`), editable.
- **Owner auto-joins** as a participant on create (appears in standings, can bet).
- **Pool detail (3b):** name, season, visibility, invite code (visible to owner +
  members only), scoring, participant list. "Palpites"/"Ranking" are inert placeholders
  for 3c/3d.
- **Leave pool:** members (non-owner) can leave. Owner cannot leave (would orphan the
  pool); pool edit/delete/transfer is out of scope.
- **Season selection:** a select of available seasons (only one exists today;
  future-proof).

## Routes (`routes/web.php`, inside the `auth` group)

- `/pools` → `pools.index` (Browse).
- `/pools/create` → `pools.create` (Create).
- `/pools/{pool:slug}` → `pools.show` (Show) — route-model binding by `slug`.
- Update the dashboard empty-state buttons: "Criar bolão" → `route('pools.create')`,
  "Entrar em bolão" → `route('pools.index')`.

## Actions (`app/Actions/Pool/`)

### CreatePoolAction
`handle(User $owner, array $data): Pool`
- Build the pool: `name`, `slug` (from `name` via `Str::slug` + uniqueness suffix),
  `description` (nullable), `season_id`, `visibility`, the four point values, and
  `invite_code` = `Str::upper(Str::random(8))` when private, `null` when public.
  `owner_id = $owner->id`.
- Auto-join the owner: `$pool->participants()->attach($owner->id, ['joined_at' => now()])`.
- Wrapped in a `DB::transaction`. Returns the pool.

### LeavePoolAction
`handle(User $user, Pool $pool): void`
- If the user is the owner, throw (owners can't leave) — or no-op with a guard; throw a
  `RuntimeException` for clarity.
- Otherwise `$pool->participants()->detach($user->id)`.

### JoinPoolAction (reuse, unchanged)
Public: no code needed. Private: requires the matching `invite_code`.

## Components (class-based Livewire, `#[Layout('layouts.dashboard')]`)

### `App\Livewire\Pools\Browse` — `/pools`
- Lists pools with `visibility = Public` (name, season year, participants count); each has
  a "Entrar" button → `JoinPoolAction` then redirect to that pool's `pools.show`.
- A "Entrar por código" input + button → `JoinPoolAction($user, $poolFoundByCode, $code)`
  for private pools. Resolve the pool by `invite_code`; on success redirect to its show; on
  bad code show a validation error.
- Does NOT list private pools.

### `App\Livewire\Pools\Create` — `/pools/create`
- Form fields: `name` (`required|string|max:255`), `description` (nullable),
  `season_id` (`required|exists:seasons,id`), `visibility` (enum, default Private),
  `points_exact`/`points_result`/`points_champion`/`points_group_position`
  (`required|integer|min:0`, defaults 10/5/25/3).
- `create()`: validate, call `CreatePoolAction::handle(auth()->user(), $validated)`,
  redirect to `pools.show` for the new pool.
- Season select uses `Season::all()` with the year label.

### `App\Livewire\Pools\Show` — `/pools/{pool:slug}`
- Mount authorizes `view` via `PoolPolicy` (`$this->authorize('view', $pool)`); a stranger
  hitting a private pool gets 403.
- Displays: name, season, visibility badge, scoring values, participant list
  (name + joined_at). The **invite code** is shown only when the viewer is the owner or a
  participant.
- A "Sair do bolão" button for non-owner members → `LeavePoolAction`, then redirect to
  `dashboard`. Owners do not see this button.
- Inert "Palpites" / "Ranking" placeholders (links to `#`) for 3c/3d.

## Authorization

- Browse lists only public pools (query constraint), so no per-row policy needed there.
- Show calls `$this->authorize('view', $pool)`. `PoolPolicy::view` already returns true for
  public pools and for owner/participant of private pools; admins pass via `before()`.
- Joining a private pool requires the correct `invite_code` (enforced by `JoinPoolAction`).

## Testing (Pest + Livewire)

**CreatePoolAction / Create component:**
- validates required `name` and `season_id`.
- creates a pool with a unique slug; private pool gets an `invite_code`, public gets `null`.
- the owner is attached as a participant.
- the component redirects to `pools.show` after creating.
- custom point values persist; defaults apply when unchanged.

**Browse component:**
- lists public pools, not private ones.
- joining a public pool adds the user as participant and redirects to its show page.
- joining by a correct invite code joins a private pool; a wrong code shows an error and
  does not join.

**Show component:**
- owner and participant can view a private pool; a stranger gets 403.
- any authenticated user can view a public pool.
- the invite code is visible to owner/members and hidden from non-members (public pool
  viewed by a non-member).
- a non-owner member sees "Sair do bolão" and leaving detaches them; the owner does not
  see it.

**Dashboard (update existing test):**
- the empty-state buttons link to `route('pools.create')` and `route('pools.index')`.

**LeavePoolAction:**
- detaches a member; throws for the owner.

## File structure (new / changed)

```
routes/web.php                                       (edit: add pools routes; dashboard button hrefs)
app/Actions/Pool/CreatePoolAction.php                (new)
app/Actions/Pool/LeavePoolAction.php                 (new)
app/Livewire/Pools/Browse.php                        (new)
resources/views/livewire/pools/browse.blade.php      (new)
app/Livewire/Pools/Create.php                        (new)
resources/views/livewire/pools/create.blade.php      (new)
app/Livewire/Pools/Show.php                          (new)
resources/views/livewire/pools/show.blade.php        (new)
resources/views/livewire/dashboard.blade.php         (edit: button hrefs)
<generated colocated tests for the three components>
tests/Feature/Pools/CreatePoolActionTest.php         (new)
tests/Feature/Pools/LeavePoolActionTest.php          (new)
```

## Out of scope (later)

- 3c: score-bet entry on the pool's fixtures (lock-aware, `PlaceBetAction`).
- 3d: champion/group bonus predictions, pool leaderboard.
- Pool edit/delete/owner-transfer by the player; pagination/search on browse.
