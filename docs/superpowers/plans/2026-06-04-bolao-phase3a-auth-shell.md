# Bolão Phase 3a — Player Auth & Shell Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let players register, log in/out, and land on an authenticated dashboard that lists the pools they own or joined — establishing the player-facing Flux shell.

**Architecture:** Class-based Livewire 4 components (`app/Livewire`, views in `resources/views/livewire`) per `config/livewire.php` (`component.type=class`). A shared authenticated Blade layout (`layouts/dashboard`) with a Flux navbar hosts the dashboard. The existing login SFC stays an SFC (only its redirect changes). No Volt, no SFC for new components.

**Tech Stack:** Laravel 12, PHP 8.4, Livewire v4 (class components), Flux + Flux Pro, Pest 4 (+ pest-plugin-livewire). Run artisan/tests via `docker exec oddly_php php artisan ...` (host PHP 8.3 fails). Pint on host.

---

## Conventions & notes

- **Branch `feature/bolao-phase3a` is checked out.** Do not branch/switch.
- **New Livewire components are class-based:** generate with `docker exec oddly_php php artisan make:livewire <Name> --class --test --no-interaction`. Class → `app/Livewire/...`, view → `resources/views/livewire/...`. Then replace generated stubs with the code in each task.
- **Flux component syntax:** when writing Flux markup (navbar, dropdown, inputs), if unsure of exact v2 component names/props, invoke the `fluxui-development` skill and/or mirror the existing `resources/views/components/auth/⚡login.blade.php` (which uses `flux:input`, `flux:button`, `flux:field`, `flux:checkbox`, `flux:link`, `flux:heading`).
- **Routing:** follow the existing `Route::livewire('/path', 'dot.name')` style. If the macro can't resolve a class component by dot-name, route to the class: `Route::get('/path', \App\Livewire\...::class)`.
- **Tests:** class-component tests use `Livewire::test(\App\Livewire\...::class)`. `make:livewire --test` creates a test file — replace its body with the task's test. The existing login keeps its colocated `⚡login.test.php`.
- After editing PHP: `vendor/bin/pint --dirty --format agent`.
- `User`: `role` defaults to `Role::Player`, `password` hashed cast, `$fillable = ['name','email','password']` (note: `role` is NOT fillable, but `Model::unguard()` is global so mass assignment of any column works; still, set role via default). `UserFactory` has `admin()` state.

---

## Task 1: Remove dead `/seed` route and broken League import

**Files:**
- Modify: `routes/web.php`
- Test: `tests/Feature/HomeTest.php` (new)

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('renders the public home page', function (): void {
    get('/')->assertOk();
});
```

- [ ] **Step 2: Run test to verify current state**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/HomeTest.php`
Expected: PASS (home already works) — this test guards against breaking `/` during cleanup. If it fails, investigate before continuing.

- [ ] **Step 3: Remove the dead route and import**

In `routes/web.php`: delete the `use App\Models\League;` line and the entire `Route::get('/seed', function () { ... });` block. Leave the `static.home` route and the `guest` group intact.

- [ ] **Step 4: Run test + full suite**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/HomeTest.php && docker exec oddly_php php artisan test --compact`
Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add routes/web.php tests/Feature/HomeTest.php
git commit -m "chore: remove dead seed route and broken League import"
```

---

## Task 2: Logout route

**Files:**
- Modify: `routes/web.php`
- Test: `tests/Feature/Auth/LogoutTest.php` (new)

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

it('logs out an authenticated user and redirects home', function (): void {
    actingAs(User::factory()->create());

    post('/logout')
        ->assertRedirect(route('static.home'));

    $this->assertGuest();
});

it('does not allow guests to logout route without auth', function (): void {
    post('/logout')->assertRedirect(route('login'));
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Auth/LogoutTest.php`
Expected: FAIL — `/logout` route not defined.

- [ ] **Step 3: Add the logout route**

In `routes/web.php`, add `use Illuminate\Http\Request;` and `use Illuminate\Support\Facades\Auth;` at the top, then:

```php
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('static.home');
})->middleware('auth')->name('logout');
```

- [ ] **Step 4: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Auth/LogoutTest.php`
Expected: PASS (2 passed). (The guest case redirects to `login` via the `auth` middleware.)

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add routes/web.php tests/Feature/Auth/LogoutTest.php
git commit -m "feat: add logout route"
```

---

## Task 3: Register (class Livewire component)

**Files:**
- Create: `app/Livewire/Auth/Register.php`
- Create: `resources/views/livewire/auth/register.blade.php`
- Modify: `routes/web.php`
- Test: generated by `make:livewire` (replace body)

- [ ] **Step 1: Scaffold the component**

Run: `docker exec oddly_php php artisan make:livewire Auth/Register --class --test --no-interaction`
This creates `app/Livewire/Auth/Register.php`, `resources/views/livewire/auth/register.blade.php`, and a test file. Note the generated test path from the command output.

- [ ] **Step 2: Write the failing test (replace the generated test body)**

Put this in the generated Register test file:

```php
<?php

