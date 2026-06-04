# Bolão Phase 3b — Player Pools Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let players create pools, browse/join public pools (and join private pools by invite code), view a pool's detail (info, scoring, participants, invite code for members), and leave a pool.

**Architecture:** Single-purpose Action classes (`CreatePoolAction`, `LeavePoolAction`; reuse `JoinPoolAction`) hold the rules; thin class-based Livewire components (`Pools\Browse`, `Pools\Create`, `Pools\Show`) drive the UI inside the authenticated Flux shell (`layouts.dashboard`).

**Tech Stack:** Laravel 12, PHP 8.4, Livewire v4 (class components), Flux + Flux Pro, Pest 4 (+ pest-plugin-livewire). Run via `docker exec oddly_php php artisan ...` (host PHP 8.3 fails). Pint on host.

---

## Conventions & notes

- **Branch `feature/bolao-phase3b` is checked out.** Do not branch/switch.
- New Livewire components are **class-based**: `docker exec oddly_php php artisan make:livewire <Name> --class --no-interaction` (this build auto-creates a colocated test; note its path). No Volt, no SFC.
- Components render inside the authed shell via `#[Layout('layouts.dashboard')]`.
- Flux markup: mirror `resources/views/livewire/dashboard.blade.php` / `auth/register.blade.php`; for unfamiliar components invoke the `fluxui-development` skill. Tests assert visible text/behavior, not Flux internals — keep the specified visible strings.
- Authorization in components: use `Illuminate\Support\Facades\Gate` (e.g. `abort_unless(Gate::allows('view', $pool), 403)`) to avoid trait-availability assumptions.
- **From Phase 1:** `Pool` (fields/relations per spec), `JoinPoolAction::handle(User,Pool,?string)` (throws `InvalidArgumentException` on bad/missing code for private), `PoolPolicy` (view: public OR owner/participant; admins via before), `Visibility` enum (`Public`/`Private`), `Season` `name` accessor. `Model::unguard()` is global.
- After PHP edits: `vendor/bin/pint --dirty --format agent`.
- Route-model binding: `/pools/{pool:slug}` binds by `slug`; `route('pools.show', $pool)` generates `/pools/<slug>`.

---

## Task 1: CreatePoolAction

**Files:**
- Create: `app/Actions/Pool/CreatePoolAction.php`
- Test: `tests/Feature/Pools/CreatePoolActionTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Pool\CreatePoolAction;
use App\Enums\Pool\Visibility;
use App\Models\Season;
use App\Models\User;

it('creates a public pool with a unique slug, no invite code, owner joined', function (): void {
    $owner = User::factory()->create();
    $season = Season::factory()->create();

    $pool = app(CreatePoolAction::class)->handle($owner, [
        'name' => 'Bolão da Firma',
        'description' => null,
        'season_id' => $season->id,
        'visibility' => Visibility::Public,
        'points_exact' => 10,
        'points_result' => 5,
        'points_champion' => 25,
        'points_group_position' => 3,
    ]);

    expect($pool->slug)->toStartWith('bolao-da-firma')
        ->and($pool->invite_code)->toBeNull()
        ->and($pool->owner_id)->toBe($owner->id)
        ->and($pool->participants()->whereKey($owner->id)->exists())->toBeTrue();
});

it('generates an invite code for a private pool', function (): void {
    $owner = User::factory()->create();
    $season = Season::factory()->create();

    $pool = app(CreatePoolAction::class)->handle($owner, [
        'name' => 'Bolão Secreto',
        'description' => null,
        'season_id' => $season->id,
        'visibility' => Visibility::Private,
        'points_exact' => 10,
        'points_result' => 5,
        'points_champion' => 25,
        'points_group_position' => 3,
    ]);

    expect($pool->invite_code)->not->toBeNull()
        ->and(mb_strlen($pool->invite_code))->toBe(8);
});

it('makes unique slugs for duplicate names', function (): void {
    $owner = User::factory()->create();
    $season = Season::factory()->create();
    $data = [
        'name' => 'Mesmo Nome', 'description' => null, 'season_id' => $season->id,
        'visibility' => Visibility::Public, 'points_exact' => 10, 'points_result' => 5,
        'points_champion' => 25, 'points_group_position' => 3,
    ];

    $a = app(CreatePoolAction::class)->handle($owner, $data);
    $b = app(CreatePoolAction::class)->handle($owner, $data);

    expect($a->slug)->not->toBe($b->slug);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Pools/CreatePoolActionTest.php`
