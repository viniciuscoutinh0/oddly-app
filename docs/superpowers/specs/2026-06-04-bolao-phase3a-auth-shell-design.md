# Bolão Phase 3a — Player Auth & Shell Design Spec

**Date:** 2026-06-04
**Status:** Approved
**Depends on:** Phases 1 & 2 (merged to `main`).
**Part of:** Phase 3 (Flux player UI), decomposed into 3a (auth & shell), 3b (pools), 3c (bets), 3d (bonus & leaderboard). This spec covers **3a only**.

## Overview

Give players a way to register, log in/out, and land on an authenticated dashboard
that lists the pools they own or participate in. This establishes the player-facing
shell (authenticated layout + navigation) that subsequent sub-phases build on. Pool
creation/joining and the pool screens are out of scope (3b).

## Existing conventions (follow exactly)

- **Livewire 4 single-file components (SFC):** PHP class + Blade in one file at
  `resources/views/components/<path>/⚡<name>.blade.php`, registered with
  `Route::livewire('/path', 'dot.name')`. Example present: `auth.login` at
  `resources/views/components/auth/⚡login.blade.php`, route `/signin` named `login`.
- **Flux UI** components (`flux:input`, `flux:button`, `flux:field`, `flux:checkbox`,
  etc.). Flux + Flux Pro are installed.
- **Colocated Pest tests:** `⚡<name>.test.php` beside the SFC, using
  `Livewire::test('dot.name')`.
- Layout `resources/views/layouts/app.blade.php` with `<x-partials.head>` /
  `<x-partials.body>`. App name via `config('app.name')`.
- `User` model: `role` cast to `App\Enums\User\Role` (default `Player`), `password`
  cast `hashed`. `UserFactory` has an `admin()` state. `Pool` has `owner()` and
  `participants()` (belongsToMany via `pool_user`).

## Decisions (locked)

- **No email verification** — register → auto-login → dashboard.
- **Registration fields:** `name`, `email`, `password`, `password_confirmation`.
  `name` is the display name used in rankings.
- **Dashboard (3a):** lists the user's pools (owned ∪ participating) with an
  empty state. Create/Join buttons are present but inert (link to `#`) until 3b.
- **Shell:** a shared authenticated layout with a Flux navbar (app name, Dashboard
  link, user dropdown with Logout). Guest landing `/` stays.
- **Password reset:** the existing "Esqueceu a senha?" link stays inert (deferred).

## Routes (`routes/web.php`)

- **Remove dead code:** the `/seed` debug route and `use App\Models\League;`
  (the `League` model does not exist — leftover that breaks if hit).
- **guest middleware group:**
  - `/signin` → `auth.login` (exists, keep).
  - `/signup` → `auth.register`, named `register` (new).
- **auth middleware group:**
  - `/dashboard` → `dashboard`, named `dashboard` (new).
- **logout:** `Route::post('/logout', ...)->name('logout')->middleware('auth')` — calls
  `Auth::logout()`, `session()->invalidate()`, `session()->regenerateToken()`, redirects
  to `static.home`.

## Components

### Register — `resources/views/components/auth/⚡register.blade.php`
Livewire SFC mirroring the login component's structure and visual layout (centered
form + hero image on large screens).

- Public properties with `#[Validate]`: `name` (`required|string|max:255`), `email`
  (`required|email|unique:users,email`), `password`
  (`required|string|min:8|confirmed`), `password_confirmation`.
- `register()` method: `$this->validate()`, create the user
  (`User::create([...])` — `role` defaults to `Player`; `password` is hashed by the
  model cast), `Auth::login($user)`, `session()->regenerate()`,
  `$this->redirectRoute('dashboard')`.
- Flux form: name, email, password (viewable), password confirmation, submit. Link to
  `/signin` ("Já tem conta? Entrar").

### Login — update `resources/views/components/auth/⚡login.blade.php`
- Change the post-login redirect from `static.home` to `dashboard`.
- Update the colocated test's redirect assertions accordingly.