declare(strict_types=1);

use App\Enums\User\Role;
use App\Livewire\Auth\Register;
use App\Models\User;

use function Pest\Laravel\get;

it('renders the register page', function (): void {
    get('/signup')->assertOk()->assertSeeLivewire(Register::class);
});

it('requires name, email and password', function (): void {
    Livewire::test(Register::class)
        ->set('name', '')
        ->set('email', '')
        ->set('password', '')
        ->call('register')
        ->assertHasErrors([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
        ]);
});

it('validates email format and uniqueness', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::test(Register::class)
        ->set('name', 'Zé')
        ->set('email', 'not-an-email')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('register')
        ->assertHasErrors(['email' => 'email']);

    Livewire::test(Register::class)
        ->set('name', 'Zé')
        ->set('email', 'taken@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('register')
        ->assertHasErrors(['email' => 'unique']);
});

it('requires a matching, min-8 password confirmation', function (): void {
    Livewire::test(Register::class)
        ->set('name', 'Zé')
        ->set('email', 'ze@example.com')
        ->set('password', 'short')
        ->set('password_confirmation', 'mismatch')
        ->call('register')
        ->assertHasErrors(['password']);
});

it('registers, logs in as a player, and redirects to dashboard', function (): void {
    Livewire::test(Register::class)
        ->set('name', 'Zé Palpiteiro')
        ->set('email', 'ze@example.com')
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $user = User::where('email', 'ze@example.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->role)->toBe(Role::Player);
    $this->assertAuthenticatedAs($user);
});
```

Note: the `dashboard` route is added in Task 5. To keep this task self-contained, temporarily add the dashboard route in Step 4 below; Task 5 builds the actual page. (Alternatively, run this test after Task 5 — but the plan adds the route now so the redirect resolves.)

- [ ] **Step 3: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact <generated Register test path>`
Expected: FAIL — component logic/route missing.

- [ ] **Step 4: Implement the component class**

`app/Livewire/Auth/Register.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
final class Register extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required|email|unique:users,email')]
    public string $email = '';

    #[Validate('required|string|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    public function register(): void
    {
        $data = $this->validate();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        Auth::login($user);
        session()->regenerate();

        $this->redirectRoute('dashboard');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.auth.register');
    }
}
```

- [ ] **Step 5: Implement the view**

`resources/views/livewire/auth/register.blade.php` — mirror the login layout. (Verify Flux component names via the existing `⚡login.blade.php` / the `fluxui-development` skill.)

```blade
<div class="flex min-h-screen">
    <div class="flex items-center justify-center flex-1">
        <div class="max-w-80 w-80 mx-auto flex-1">
            <div class="text-center mb-6">
                <h1 class="text-2xl md:text-4xl font-extrabold text-secondary-500">{{ config('app.name') }}</h1>
            </div>

            <flux:heading class="text-center" size="xl">Criar conta</flux:heading>

            <form wire:submit="register">
                <div class="space-y-6">
                    <flux:input label="Nome" placeholder="Seu nome" required wire:model="name" />
                    <flux:input label="E-mail" placeholder="seu.email@exemplo.com" required wire:model="email" />
                    <flux:input label="Senha" type="password" viewable required wire:model="password" />
                    <flux:input label="Confirme a senha" type="password" viewable required wire:model="password_confirmation" />

                    <flux:button type="submit" variant="primary" color="cyan" class="min-w-full">Criar conta</flux:button>

                    <flux:text class="text-center">
                        Já tem conta?
                        <flux:link :href="route('login')">Entrar</flux:link>
                    </flux:text>
                </div>
            </form>
        </div>
    </div>
    <div class="flex-1 overflow-hidden rounded-lg p-16 max-lg:hidden">
        <img src="{{ asset('images/hero.webp') }}" alt="Oddly" class="object-cover w-full h-full rounded-lg" draggable="false" />
    </div>
</div>
```

- [ ] **Step 6: Register the route**

In `routes/web.php`, inside the existing `guest` middleware group (next to `/signin`):

```php
Route::livewire('/signup', 'auth.register')->name('register');
```

If `Route::livewire` can't resolve the class component by dot-name, use:
```php
Route::get('/signup', \App\Livewire\Auth\Register::class)->name('register');
```

Also add the dashboard route now so the redirect resolves (Task 5 builds the page). Inside an `auth` group:
```php
Route::livewire('/dashboard', 'dashboard')->name('dashboard');
```
(If `dashboard` component doesn't exist yet, this route will 500 only when visited — the Register test only checks the redirect target, not the dashboard render. That's fine. Task 5 implements it.)

- [ ] **Step 7: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact <generated Register test path>`
Expected: PASS (5 passed).

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Auth/Register.php resources/views/livewire/auth/register.blade.php routes/web.php <generated Register test path>
git commit -m "feat: add player registration"
```

---

## Task 4: Point login at the dashboard

**Files:**
- Modify: `resources/views/components/auth/⚡login.blade.php`
- Modify: `resources/views/components/auth/⚡login.test.php`
- Modify: `bootstrap/app.php` (guest redirect target)

- [ ] **Step 1: Update the login test expectations**

In `⚡login.test.php`, change the two assertions that reference `route('static.home')` to `route('dashboard')`:
- the "redirect authenticated users" test: `->assertRedirect(uri: route('dashboard'))`.
- the "login successfully with correct credentials" test: `->assertRedirect(uri: route('dashboard'))`.

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact "resources/views/components/auth/⚡login.test.php"`
Expected: FAIL — login still redirects to `static.home`, and the guest middleware may still send authed users elsewhere.

- [ ] **Step 3: Change the login redirect**

In `⚡login.blade.php`, change `$this->redirectRoute('static.home');` to `$this->redirectRoute('dashboard');`.

- [ ] **Step 4: Make the guest middleware redirect authenticated users to the dashboard**

In `bootstrap/app.php`, inside `->withMiddleware(function (Middleware $middleware) { ... })`, add:

```php
$middleware->redirectUsersTo(fn () => route('dashboard'));
```

(This sets where `RedirectIfAuthenticated` / the `guest` middleware sends already-authenticated users. Import for the closure isn't needed. If `redirectUsersTo` isn't available in this Laravel build, instead set the redirect in the guest route group or confirm the default already targets `/dashboard`.)

- [ ] **Step 5: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact "resources/views/components/auth/⚡login.test.php"`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add "resources/views/components/auth/⚡login.blade.php" "resources/views/components/auth/⚡login.test.php" bootstrap/app.php
git commit -m "feat: redirect login and authenticated guests to dashboard"
```

---

## Task 5: Authenticated shell + dashboard

**Files:**
- Create: `resources/views/layouts/dashboard.blade.php`
- Create: `app/Livewire/Dashboard.php`
- Create: `resources/views/livewire/dashboard.blade.php`
- Test: generated by `make:livewire` (replace body)
- Note: the `/dashboard` route was added in Task 3 Step 6.

- [ ] **Step 1: Scaffold the component**

Run: `docker exec oddly_php php artisan make:livewire Dashboard --class --test --no-interaction`
Note the generated test path.

- [ ] **Step 2: Write the failing test (replace generated body)**

```php
<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Livewire\Dashboard;
use App\Models\Pool;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('redirects guests to login', function (): void {
    get('/dashboard')->assertRedirect(route('login'));
});

it('shows the pools the user owns and joined, not others', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $owned = Pool::factory()->create(['owner_id' => $user->id, 'name' => 'Meu Bolão']);
    $joinedPool = Pool::factory()->public()->create(['name' => 'Bolão Aberto']);
    app(JoinPoolAction::class)->handle($user, $joinedPool);
    $other = Pool::factory()->public()->create(['name' => 'Bolão Alheio']);

    Livewire::test(Dashboard::class)
        ->assertOk()
        ->assertSee('Meu Bolão')
        ->assertSee('Bolão Aberto')
        ->assertDontSee('Bolão Alheio');
});