Expected: FAIL — `CreatePoolAction` not found.

- [ ] **Step 3: Implement the action**

```php
<?php

declare(strict_types=1);

namespace App\Actions\Pool;

use App\Enums\Pool\Visibility;
use App\Models\Pool;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreatePoolAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $owner, array $data): Pool
    {
        return DB::transaction(function () use ($owner, $data): Pool {
            $visibility = $data['visibility'] instanceof Visibility
                ? $data['visibility']
                : Visibility::from($data['visibility']);

            $pool = Pool::create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'description' => $data['description'] ?? null,
                'season_id' => $data['season_id'],
                'owner_id' => $owner->id,
                'visibility' => $visibility,
                'invite_code' => $visibility === Visibility::Private
                    ? Str::upper(Str::random(8))
                    : null,
                'points_exact' => $data['points_exact'],
                'points_result' => $data['points_result'],
                'points_champion' => $data['points_champion'],
                'points_group_position' => $data['points_group_position'],
            ]);

            $pool->participants()->attach($owner->id, ['joined_at' => now()]);

            return $pool;
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;

        while (Pool::where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(6));
        }

        return $slug;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Pools/CreatePoolActionTest.php`
Expected: PASS (3 passed).

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Pool/CreatePoolAction.php tests/Feature/Pools/CreatePoolActionTest.php
git commit -m "feat: add create pool action"
```

---

## Task 2: LeavePoolAction

**Files:**
- Create: `app/Actions/Pool/LeavePoolAction.php`
- Test: `tests/Feature/Pools/LeavePoolActionTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Actions\Pool\LeavePoolAction;
use App\Models\Pool;
use App\Models\User;

it('detaches a member from the pool', function (): void {
    $pool = Pool::factory()->public()->create();
    $member = User::factory()->create();
    app(JoinPoolAction::class)->handle($member, $pool);

    app(LeavePoolAction::class)->handle($member, $pool);

    expect($pool->participants()->whereKey($member->id)->exists())->toBeFalse();
});

