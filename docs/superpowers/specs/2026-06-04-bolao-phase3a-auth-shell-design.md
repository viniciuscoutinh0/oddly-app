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

- **Livewire component format = `class`** (per `config/livewire.php`:
  `component.type = 'class'`). New components MUST be class-based, generated with
  `php artisan make:livewire <Name> --class --test`. Do **NOT** use Volt and do **NOT**
  use the single-file (`⚡`/SFC) format for new components.
  - Component classes live in `app/Livewire/` (`class_namespace = App\Livewire`).
  - Component views live in `resources/views/livewire/` (`view_path`).
  - The existing `auth.login` is an older SFC (`resources/views/components/auth/⚡login.blade.php`).
    Leave it as an SFC — only change its post-login redirect. Do not convert it.
- **Routing:** keep the existing `Route::livewire('/path', 'dot.name')` style; the
  dot-name resolves a class component via `class_namespace` (e.g. `'auth.register'` →
  `App\Livewire\Auth\Register`). If the macro cannot resolve a class component by
  dot-name in this build, fall back to routing to the class:
  `Route::get('/signup', App\Livewire\Auth\Register::class)`.
- **Flux UI** components (`flux:input`, `flux:button`, `flux:field`, `flux:checkbox`,
  `flux:navbar`, `flux:dropdown`, etc.). Flux + Flux Pro are installed.
- **Tests:** `make:livewire --test` generates the component test (location per config —
  likely `tests/Feature/...`). Use `Livewire::test(ComponentClass::class)` for
  class components. The existing login SFC keeps its colocated `⚡login.test.php`.
- Layouts: `resources/views/layouts/app.blade.php` (bare, `<x-partials.head>` /
  `<x-partials.body>`). Class components pick their layout via the `#[Layout(...)]`
  attribute or the `component_layout` config default (`layouts::app`). App name via
  `config('app.name')`.
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

### Register — class component `App\Livewire\Auth\Register` (`make:livewire Auth/Register --class --test`)
Class in `app/Livewire/Auth/Register.php`, view `resources/views/livewire/auth/register.blade.php`,
mirroring the login component's visual layout (centered form + hero image on large
screens). Uses `#[Layout('layouts.app')]` (guest layout) or the default.

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

### Authenticated shell — `resources/views/layouts/dashboard.blade.php`
A Blade layout used by authenticated pages, selected by the Dashboard component via
`#[Layout('layouts.dashboard')]`. Reuses `<x-partials.head>` / `<x-partials.body>` and
adds a Flux navbar:
- Brand: `config('app.name')` linking to `dashboard`.
- Nav item: "Dashboard" → `dashboard`.
- User dropdown (`flux:dropdown`): shows `auth()->user()->name`; a Logout item
  rendered as a `<form method="POST" action="{{ route('logout') }}">@csrf` with a
  submit styled as a Flux button/menu item.

Keep the navbar markup in this one layout file so all authenticated pages share it.

### Dashboard — class component `App\Livewire\Dashboard` (`make:livewire Dashboard --class --test`)
Class in `app/Livewire/Dashboard.php`, view `resources/views/livewire/dashboard.blade.php`,
declares `#[Layout('layouts.dashboard')]` to render within the authenticated shell.

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

## Testing (Pest + Livewire). Class-component tests use `Livewire::test(ComponentClass::class)`.

**Register** (test generated by `make:livewire Auth/Register --test`, at its generated path):
- renders successfully.
- requires `name`, `email`, `password`.
- validates email format and `unique` email.
- requires password confirmation match (`confirmed`) and `min:8`.
- successful registration creates a `User` with `role = Role::Player`, authenticates,
  and redirects to `dashboard`.

**Login** (update the existing colocated `components/auth/⚡login.test.php`):
- successful login now redirects to `dashboard` (update the two redirect assertions).

**Dashboard** (test generated by `make:livewire Dashboard --test`):
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
routes/web.php                                          (edit: remove /seed + League; add routes)
app/Livewire/Auth/Register.php                          (new class component)
resources/views/livewire/auth/register.blade.php        (new view)
app/Livewire/Dashboard.php                              (new class component, #[Layout('layouts.dashboard')])
resources/views/livewire/dashboard.blade.php            (new view)
resources/views/layouts/dashboard.blade.php             (new authenticated shell w/ Flux navbar)
resources/views/components/auth/⚡login.blade.php        (edit: redirect → dashboard)
resources/views/components/auth/⚡login.test.php         (edit: redirect assertions)
app/Http/Controllers/Auth/LogoutController.php          (new; or a route closure)
<generated test paths for Register and Dashboard via make:livewire --test>
tests/Feature/Auth/LogoutTest.php                       (new)
```

## Out of scope (later sub-phases)

- 3b: browse public pools, create pool, join by invite code, pool detail screens.
- 3c: score-bet entry on fixtures (lock-aware, `PlaceBetAction`).
- 3d: champion/group bonus predictions, pool leaderboard view.
- Password reset / email verification.