### Authenticated shell — `resources/views/components/layouts/⚡app-shell.blade.php` (or a Blade layout component)
A layout used by authenticated pages. Flux navbar:
- Brand: `config('app.name')` linking to `dashboard`.
- Nav item: "Início"/"Dashboard" → `dashboard`.
- User dropdown (`flux:dropdown`): shows `auth()->user()->name`; a Logout item
  rendered as a `<form method="POST" action="{{ route('logout') }}">@csrf` with a
  submit styled as a Flux button/menu item.

Implementation note: the exact wrapper mechanism (Blade layout component vs. a
`@extends`-style layout vs. Flux's app-shell components) should follow what the
project's Livewire 4 SFC pages can use cleanly. The dashboard SFC renders inside this
shell. If a shared layout component is awkward with SFC routing, an acceptable
alternative is a Blade layout (`resources/views/layouts/dashboard.blade.php`) that the
SFC view markup wraps with `<x-layouts.dashboard>`. Pick the one that fits the SFC
rendering model; keep the navbar markup in one place.

### Dashboard — `resources/views/components/⚡dashboard.blade.php`
Livewire SFC, rendered within the authenticated shell.

- Computed/loaded data: the current user's pools = owned ∪ participating. Implement as
  a single query, e.g.
  `Pool::query()->where('owner_id', $userId)->orWhereHas('participants', fn ($q) => $q->whereKey($userId))->withCount('participants')->with('season')->get()`.
- View: a grid of cards (pool name, season year via `season->name`, participant
  count). Empty state when none: a message + "Criar bolão" and "Entrar em bolão"
  buttons linking to `#` (inert until 3b).

## Authorization & flow

- `/dashboard` and `/logout` require `auth`. Guests hitting `/dashboard` are redirected
  to `login` (Laravel's default unauthenticated redirect → ensure the `login` named
  route resolves, which it does).
- `/signin` and `/signup` require `guest` (authenticated users are bounced away by the
  `guest` middleware).
- Login/register set `role = Player` implicitly (factory/model default); admins still
  reach Filament via their role.

## Testing (Pest + Livewire, colocated `⚡*.test.php` beside each SFC)

**Register** (`components/auth/⚡register.test.php`):
- renders successfully.
- requires `name`, `email`, `password`.
- validates email format and `unique` email.
- requires password confirmation match (`confirmed`) and `min:8`.
- successful registration creates a `User` with `role = Role::Player`, authenticates,
  and redirects to `dashboard`.

**Login** (update `components/auth/⚡login.test.php`):
- successful login now redirects to `dashboard` (update the two redirect assertions).

**Dashboard** (`components/⚡dashboard.test.php`):
- a guest visiting `/dashboard` is redirected to `login`.
- an authenticated user sees their owned and joined pools and does NOT see pools they
  neither own nor joined.
- empty state renders when the user has no pools.

**Logout** (a feature test, e.g. `tests/Feature/Auth/LogoutTest.php`):
- an authenticated POST to `/logout` logs the user out (`assertGuest`) and redirects to
  `static.home`.

All tests run inside Docker: `docker exec oddly_php php artisan test --compact`.

## File structure (new / changed)

```
routes/web.php                                            (edit: remove /seed + League; add routes)
resources/views/components/auth/⚡register.blade.php       (new SFC)
resources/views/components/auth/⚡register.test.php        (new)
resources/views/components/auth/⚡login.blade.php          (edit: redirect → dashboard)
resources/views/components/auth/⚡login.test.php           (edit: redirect assertions)
resources/views/components/⚡dashboard.blade.php           (new SFC)
resources/views/components/⚡dashboard.test.php            (new)
resources/views/components/layouts/⚡app-shell.blade.php   (new shell; or layouts/dashboard.blade.php)
app/Http/Controllers/Auth/LogoutController.php            (or a route closure) (new)
tests/Feature/Auth/LogoutTest.php                         (new)
```

## Out of scope (later sub-phases)

- 3b: browse public pools, create pool, join by invite code, pool detail screens.
- 3c: score-bet entry on fixtures (lock-aware, `PlaceBetAction`).
- 3d: champion/group bonus predictions, pool leaderboard view.
- Password reset / email verification.