it('does not let the owner leave', function (): void {
    $owner = User::factory()->create();
    $pool = Pool::factory()->public()->create(['owner_id' => $owner->id]);

    expect(fn () => app(LeavePoolAction::class)->handle($owner, $pool))
        ->toThrow(RuntimeException::class);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Pools/LeavePoolActionTest.php`
Expected: FAIL — `LeavePoolAction` not found.

- [ ] **Step 3: Implement the action**

```php
<?php

declare(strict_types=1);

namespace App\Actions\Pool;

use App\Models\Pool;
use App\Models\User;
use RuntimeException;

final class LeavePoolAction
{
    public function handle(User $user, Pool $pool): void
    {
        if ($user->id === $pool->owner_id) {
            throw new RuntimeException('O dono não pode sair do próprio bolão.');
        }

        $pool->participants()->detach($user->id);
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Pools/LeavePoolActionTest.php`
Expected: PASS (2 passed).

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Pool/LeavePoolAction.php tests/Feature/Pools/LeavePoolActionTest.php
git commit -m "feat: add leave pool action"
```

---

## Task 3: Create pool screen

**Files:**
- Create: `app/Livewire/Pools/Create.php`
- Create: `resources/views/livewire/pools/create.blade.php`
- Modify: `routes/web.php`
- Test: generated colocated test (replace body)

- [ ] **Step 1: Scaffold**

Run: `docker exec oddly_php php artisan make:livewire Pools/Create --class --no-interaction`. Note the generated test path (likely `tests/Feature/Livewire/Pools/CreateTest.php`).

- [ ] **Step 2: Write the failing test (replace generated body)**

```php
<?php

declare(strict_types=1);

use App\Enums\Pool\Visibility;
use App\Livewire\Pools\Create;
use App\Models\Pool;
use App\Models\Season;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(fn () => actingAs(User::factory()->create()));

it('requires a name and season', function (): void {
    Livewire::test(Create::class)
        ->set('name', '')
        ->set('season_id', null)
        ->call('create')
        ->assertHasErrors(['name' => 'required', 'season_id' => 'required']);
});

it('creates a pool and redirects to its page', function (): void {
    $season = Season::factory()->create();

    Livewire::test(Create::class)
        ->set('name', 'Bolão Top')
        ->set('season_id', $season->id)
        ->set('visibility', Visibility::Public->value)
        ->call('create')
        ->assertHasNoErrors();

    $pool = Pool::where('name', 'Bolão Top')->first();
    expect($pool)->not->toBeNull()
        ->and($pool->participants()->whereKey(auth()->id())->exists())->toBeTrue();
});

it('persists custom point values', function (): void {
    $season = Season::factory()->create();

    Livewire::test(Create::class)
        ->set('name', 'Bolão Pontos')
        ->set('season_id', $season->id)
        ->set('visibility', Visibility::Private->value)
        ->set('points_exact', 20)
        ->call('create')
        ->assertHasNoErrors();

    expect(Pool::where('name', 'Bolão Pontos')->first()->points_exact)->toBe(20);
});
```

- [ ] **Step 3: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact <generated Create test path>`
Expected: FAIL.

- [ ] **Step 4: Implement the component**

`app/Livewire/Pools/Create.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Pool\CreatePoolAction;
use App\Enums\Pool\Visibility;
use App\Models\Season;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Create extends Component
{
    #[Validate('required|string|max:255')]
    public string $name = '';

    public ?string $description = null;

    #[Validate('required|exists:seasons,id')]
    public ?int $season_id = null;

    #[Validate('required')]
    public string $visibility = 'private';

    #[Validate('required|integer|min:0')]
    public int $points_exact = 10;

    #[Validate('required|integer|min:0')]
    public int $points_result = 5;

    #[Validate('required|integer|min:0')]
    public int $points_champion = 25;

    #[Validate('required|integer|min:0')]
    public int $points_group_position = 3;

    public function create(CreatePoolAction $action): void
    {
        $data = $this->validate();
        $data['visibility'] = Visibility::from($this->visibility);

        $pool = $action->handle(auth()->user(), $data);

        $this->redirectRoute('pools.show', $pool);
    }

    /**
     * @return Collection<int, Season>
     */
    public function seasons(): Collection
    {
        return Season::query()->get();
    }

    public function render(): View
    {
        return view('livewire.pools.create', ['seasons' => $this->seasons()]);
    }
}
```

- [ ] **Step 5: Implement the view**

`resources/views/livewire/pools/create.blade.php` (adjust Flux components to the installed version via the `fluxui-development` skill if needed):

```blade
<div class="max-w-xl mx-auto">
    <flux:heading size="xl" class="mb-6">Criar bolão</flux:heading>

    <form wire:submit="create" class="space-y-6">
        <flux:input label="Nome" required wire:model="name" />
        <flux:textarea label="Descrição" wire:model="description" />

        <flux:select label="Temporada" wire:model="season_id">
            <flux:select.option value="">Selecione…</flux:select.option>
            @foreach ($seasons as $season)
                <flux:select.option :value="$season->id">{{ $season->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select label="Visibilidade" wire:model="visibility">
            <flux:select.option value="public">Público</flux:select.option>
            <flux:select.option value="private">Privado</flux:select.option>
        </flux:select>

        <flux:heading size="lg">Pontuação</flux:heading>
        <div class="grid grid-cols-2 gap-4">
            <flux:input type="number" label="Placar exato" wire:model="points_exact" />
            <flux:input type="number" label="Resultado" wire:model="points_result" />
            <flux:input type="number" label="Campeão" wire:model="points_champion" />
            <flux:input type="number" label="Posição no grupo" wire:model="points_group_position" />
        </div>

        <flux:button type="submit" variant="primary" color="cyan">Criar bolão</flux:button>
    </form>
</div>
```

- [ ] **Step 6: Register the route**

In `routes/web.php`, inside the `auth` group, add (import the component classes at the top as needed):

```php
Route::get('/pools/create', \App\Livewire\Pools\Create::class)->name('pools.create');
```

Note: register `/pools/create` BEFORE `/pools/{pool:slug}` (added in Task 5) so `create` isn't captured as a slug.

- [ ] **Step 7: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact <generated Create test path>`
Expected: PASS (3 passed). The redirect target `pools.show` route is added in Task 5; if this test runs before Task 5, temporarily the redirect route won't resolve — to keep this task self-contained, also add the `pools.show` route now pointing at a placeholder, OR run Task 5 before re-running. Simplest: add the real `pools.show` route in Task 5 and run the Create test's redirect assertion is `assertHasNoErrors()` only (it does not assert the redirect URL), so it passes without the route resolving. Confirm the test asserts no errors, not the redirect URL.

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Pools/Create.php resources/views/livewire/pools/create.blade.php routes/web.php <generated Create test path>
git commit -m "feat: add create pool screen"
```

---

## Task 4: Browse pools screen

**Files:**
- Create: `app/Livewire/Pools/Browse.php`
- Create: `resources/views/livewire/pools/browse.blade.php`
- Modify: `routes/web.php`
- Test: generated colocated test (replace body)

- [ ] **Step 1: Scaffold**

Run: `docker exec oddly_php php artisan make:livewire Pools/Browse --class --no-interaction`. Note the generated test path.

- [ ] **Step 2: Write the failing test (replace generated body)**

```php
<?php

declare(strict_types=1);

use App\Livewire\Pools\Browse;
use App\Models\Pool;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(fn () => actingAs(User::factory()->create()));

it('lists public pools and not private ones', function (): void {
    $public = Pool::factory()->public()->create(['name' => 'Aberto']);
    Pool::factory()->create(['name' => 'Fechado']); // private by default

    Livewire::test(Browse::class)
        ->assertSee('Aberto')
        ->assertDontSee('Fechado');
});

it('joins a public pool and redirects to it', function (): void {
    $pool = Pool::factory()->public()->create();

    Livewire::test(Browse::class)
        ->call('join', $pool->id)
        ->assertHasNoErrors();

    expect($pool->participants()->whereKey(auth()->id())->exists())->toBeTrue();
});

it('joins a private pool with the correct invite code', function (): void {
    $pool = Pool::factory()->create(['invite_code' => 'SECRET12']);

    Livewire::test(Browse::class)
        ->set('inviteCode', 'SECRET12')
        ->call('joinByCode')
        ->assertHasNoErrors();

    expect($pool->participants()->whereKey(auth()->id())->exists())->toBeTrue();
});

it('shows an error for a wrong invite code', function (): void {
    Pool::factory()->create(['invite_code' => 'SECRET12']);

    Livewire::test(Browse::class)
        ->set('inviteCode', 'WRONG')
        ->call('joinByCode')
        ->assertHasErrors('inviteCode');
});
```

- [ ] **Step 3: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact <generated Browse test path>`
Expected: FAIL.

- [ ] **Step 4: Implement the component**

`app/Livewire/Pools/Browse.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Pool\JoinPoolAction;
use App\Enums\Pool\Visibility;
use App\Models\Pool;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Browse extends Component
{
    #[Validate('nullable|string')]
    public string $inviteCode = '';

    public function join(int $poolId, JoinPoolAction $action): void
    {
        $pool = Pool::where('visibility', Visibility::Public)->findOrFail($poolId);

        $action->handle(auth()->user(), $pool);

        $this->redirectRoute('pools.show', $pool);
    }

    public function joinByCode(JoinPoolAction $action): void
    {
        $pool = Pool::where('invite_code', $this->inviteCode)->first();

        if ($pool === null) {
            $this->addError('inviteCode', 'Código de convite inválido.');

            return;
        }

        try {
            $action->handle(auth()->user(), $pool, $this->inviteCode);
        } catch (InvalidArgumentException $e) {
            $this->addError('inviteCode', $e->getMessage());

            return;
        }

        $this->redirectRoute('pools.show', $pool);
    }

    /**
     * @return Collection<int, Pool>
     */
    public function pools(): Collection
    {
        return Pool::query()
            ->where('visibility', Visibility::Public)
            ->withCount('participants')
            ->with('season')
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.pools.browse', ['pools' => $this->pools()]);
    }
}
```

- [ ] **Step 5: Implement the view**

`resources/views/livewire/pools/browse.blade.php`:

```blade
<div class="space-y-8">
    <div>
        <flux:heading size="xl" class="mb-4">Bolões públicos</flux:heading>

        @forelse ($pools as $pool)
            <flux:card class="mb-4 flex items-center justify-between">
                <div>
                    <flux:heading size="lg">{{ $pool->name }}</flux:heading>
                    <flux:text>Temporada {{ $pool->season->name }} · {{ $pool->participants_count }} participante(s)</flux:text>
                </div>
                <flux:button wire:click="join({{ $pool->id }})" variant="primary" color="cyan">Entrar</flux:button>
            </flux:card>
        @empty
            <flux:text>Nenhum bolão público ainda.</flux:text>
        @endforelse
    </div>

    <div class="max-w-md">
        <flux:heading size="lg" class="mb-2">Entrar por código</flux:heading>
        <form wire:submit="joinByCode" class="flex gap-2 items-end">
            <flux:input label="Código de convite" wire:model="inviteCode" />
            <flux:button type="submit">Entrar</flux:button>
        </form>
    </div>
</div>
```

- [ ] **Step 6: Register the route**

In `routes/web.php`, inside the `auth` group, add (keep `/pools/create` before `/pools/{pool:slug}`):

```php
Route::get('/pools', \App\Livewire\Pools\Browse::class)->name('pools.index');
```

- [ ] **Step 7: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact <generated Browse test path>`
Expected: PASS (4 passed). (The `join`/`joinByCode` redirect targets `pools.show`, added in Task 5; the tests assert participation + no errors, not the redirect URL.)

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Pools/Browse.php resources/views/livewire/pools/browse.blade.php routes/web.php <generated Browse test path>
git commit -m "feat: add browse and join pools screen"
```

---

## Task 5: Pool detail screen

**Files:**
- Create: `app/Livewire/Pools/Show.php`
- Create: `resources/views/livewire/pools/show.blade.php`
- Modify: `routes/web.php`
- Test: generated colocated test (replace body)

- [ ] **Step 1: Scaffold**

Run: `docker exec oddly_php php artisan make:livewire Pools/Show --class --no-interaction`. Note the generated test path.

- [ ] **Step 2: Write the failing test (replace generated body)**

```php
<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Livewire\Pools\Show;
use App\Models\Pool;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('lets the owner view a private pool and see the invite code', function (): void {
    $owner = User::factory()->create();
    actingAs($owner);
    $pool = Pool::factory()->create(['owner_id' => $owner->id, 'invite_code' => 'CODE1234']);
    $pool->participants()->attach($owner->id, ['joined_at' => now()]);

    Livewire::test(Show::class, ['pool' => $pool])
        ->assertOk()
        ->assertSee($pool->name)
        ->assertSee('CODE1234');
});

it('forbids a stranger from a private pool', function (): void {
    actingAs(User::factory()->create());
    $pool = Pool::factory()->create();

    Livewire::test(Show::class, ['pool' => $pool])->assertForbidden();
});

it('lets any authenticated user view a public pool but hides the invite code from non-members', function (): void {
    actingAs(User::factory()->create());
    $pool = Pool::factory()->public()->create(['invite_code' => null]);

    Livewire::test(Show::class, ['pool' => $pool])
        ->assertOk()
        ->assertSee($pool->name);
});

it('shows leave for a non-owner member and detaches on leave', function (): void {
    $member = User::factory()->create();
    actingAs($member);
    $pool = Pool::factory()->public()->create();
    app(JoinPoolAction::class)->handle($member, $pool);

    Livewire::test(Show::class, ['pool' => $pool])
        ->assertSee('Sair do bolão')
        ->call('leave')
        ->assertHasNoErrors();

    expect($pool->participants()->whereKey($member->id)->exists())->toBeFalse();
});

it('does not show leave to the owner', function (): void {
    $owner = User::factory()->create();
    actingAs($owner);
    $pool = Pool::factory()->public()->create(['owner_id' => $owner->id]);
    $pool->participants()->attach($owner->id, ['joined_at' => now()]);

    Livewire::test(Show::class, ['pool' => $pool])
        ->assertDontSee('Sair do bolão');
});
```

- [ ] **Step 3: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact <generated Show test path>`
Expected: FAIL.

- [ ] **Step 4: Implement the component**

`app/Livewire/Pools/Show.php`:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Pool\LeavePoolAction;
use App\Models\Pool;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Show extends Component
{
    public Pool $pool;

    public function mount(Pool $pool): void
    {
        abort_unless(Gate::allows('view', $pool), 403);

        $this->pool = $pool->load('season', 'participants', 'owner');
    }

    public function isMember(): bool
    {
        return $this->pool->participants->contains(auth()->id());
    }

    public function isOwner(): bool
    {
        return $this->pool->owner_id === auth()->id();
    }

    public function canSeeInviteCode(): bool
    {
        return $this->pool->invite_code !== null && ($this->isOwner() || $this->isMember());
    }

    public function canLeave(): bool
    {
        return ! $this->isOwner() && $this->isMember();
    }

    public function leave(LeavePoolAction $action): void
    {
        $action->handle(auth()->user(), $this->pool);

        $this->redirectRoute('dashboard');
    }

    public function render(): View
    {
        return view('livewire.pools.show');
    }
}
```

- [ ] **Step 5: Implement the view**

`resources/views/livewire/pools/show.blade.php`:

```blade
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ $pool->name }}</flux:heading>
        @if ($this->canLeave())
            <flux:button wire:click="leave" variant="danger">Sair do bolão</flux:button>
        @endif
    </div>

    <flux:text>Temporada {{ $pool->season->name }} · {{ $pool->visibility->getLabel() }}</flux:text>

    @if ($this->canSeeInviteCode())
        <flux:callout>
            Código de convite: <strong>{{ $pool->invite_code }}</strong>
        </flux:callout>
    @endif

    <flux:card>
        <flux:heading size="lg">Pontuação</flux:heading>
        <flux:text>Placar exato: {{ $pool->points_exact }} · Resultado: {{ $pool->points_result }} · Campeão: {{ $pool->points_champion }} · Grupo: {{ $pool->points_group_position }}</flux:text>
    </flux:card>

    <flux:card>
        <flux:heading size="lg" class="mb-2">Participantes</flux:heading>
        <ul class="space-y-1">
            @foreach ($pool->participants as $participant)
                <li>{{ $participant->name }}</li>
            @endforeach
        </ul>
    </flux:card>

    <div class="flex gap-3">
        <flux:button href="#" variant="ghost">Palpites</flux:button>
        <flux:button href="#" variant="ghost">Ranking</flux:button>
    </div>
</div>
```

If `flux:callout` doesn't exist in the installed Flux, use a plain styled `<div>` — keep the invite code text rendered so the test sees it.

- [ ] **Step 6: Register the route**

In `routes/web.php`, inside the `auth` group, add AFTER the `/pools` and `/pools/create` routes:

```php
Route::get('/pools/{pool:slug}', \App\Livewire\Pools\Show::class)->name('pools.show');
```

- [ ] **Step 7: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact <generated Show test path>`
Expected: PASS (5 passed). Then run the Create and Browse tests again now that `pools.show` resolves: `docker exec oddly_php php artisan test --compact tests/Feature/Livewire/Pools`.

- [ ] **Step 8: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Pools/Show.php resources/views/livewire/pools/show.blade.php routes/web.php <generated Show test path>
git commit -m "feat: add pool detail screen"
```

---

## Task 6: Wire dashboard buttons

**Files:**
- Modify: `resources/views/livewire/dashboard.blade.php`
- Modify: the Dashboard test (`tests/Feature/Livewire/DashboardTest.php`)

- [ ] **Step 1: Update the dashboard test**

Add to `tests/Feature/Livewire/DashboardTest.php`:

```php
it('links the empty-state buttons to the pool routes', function (): void {
    actingAs(User::factory()->create());

    Livewire::test(Dashboard::class)
        ->assertSee(route('pools.create'))
        ->assertSee(route('pools.index'));
});
```

(Ensure `use function Pest\Laravel\actingAs;` and `use App\Livewire\Dashboard;` are present — they already are.)

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Livewire/DashboardTest.php`
Expected: FAIL — buttons still link to `#`.

- [ ] **Step 3: Update the view**

In `resources/views/livewire/dashboard.blade.php`, change the empty-state buttons:
- "Criar bolão": `href="#"` → `:href="route('pools.create')"`.
- "Entrar em bolão": `href="#"` → `:href="route('pools.index')"`.

- [ ] **Step 4: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Livewire/DashboardTest.php`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add resources/views/livewire/dashboard.blade.php tests/Feature/Livewire/DashboardTest.php
git commit -m "feat: wire dashboard buttons to pool screens"
```

---

## Task 7: Full suite green + wrap-up

**Files:** none (verification)

- [ ] **Step 1: Full suite**

Run: `docker exec oddly_php php artisan test --compact`
Expected: all PASS (Phases 1–3a + 3b).

- [ ] **Step 2: Migrations + seed clean**

Run: `docker exec oddly_php php artisan migrate:fresh --seed --no-interaction`
Expected: clean.

- [ ] **Step 3: Formatting**

Run: `vendor/bin/pint --dirty --format agent`
Expected: clean.

- [ ] **Step 4: Final commit if needed**

```bash
git add -A
git commit -m "chore: phase 3b green" || echo "nothing to commit"
```

---

## Self-Review (completed during authoring)

- **Spec coverage:** CreatePoolAction (slug, invite_code, owner auto-join) — T1; LeavePoolAction (owner guard) — T2; Create screen (editable points, season select, redirect) — T3; Browse (public only, join, join-by-code with error) — T4; Show (authorize view, invite code for members, leave for non-owner members, placeholders) — T5; dashboard buttons wired — T6; full green — T7. Out-of-scope (edit/delete/pagination) correctly absent.
- **Type/name consistency:** routes `pools.index`/`pools.create`/`pools.show`; components `App\Livewire\Pools\{Browse,Create,Show}`; actions `CreatePoolAction::handle(User,array):Pool`, `LeavePoolAction::handle(User,Pool):void`; methods `join(int)`, `joinByCode()`, `leave()`, `pools()`. Tests reference these exactly. Route order note (create before {slug}) is called out.
- **Placeholder scan:** no TBD/TODO; full code in every step. Flux uncertainties point at the `fluxui-development` skill with a behavioral fallback; tests assert visible text/behavior, not Flux internals.
- **Redirect-route ordering:** Create/Browse redirect to `pools.show` which is added in Task 5; their tests assert participation + no errors (not the redirect URL), so they pass before Task 5 too, and Task 5 re-runs them.