it('shows an empty state when the user has no pools', function (): void {
    actingAs(User::factory()->create());

    Livewire::test(Dashboard::class)
        ->assertSee('Criar bolão')
        ->assertSee('Entrar em bolão');
});
```

- [ ] **Step 3: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact <generated Dashboard test path>`
Expected: FAIL — component/layout missing.

- [ ] **Step 4: Implement the authenticated layout**

`resources/views/layouts/dashboard.blade.php` — verify Flux navbar/dropdown component names via the `fluxui-development` skill; this is a reasonable starting shape:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<x-partials.head :title="$title ?? null" />

<x-partials.body>
    <flux:header container class="border-b border-zinc-200 dark:border-zinc-700">
        <flux:brand :href="route('dashboard')" name="{{ config('app.name') }}" />

        <flux:navbar class="me-auto ms-4">
            <flux:navbar.item :href="route('dashboard')">Dashboard</flux:navbar.item>
        </flux:navbar>

        <flux:spacer />

        <flux:dropdown position="bottom" align="end">
            <flux:button variant="ghost">{{ auth()->user()->name }}</flux:button>

            <flux:menu>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">
                        Sair
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <flux:main container>
        {{ $slot }}
    </flux:main>
</x-partials.body>

</html>
```

If a specific Flux component/prop above doesn't exist in the installed version, substitute the correct one (use the `fluxui-development` skill) — the required behavior is: brand → dashboard, a Dashboard nav link, and a user dropdown containing a POST-logout form.

- [ ] **Step 5: Implement the Dashboard component**

`app/Livewire/Dashboard.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Pool;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Dashboard extends Component
{
    /**
     * @return Collection<int, Pool>
     */
    public function pools(): Collection
    {
        $userId = auth()->id();

        return Pool::query()
            ->where('owner_id', $userId)
            ->orWhereHas('participants', fn ($query) => $query->whereKey($userId))
            ->withCount('participants')
            ->with('season')
            ->get();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard', ['pools' => $this->pools()]);
    }
}
```

- [ ] **Step 6: Implement the dashboard view**

`resources/views/livewire/dashboard.blade.php`:

```blade
<div>
    <flux:heading size="xl" class="mb-6">Meus bolões</flux:heading>

    @forelse ($pools as $pool)
        <flux:card class="mb-4">
            <flux:heading size="lg">{{ $pool->name }}</flux:heading>
            <flux:text>Temporada {{ $pool->season->name }}</flux:text>
            <flux:text>{{ $pool->participants_count }} participante(s)</flux:text>
        </flux:card>
    @empty
        <flux:card class="text-center space-y-4">
            <flux:heading size="lg">Você ainda não está em nenhum bolão</flux:heading>
            <div class="flex gap-3 justify-center">
                <flux:button href="#" variant="primary" color="cyan">Criar bolão</flux:button>
                <flux:button href="#" variant="ghost">Entrar em bolão</flux:button>
            </div>
        </flux:card>
    @endforelse
</div>
```

(If `flux:card` isn't available in the installed Flux, use a plain styled `<div>` — keep the visible texts "Criar bolão" / "Entrar em bolão" and the pool name/season/count so the tests pass.)

- [ ] **Step 7: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact <generated Dashboard test path>`
Expected: PASS (3 passed).

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Dashboard.php resources/views/livewire/dashboard.blade.php resources/views/layouts/dashboard.blade.php <generated Dashboard test path>
git commit -m "feat: add authenticated shell and player dashboard"
```

---

## Task 6: Full suite green + manual smoke

**Files:** none (verification)

- [ ] **Step 1: Full suite**

Run: `docker exec oddly_php php artisan test --compact`
Expected: all PASS (Phases 1–2 + 3a). Confirm the existing login SFC tests pass with the dashboard redirect.

- [ ] **Step 2: Build assets if needed for a manual check (optional)**

If you manually browse, run `npm run build` (or ask the user to run `npm run dev`). Not required for tests.

- [ ] **Step 3: Confirm formatting**

Run: `vendor/bin/pint --dirty --format agent`
Expected: clean (or it fixes; commit).

- [ ] **Step 4: Final commit if needed**

```bash
git add -A
git commit -m "chore: phase 3a green" || echo "nothing to commit"
```

---

## Self-Review (completed during authoring)

- **Spec coverage:** dead-code cleanup (T1); logout route (T2); register class component + route + redirect-to-dashboard + Player role (T3); login redirect to dashboard + guest redirect (T4); authenticated Flux shell + dashboard listing owned∪joined pools + empty state (T5); full green (T6). Password reset stays inert (out of scope) — correctly absent.
- **Class-based components:** Register and Dashboard generated via `make:livewire --class`; login stays SFC (redirect-only change) as the spec mandates. No Volt, no new SFCs.
- **Type/name consistency:** routes named `register`/`dashboard`/`logout`; components `App\Livewire\Auth\Register`, `App\Livewire\Dashboard`; layout `layouts.dashboard`; dashboard exposes `pools()`. Tests reference these exactly.
- **Placeholder scan:** no TBD/TODO; full code in every step. Flux/framework-API uncertainties point the implementer at the `fluxui-development` skill and the existing login component, with a behavioral fallback specified each time.
- **Risk:** exact Flux navbar/dropdown/card component names may differ in the installed build; each such step states the required behavior and a fallback so tests (which assert visible text/behavior, not Flux internals) stay valid. The `redirectUsersTo` config call may vary by Laravel build; fallback noted.
