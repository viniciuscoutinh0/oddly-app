# Bolão Phase 1 — Domain & Scoring Engine Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the betting-pool data model and scoring engine — pools, participants, score bets, champion/group bonus bets, resolution actions, and per-pool leaderboard — fully covered by tests. No UI in this phase.

**Architecture:** A score bet is global per `(user, fixture)`; correctness flags (`is_exact`, `is_correct_result`) are stored on the bet when a fixture finishes and are pool-independent. Each pool stores its own point values and the `PoolStandings` service applies them to the stored flags plus bonus tallies. Resolution runs through single-purpose Action classes triggered by model observers (fixtures, seasons) and callable manually.

**Tech Stack:** Laravel 12, PHP 8.4, SQLite, Pest 4. Run artisan/tests inside the `oddly_php` Docker container (`docker exec oddly_php php artisan ...`); host PHP is 8.3 and fails. Pint runs on host (`vendor/bin/pint`).

---

## Conventions (match existing code)

- Models: `final class`, `declare(strict_types=1)`, casts via `casts(): array` method, relations with return type hints. `Model::unguard()` is global (no `$fillable` needed).
- Enums live in `app/Enums/<Domain>/`, string-backed, use `App\Enums\Concerns\HasCases`, implement Filament `HasLabel`/`HasColor` (methods `getLabel()`/`getColor()`) where shown in UI.
- Tests: Pest, in `tests/Feature` (gets `RefreshDatabase` via `tests/Pest.php`). Run with `docker exec oddly_php php artisan test --compact tests/Feature/<File>.php`.
- Factories use `fake()`.
- After editing PHP: `vendor/bin/pint --dirty --format agent`.
- Branch: `feature/bolao` (already checked out).

---

## Task 1: Role and Visibility enums

**Files:**
- Create: `app/Enums/User/Role.php`
- Create: `app/Enums/Pool/Visibility.php`
- Test: `tests/Feature/EnumsTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Enums\Pool\Visibility;
use App\Enums\User\Role;

it('exposes role cases with labels', function (): void {
    expect(Role::Admin->value)->toBe('admin')
        ->and(Role::Player->value)->toBe('player')
        ->and(Role::Admin->getLabel())->toBe('Administrador');
});

it('exposes visibility cases with labels', function (): void {
    expect(Visibility::Public->value)->toBe('public')
        ->and(Visibility::Private->value)->toBe('private')
        ->and(Visibility::Private->getLabel())->toBe('Privado');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/EnumsTest.php`
Expected: FAIL — `Class "App\Enums\User\Role" not found`.

- [ ] **Step 3: Create the Role enum**

```php
<?php

declare(strict_types=1);

namespace App\Enums\User;

use App\Enums\Concerns\HasCases;
use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel
{
    use HasCases;

    case Admin = 'admin';
    case Player = 'player';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Player => 'Jogador',
        };
    }
}
```

- [ ] **Step 4: Create the Visibility enum**

```php
<?php

declare(strict_types=1);

namespace App\Enums\Pool;

use App\Enums\Concerns\HasCases;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Visibility: string implements HasColor, HasLabel
{
    use HasCases;

    case Public = 'public';
    case Private = 'private';

    public function getLabel(): string
    {
        return match ($this) {
            self::Public => 'Público',
            self::Private => 'Privado',
        };
    }

    public function getColor(): array
    {
        return match ($this) {
            self::Public => Color::Green,
            self::Private => Color::Gray,
        };
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/EnumsTest.php`
Expected: PASS (2 passed).

- [ ] **Step 6: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Enums/User/Role.php app/Enums/Pool/Visibility.php tests/Feature/EnumsTest.php
git commit -m "feat: add Role and Visibility enums"
```

---

## Task 2: Add role to users

**Files:**
- Create migration via artisan (see step), edit `up()`
- Modify: `app/Models/User.php`
- Modify: `database/factories/UserFactory.php`
- Modify: `database/seeders/UserSeeder.php`
- Test: `tests/Feature/UserRoleTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Enums\User\Role;
use App\Models\User;
use Filament\Facades\Filament;

it('defaults to player role', function (): void {
    $user = User::factory()->create();

    expect($user->role)->toBe(Role::Player)
        ->and($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();
});

it('admins can access the panel', function (): void {
    $admin = User::factory()->admin()->create();

    expect($admin->role)->toBe(Role::Admin)
        ->and($admin->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/UserRoleTest.php`
Expected: FAIL — `role` column / `admin()` state missing.

- [ ] **Step 3: Create the migration**

Run: `docker exec oddly_php php artisan make:migration add_role_to_users_table --no-interaction`

Edit the generated file's `up()`/`down()`:

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table): void {
        $table->string('role', 20)->default('player')->after('email');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table): void {
        $table->dropColumn('role');
    });
}
```

- [ ] **Step 4: Update the User model**

In `app/Models/User.php`, import `use App\Enums\User\Role;`, add `role` to `casts()`:

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => Role::class,
    ];
}
```

Replace the existing `canAccessPanel` body:

```php
public function canAccessPanel(Panel $panel): bool
{
    return $this->role === Role::Admin;
}
```

- [ ] **Step 5: Add the factory admin state**

In `database/factories/UserFactory.php`, add `'role' => Role::Player` to `definition()` (import `use App\Enums\User\Role;`) and add:

```php
public function admin(): static
{
    return $this->state(fn (array $attributes): array => [
        'role' => Role::Admin,
    ]);
}
```

- [ ] **Step 6: Make the seeded user an admin**

In `database/seeders/UserSeeder.php`, add `'role' => \App\Enums\User\Role::Admin,` to the `create([...])` array.

- [ ] **Step 7: Run tests + full migrate to verify**

Run: `docker exec oddly_php php artisan migrate:fresh --no-interaction && docker exec oddly_php php artisan test --compact tests/Feature/UserRoleTest.php`
Expected: migrations run; PASS (2 passed).

- [ ] **Step 8: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/migrations app/Models/User.php database/factories/UserFactory.php database/seeders/UserSeeder.php tests/Feature/UserRoleTest.php
git commit -m "feat: add role to users with admin panel gating"
```

---

## Task 3: Pools table and model

**Files:**
- Create migration via artisan, edit `up()`
- Create: `app/Models/Pool.php`
- Create: `database/factories/PoolFactory.php`
- Modify: `app/Models/Season.php` (add `pools()` relation)
- Modify: `app/Models/User.php` (add `ownedPools()` relation)
- Test: `tests/Feature/PoolModelTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Enums\Pool\Visibility;
use App\Models\Pool;
use App\Models\Season;
use App\Models\User;

it('creates a pool with defaults and relations', function (): void {
    $pool = Pool::factory()->create();

    expect($pool->visibility)->toBeInstanceOf(Visibility::class)
        ->and($pool->points_exact)->toBe(10)
        ->and($pool->points_result)->toBe(5)
        ->and($pool->points_champion)->toBe(25)
        ->and($pool->points_group_position)->toBe(3)
        ->and($pool->season)->toBeInstanceOf(Season::class)
        ->and($pool->owner)->toBeInstanceOf(User::class);
});

it('relates pools back to season and owner', function (): void {
    $pool = Pool::factory()->create();

    expect($pool->season->pools->contains($pool))->toBeTrue()
        ->and($pool->owner->ownedPools->contains($pool))->toBeTrue();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/PoolModelTest.php`
Expected: FAIL — `App\Models\Pool` not found.

- [ ] **Step 3: Create the migration**

Run: `docker exec oddly_php php artisan make:migration create_pools_table --no-interaction`

```php
public function up(): void
{
    Schema::create('pools', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->foreignIdFor(\App\Models\Season::class)->constrained()->cascadeOnDelete();
        $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
        $table->string('visibility', 10)->default('private')->index();
        $table->string('invite_code')->nullable()->unique();
        $table->unsignedSmallInteger('points_exact')->default(10);
        $table->unsignedSmallInteger('points_result')->default(5);
        $table->unsignedSmallInteger('points_champion')->default(25);
        $table->unsignedSmallInteger('points_group_position')->default(3);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('pools');
}
```

- [ ] **Step 4: Create the Pool model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Pool\Visibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class Pool extends Model
{
    /** @use HasFactory<\Database\Factories\PoolFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'visibility' => Visibility::class,
            'points_exact' => 'integer',
            'points_result' => 'integer',
            'points_champion' => 'integer',
            'points_group_position' => 'integer',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pool_user')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    public function isPrivate(): bool
    {
        return $this->visibility === Visibility::Private;
    }
}
```

- [ ] **Step 5: Create the factory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Pool\Visibility;
use App\Models\Pool;
use App\Models\Season;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pool>
 */
final class PoolFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'description' => fake()->optional()->sentence(),
            'season_id' => Season::factory(),
            'owner_id' => User::factory(),
            'visibility' => Visibility::Private,
            'invite_code' => Str::upper(Str::random(8)),
            'points_exact' => 10,
            'points_result' => 5,
            'points_champion' => 25,
            'points_group_position' => 3,
        ];
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes): array => [
            'visibility' => Visibility::Public,
            'invite_code' => null,
        ]);
    }
}
```

- [ ] **Step 6: Add relations on Season and User**

In `app/Models/Season.php` add (import `HasMany` already present):

```php
public function pools(): HasMany
{
    return $this->hasMany(Pool::class);
}
```

In `app/Models/User.php` add `use Illuminate\Database\Eloquent\Relations\HasMany;` and:

```php
public function ownedPools(): HasMany
{
    return $this->hasMany(Pool::class, 'owner_id');
}
```

- [ ] **Step 7: Run test to verify it passes**

Run: `docker exec oddly_php php artisan migrate:fresh --no-interaction && docker exec oddly_php php artisan test --compact tests/Feature/PoolModelTest.php`
Expected: PASS (2 passed).

- [ ] **Step 8: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/migrations app/Models/Pool.php database/factories/PoolFactory.php app/Models/Season.php app/Models/User.php tests/Feature/PoolModelTest.php
git commit -m "feat: add pools table, model, and relations"
```

---

## Task 4: Participants (pool_user) and JoinPoolAction

**Files:**
- Create migration via artisan, edit `up()`
- Create: `app/Actions/Pool/JoinPoolAction.php`
- Modify: `app/Models/User.php` (add `pools()` relation)
- Create: `tests/Feature/JoinPoolTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Models\Pool;
use App\Models\User;

it('joins a public pool without a code', function (): void {
    $pool = Pool::factory()->public()->create();
    $user = User::factory()->create();

    app(JoinPoolAction::class)->handle($user, $pool);

    expect($pool->participants()->whereKey($user->id)->exists())->toBeTrue();
});

it('joins a private pool with the correct code', function (): void {
    $pool = Pool::factory()->create(['invite_code' => 'SECRET12']);
    $user = User::factory()->create();

    app(JoinPoolAction::class)->handle($user, $pool, 'SECRET12');

    expect($pool->participants()->whereKey($user->id)->exists())->toBeTrue();
});

it('rejects a private pool with a wrong code', function (): void {
    $pool = Pool::factory()->create(['invite_code' => 'SECRET12']);
    $user = User::factory()->create();

    expect(fn () => app(JoinPoolAction::class)->handle($user, $pool, 'WRONG'))
        ->toThrow(InvalidArgumentException::class);
});

it('is idempotent and does not duplicate membership', function (): void {
    $pool = Pool::factory()->public()->create();
    $user = User::factory()->create();

    app(JoinPoolAction::class)->handle($user, $pool);
    app(JoinPoolAction::class)->handle($user, $pool);

    expect($pool->participants()->whereKey($user->id)->count())->toBe(1);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/JoinPoolTest.php`
Expected: FAIL — migration/action missing.

- [ ] **Step 3: Create the migration**

Run: `docker exec oddly_php php artisan make:migration create_pool_user_table --no-interaction`

```php
public function up(): void
{
    Schema::create('pool_user', function (Blueprint $table): void {
        $table->id();
        $table->foreignIdFor(\App\Models\Pool::class)->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->timestamp('joined_at');
        $table->timestamps();
        $table->unique(['pool_id', 'user_id']);
    });
}

public function down(): void
{
    Schema::dropIfExists('pool_user');
}
```

- [ ] **Step 4: Add the pools() relation on User**

In `app/Models/User.php` add `use Illuminate\Database\Eloquent\Relations\BelongsToMany;` and:

```php
public function pools(): BelongsToMany
{
    return $this->belongsToMany(Pool::class, 'pool_user')
        ->withPivot('joined_at')
        ->withTimestamps();
}
```

- [ ] **Step 5: Create JoinPoolAction**

```php
<?php

declare(strict_types=1);

namespace App\Actions\Pool;

use App\Models\Pool;
use App\Models\User;
use InvalidArgumentException;

final class JoinPoolAction
{
    public function handle(User $user, Pool $pool, ?string $inviteCode = null): void
    {
        if ($pool->isPrivate() && $inviteCode !== $pool->invite_code) {
            throw new InvalidArgumentException('Código de convite inválido.');
        }

        if ($pool->participants()->whereKey($user->id)->exists()) {
            return;
        }

        $pool->participants()->attach($user->id, ['joined_at' => now()]);
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `docker exec oddly_php php artisan migrate:fresh --no-interaction && docker exec oddly_php php artisan test --compact tests/Feature/JoinPoolTest.php`
Expected: PASS (4 passed).

- [ ] **Step 7: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/migrations app/Actions/Pool/JoinPoolAction.php app/Models/User.php tests/Feature/JoinPoolTest.php
git commit -m "feat: add pool participants and join action"
```

---

## Task 5: Bets table, model, and relations

**Files:**
- Create migration via artisan, edit `up()`
- Create: `app/Models/Bet.php`
- Create: `database/factories/BetFactory.php`
- Modify: `app/Models/Fixture.php` (add `bets()` relation)
- Modify: `app/Models/User.php` (add `bets()` relation)
- Test: `tests/Feature/BetModelTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Models\Bet;
use App\Models\Fixture;
use App\Models\User;

it('creates a bet linked to a user and fixture', function (): void {
    $bet = Bet::factory()->create();

    expect($bet->user)->toBeInstanceOf(User::class)
        ->and($bet->fixture)->toBeInstanceOf(Fixture::class)
        ->and($bet->is_exact)->toBeNull()
        ->and($bet->is_correct_result)->toBeNull();
});

it('enforces one bet per user per fixture', function (): void {
    $bet = Bet::factory()->create();

    expect(fn () => Bet::factory()->create([
        'user_id' => $bet->user_id,
        'fixture_id' => $bet->fixture_id,
    ]))->toThrow(Illuminate\Database\QueryException::class);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/BetModelTest.php`
Expected: FAIL — `App\Models\Bet` not found.

- [ ] **Step 3: Create the migration**

Run: `docker exec oddly_php php artisan make:migration create_bets_table --no-interaction`

```php
public function up(): void
{
    Schema::create('bets', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignIdFor(\App\Models\Fixture::class)->constrained()->cascadeOnDelete();
        $table->unsignedTinyInteger('home_score');
        $table->unsignedTinyInteger('away_score');
        $table->boolean('is_exact')->nullable();
        $table->boolean('is_correct_result')->nullable();
        $table->timestamp('resolved_at')->nullable();
        $table->timestamps();
        $table->unique(['user_id', 'fixture_id']);
    });
}

public function down(): void
{
    Schema::dropIfExists('bets');
}
```

- [ ] **Step 4: Create the Bet model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Bet extends Model
{
    /** @use HasFactory<\Database\Factories\BetFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_exact' => 'boolean',
            'is_correct_result' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }
}
```

- [ ] **Step 5: Create the factory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bet;
use App\Models\Fixture;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bet>
 */
final class BetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fixture_id' => Fixture::factory(),
            'home_score' => fake()->numberBetween(0, 4),
            'away_score' => fake()->numberBetween(0, 4),
            'is_exact' => null,
            'is_correct_result' => null,
            'resolved_at' => null,
        ];
    }
}
```

- [ ] **Step 6: Add relations on Fixture and User**

In `app/Models/Fixture.php` add `use Illuminate\Database\Eloquent\Relations\HasMany;` and:

```php
public function bets(): HasMany
{
    return $this->hasMany(Bet::class);
}
```

In `app/Models/User.php` add (HasMany already imported in Task 3):

```php
public function bets(): HasMany
{
    return $this->hasMany(Bet::class);
}
```

- [ ] **Step 7: Run test to verify it passes**

Run: `docker exec oddly_php php artisan migrate:fresh --no-interaction && docker exec oddly_php php artisan test --compact tests/Feature/BetModelTest.php`
Expected: PASS (2 passed).

- [ ] **Step 8: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/migrations app/Models/Bet.php database/factories/BetFactory.php app/Models/Fixture.php app/Models/User.php tests/Feature/BetModelTest.php
git commit -m "feat: add bets table, model, and relations"
```

---

## Task 6: PlaceBetAction and BetPolicy (lock enforcement)

**Files:**
- Create: `app/Actions/Bet/PlaceBetAction.php`
- Create: `app/Policies/BetPolicy.php`
- Test: `tests/Feature/PlaceBetTest.php`

Note: `Fixture::isLocked()` already returns `locked_at !== null && now()->gte(locked_at)`. For the lock to also default to kickoff, `PlaceBetAction` treats a fixture as locked when `locked_at ?? match_date <= now()`.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Bet\PlaceBetAction;
use App\Models\Fixture;
use App\Models\User;

it('creates a bet before the fixture locks', function (): void {
    $user = User::factory()->create();
    $fixture = Fixture::factory()->create([
        'match_date' => now()->addDay(),
        'locked_at' => null,
    ]);

    $bet = app(PlaceBetAction::class)->handle($user, $fixture, 2, 1);

    expect($bet->home_score)->toBe(2)->and($bet->away_score)->toBe(1);
});

it('updates an existing bet instead of duplicating', function (): void {
    $user = User::factory()->create();
    $fixture = Fixture::factory()->create([
        'match_date' => now()->addDay(),
        'locked_at' => null,
    ]);

    app(PlaceBetAction::class)->handle($user, $fixture, 2, 1);
    app(PlaceBetAction::class)->handle($user, $fixture, 0, 0);

    expect($user->bets()->count())->toBe(1)
        ->and($user->bets()->first()->home_score)->toBe(0);
});

it('rejects a bet after the fixture is locked', function (): void {
    $user = User::factory()->create();
    $fixture = Fixture::factory()->create([
        'match_date' => now()->subHour(),
        'locked_at' => null,
    ]);

    expect(fn () => app(PlaceBetAction::class)->handle($user, $fixture, 1, 0))
        ->toThrow(RuntimeException::class);
});

it('respects an explicit locked_at', function (): void {
    $user = User::factory()->create();
    $fixture = Fixture::factory()->create([
        'match_date' => now()->addDay(),
        'locked_at' => now()->subMinute(),
    ]);

    expect(fn () => app(PlaceBetAction::class)->handle($user, $fixture, 1, 0))
        ->toThrow(RuntimeException::class);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/PlaceBetTest.php`
Expected: FAIL — `PlaceBetAction` not found.

- [ ] **Step 3: Create PlaceBetAction**

```php
<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\Bet;
use App\Models\Fixture;
use App\Models\User;
use RuntimeException;

final class PlaceBetAction
{
    public function handle(User $user, Fixture $fixture, int $homeScore, int $awayScore): Bet
    {
        if ($this->isLocked($fixture)) {
            throw new RuntimeException('Os palpites para este jogo estão encerrados.');
        }

        return $user->bets()->updateOrCreate(
            ['fixture_id' => $fixture->id],
            ['home_score' => $homeScore, 'away_score' => $awayScore],
        );
    }

    private function isLocked(Fixture $fixture): bool
    {
        $lockTime = $fixture->locked_at ?? $fixture->match_date;

        return now()->gte($lockTime);
    }
}
```

- [ ] **Step 4: Create BetPolicy**

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Bet;
use App\Models\User;

final class BetPolicy
{
    public function update(User $user, Bet $bet): bool
    {
        $fixture = $bet->fixture;
        $lockTime = $fixture->locked_at ?? $fixture->match_date;

        return $user->id === $bet->user_id && now()->lt($lockTime);
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/PlaceBetTest.php`
Expected: PASS (4 passed).

- [ ] **Step 6: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Bet/PlaceBetAction.php app/Policies/BetPolicy.php tests/Feature/PlaceBetTest.php
git commit -m "feat: add place-bet action and bet policy with lock enforcement"
```

---

## Task 7: ResolveFixtureBetsAction and FixtureObserver

**Files:**
- Create: `app/Actions/Bet/ResolveFixtureBetsAction.php`
- Create: `app/Observers/FixtureObserver.php`
- Modify: `app/Models/Fixture.php` (add `#[ObservedBy(FixtureObserver::class)]`)
- Test: `tests/Feature/ResolveFixtureBetsTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Bet\ResolveFixtureBetsAction;
use App\Enums\Fixture\Status;
use App\Models\Bet;
use App\Models\Fixture;

function finishedFixture(int $home, int $away): Fixture
{
    return Fixture::factory()->create([
        'status' => Status::Finished,
        'home_score' => $home,
        'away_score' => $away,
    ]);
}

it('flags an exact bet as exact and correct result', function (): void {
    $fixture = finishedFixture(2, 1);
    $bet = Bet::factory()->for($fixture)->create(['home_score' => 2, 'away_score' => 1]);

    app(ResolveFixtureBetsAction::class)->handle($fixture->fresh());

    $bet->refresh();
    expect($bet->is_exact)->toBeTrue()
        ->and($bet->is_correct_result)->toBeTrue()
        ->and($bet->resolved_at)->not->toBeNull();
});

it('flags a correct-result-only bet', function (): void {
    $fixture = finishedFixture(2, 1);
    $bet = Bet::factory()->for($fixture)->create(['home_score' => 3, 'away_score' => 0]);

    app(ResolveFixtureBetsAction::class)->handle($fixture->fresh());

    $bet->refresh();
    expect($bet->is_exact)->toBeFalse()->and($bet->is_correct_result)->toBeTrue();
});

it('flags a wrong bet', function (): void {
    $fixture = finishedFixture(2, 1);
    $bet = Bet::factory()->for($fixture)->create(['home_score' => 0, 'away_score' => 2]);

    app(ResolveFixtureBetsAction::class)->handle($fixture->fresh());

    $bet->refresh();
    expect($bet->is_exact)->toBeFalse()->and($bet->is_correct_result)->toBeFalse();
});

it('handles draws as correct result', function (): void {
    $fixture = finishedFixture(1, 1);
    $bet = Bet::factory()->for($fixture)->create(['home_score' => 2, 'away_score' => 2]);

    app(ResolveFixtureBetsAction::class)->handle($fixture->fresh());

    $bet->refresh();
    expect($bet->is_exact)->toBeFalse()->and($bet->is_correct_result)->toBeTrue();
});

it('recomputes idempotently after a score edit', function (): void {
    $fixture = finishedFixture(2, 1);
    $bet = Bet::factory()->for($fixture)->create(['home_score' => 2, 'away_score' => 1]);

    app(ResolveFixtureBetsAction::class)->handle($fixture->fresh());
    $fixture->update(['home_score' => 0, 'away_score' => 0]);
    app(ResolveFixtureBetsAction::class)->handle($fixture->fresh());

    $bet->refresh();
    expect($bet->is_exact)->toBeFalse()->and($bet->is_correct_result)->toBeFalse();
});

it('resolves bets automatically via the observer when a fixture finishes', function (): void {
    $fixture = Fixture::factory()->create([
        'status' => Status::Scheduled,
        'home_score' => null,
        'away_score' => null,
    ]);
    $bet = Bet::factory()->for($fixture)->create(['home_score' => 2, 'away_score' => 1]);

    $fixture->update([
        'status' => Status::Finished,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    expect($bet->refresh()->is_exact)->toBeTrue();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/ResolveFixtureBetsTest.php`
Expected: FAIL — `ResolveFixtureBetsAction` not found.

- [ ] **Step 3: Create ResolveFixtureBetsAction**

```php
<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\Fixture;

final class ResolveFixtureBetsAction
{
    public function handle(Fixture $fixture): void
    {
        if (! $fixture->isFinished() || $fixture->home_score === null || $fixture->away_score === null) {
            return;
        }

        $resultSign = $fixture->home_score <=> $fixture->away_score;

        $fixture->bets()->chunkById(200, function ($bets) use ($fixture, $resultSign): void {
            foreach ($bets as $bet) {
                $bet->is_exact = $bet->home_score === $fixture->home_score
                    && $bet->away_score === $fixture->away_score;
                $bet->is_correct_result = ($bet->home_score <=> $bet->away_score) === $resultSign;
                $bet->resolved_at = now();
                $bet->save();
            }
        });
    }
}
```

- [ ] **Step 4: Create the observer**

```php
<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\Bet\ResolveFixtureBetsAction;
use App\Models\Fixture;

final class FixtureObserver
{
    public function __construct(private ResolveFixtureBetsAction $resolveBets) {}

    public function saved(Fixture $fixture): void
    {
        if (! $fixture->wasChanged(['status', 'home_score', 'away_score'])) {
            return;
        }

        $this->resolveBets->handle($fixture);
    }
}
```

- [ ] **Step 5: Attach the observer to the model**

In `app/Models/Fixture.php` add `use App\Observers\FixtureObserver;` and `use Illuminate\Database\Eloquent\Attributes\ObservedBy;`, then put the attribute directly above the class:

```php
#[ObservedBy(FixtureObserver::class)]
final class Fixture extends Model
```

- [ ] **Step 6: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/ResolveFixtureBetsTest.php`
Expected: PASS (6 passed).

- [ ] **Step 7: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Bet/ResolveFixtureBetsAction.php app/Observers/FixtureObserver.php app/Models/Fixture.php tests/Feature/ResolveFixtureBetsTest.php
git commit -m "feat: resolve fixture bet flags on finish via action and observer"
```

---

## Task 8: Champion bonus — table, model, action, observer

**Files:**
- Create migration via artisan, edit `up()`
- Create: `app/Models/ChampionBet.php`
- Create: `database/factories/ChampionBetFactory.php`
- Create: `app/Actions/Bet/ResolveChampionBetsAction.php`
- Create: `app/Observers/SeasonObserver.php`
- Modify: `app/Models/Season.php` (add relation + `#[ObservedBy]`)
- Modify: `app/Models/User.php` (add `championBets()` relation)
- Test: `tests/Feature/ResolveChampionBetsTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Bet\ResolveChampionBetsAction;
use App\Models\ChampionBet;
use App\Models\Season;
use App\Models\Team;

it('marks champion bets correct when they match the winner', function (): void {
    $season = Season::factory()->create();
    $winner = Team::factory()->create();
    $loser = Team::factory()->create();

    $right = ChampionBet::factory()->for($season)->create(['team_id' => $winner->id]);
    $wrong = ChampionBet::factory()->for($season)->create(['team_id' => $loser->id]);

    $season->update(['winner_id' => $winner->id]);
    app(ResolveChampionBetsAction::class)->handle($season->fresh());

    expect($right->refresh()->is_correct)->toBeTrue()
        ->and($wrong->refresh()->is_correct)->toBeFalse();
});

it('does nothing while the season has no winner', function (): void {
    $season = Season::factory()->create(['winner_id' => null]);
    $bet = ChampionBet::factory()->for($season)->create();

    app(ResolveChampionBetsAction::class)->handle($season);

    expect($bet->refresh()->is_correct)->toBeNull();
});

it('resolves automatically via the observer when a winner is set', function (): void {
    $season = Season::factory()->create(['winner_id' => null]);
    $winner = Team::factory()->create();
    $bet = ChampionBet::factory()->for($season)->create(['team_id' => $winner->id]);

    $season->update(['winner_id' => $winner->id]);

    expect($bet->refresh()->is_correct)->toBeTrue();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/ResolveChampionBetsTest.php`
Expected: FAIL — table/model/action missing.

- [ ] **Step 3: Create the migration**

Run: `docker exec oddly_php php artisan make:migration create_champion_bets_table --no-interaction`

```php
public function up(): void
{
    Schema::create('champion_bets', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignIdFor(\App\Models\Season::class)->constrained()->cascadeOnDelete();
        $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
        $table->boolean('is_correct')->nullable();
        $table->timestamp('resolved_at')->nullable();
        $table->timestamps();
        $table->unique(['user_id', 'season_id']);
    });
}

public function down(): void
{
    Schema::dropIfExists('champion_bets');
}
```

- [ ] **Step 4: Create the ChampionBet model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ChampionBet extends Model
{
    /** @use HasFactory<\Database\Factories\ChampionBetFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
```

- [ ] **Step 5: Create the factory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ChampionBet;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChampionBet>
 */
final class ChampionBetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'season_id' => Season::factory(),
            'team_id' => Team::factory(),
            'is_correct' => null,
            'resolved_at' => null,
        ];
    }
}
```

- [ ] **Step 6: Create ResolveChampionBetsAction**

```php
<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\Season;

final class ResolveChampionBetsAction
{
    public function handle(Season $season): void
    {
        if ($season->winner_id === null) {
            return;
        }

        $season->championBets()->chunkById(200, function ($bets) use ($season): void {
            foreach ($bets as $bet) {
                $bet->is_correct = $bet->team_id === $season->winner_id;
                $bet->resolved_at = now();
                $bet->save();
            }
        });
    }
}
```

- [ ] **Step 7: Create the SeasonObserver**

```php
<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\Bet\ResolveChampionBetsAction;
use App\Models\Season;

final class SeasonObserver
{
    public function __construct(private ResolveChampionBetsAction $resolveChampion) {}

    public function saved(Season $season): void
    {
        if (! $season->wasChanged('winner_id')) {
            return;
        }

        $this->resolveChampion->handle($season);
    }
}
```

- [ ] **Step 8: Wire relations and observer**

In `app/Models/Season.php` add `use App\Observers\SeasonObserver;` and `use Illuminate\Database\Eloquent\Attributes\ObservedBy;`, add `#[ObservedBy(SeasonObserver::class)]` above the class, and add:

```php
public function championBets(): HasMany
{
    return $this->hasMany(ChampionBet::class);
}
```

In `app/Models/User.php` add:

```php
public function championBets(): HasMany
{
    return $this->hasMany(ChampionBet::class);
}
```

- [ ] **Step 9: Run test to verify it passes**

Run: `docker exec oddly_php php artisan migrate:fresh --no-interaction && docker exec oddly_php php artisan test --compact tests/Feature/ResolveChampionBetsTest.php`
Expected: PASS (3 passed).

- [ ] **Step 10: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/migrations app/Models/ChampionBet.php database/factories/ChampionBetFactory.php app/Actions/Bet/ResolveChampionBetsAction.php app/Observers/SeasonObserver.php app/Models/Season.php app/Models/User.php tests/Feature/ResolveChampionBetsTest.php
git commit -m "feat: add champion bonus bets with resolution action and observer"
```

---

## Task 9: Group standings bonus — table, model, action

**Files:**
- Create migration via artisan, edit `up()`
- Create: `app/Models/GroupBet.php`
- Create: `database/factories/GroupBetFactory.php`
- Create: `app/Actions/Bet/ResolveGroupBetsAction.php`
- Modify: `app/Models/Season.php` (add `groupBets()` relation)
- Modify: `app/Models/User.php` (add `groupBets()` relation)
- Test: `tests/Feature/ResolveGroupBetsTest.php`

Note: group resolution reads actual standings from the `season_teams` pivot
(`group_letter`, `group_position`). There is no observer (pivot edits don't fire
season events); it is invoked manually (a Filament action in Phase 2) and tested
directly here.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Bet\ResolveGroupBetsAction;
use App\Models\GroupBet;
use App\Models\Season;
use App\Models\Team;

it('marks group bets correct against actual group positions', function (): void {
    $season = Season::factory()->create();
    $first = Team::factory()->create();
    $second = Team::factory()->create();

    $season->teams()->attach($first->id, ['group_letter' => 'A', 'group_position' => 1]);
    $season->teams()->attach($second->id, ['group_letter' => 'A', 'group_position' => 2]);

    $right = GroupBet::factory()->for($season)->create([
        'group_letter' => 'A', 'team_id' => $first->id, 'predicted_position' => 1,
    ]);
    $swapped = GroupBet::factory()->for($season)->create([
        'group_letter' => 'A', 'team_id' => $first->id, 'predicted_position' => 2,
    ]);

    app(ResolveGroupBetsAction::class)->handle($season);

    expect($right->refresh()->is_correct)->toBeTrue()
        ->and($swapped->refresh()->is_correct)->toBeFalse();
});

it('marks a bet incorrect when the slot has no actual team yet', function (): void {
    $season = Season::factory()->create();
    $team = Team::factory()->create();

    $bet = GroupBet::factory()->for($season)->create([
        'group_letter' => 'B', 'team_id' => $team->id, 'predicted_position' => 1,
    ]);

    app(ResolveGroupBetsAction::class)->handle($season);

    expect($bet->refresh()->is_correct)->toBeFalse();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/ResolveGroupBetsTest.php`
Expected: FAIL — table/model/action missing.

- [ ] **Step 3: Create the migration**

Run: `docker exec oddly_php php artisan make:migration create_group_bets_table --no-interaction`

```php
public function up(): void
{
    Schema::create('group_bets', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignIdFor(\App\Models\Season::class)->constrained()->cascadeOnDelete();
        $table->string('group_letter', 1);
        $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
        $table->unsignedTinyInteger('predicted_position');
        $table->boolean('is_correct')->nullable();
        $table->timestamp('resolved_at')->nullable();
        $table->timestamps();
        $table->unique(['user_id', 'season_id', 'group_letter', 'predicted_position']);
    });
}

public function down(): void
{
    Schema::dropIfExists('group_bets');
}
```

- [ ] **Step 4: Create the GroupBet model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GroupBet extends Model
{
    /** @use HasFactory<\Database\Factories\GroupBetFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'predicted_position' => 'integer',
            'is_correct' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
```

- [ ] **Step 5: Create the factory**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GroupBet;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroupBet>
 */
final class GroupBetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'season_id' => Season::factory(),
            'group_letter' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'team_id' => Team::factory(),
            'predicted_position' => fake()->numberBetween(1, 2),
            'is_correct' => null,
            'resolved_at' => null,
        ];
    }
}
```

- [ ] **Step 6: Create ResolveGroupBetsAction**

```php
<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\Season;

final class ResolveGroupBetsAction
{
    public function handle(Season $season): void
    {
        // Map of "<group_letter>:<position>" => team_id from the actual standings.
        $actual = $season->teams()
            ->wherePivotNotNull('group_position')
            ->get()
            ->mapWithKeys(fn ($team): array => [
                $team->pivot->group_letter.':'.$team->pivot->group_position => $team->id,
            ]);

        $season->groupBets()->chunkById(200, function ($bets) use ($actual): void {
            foreach ($bets as $bet) {
                $key = $bet->group_letter.':'.$bet->predicted_position;
                $bet->is_correct = ($actual[$key] ?? null) === $bet->team_id;
                $bet->resolved_at = now();
                $bet->save();
            }
        });
    }
}
```

- [ ] **Step 7: Add relations**

In `app/Models/Season.php` add:

```php
public function groupBets(): HasMany
{
    return $this->hasMany(GroupBet::class);
}
```

In `app/Models/User.php` add:

```php
public function groupBets(): HasMany
{
    return $this->hasMany(GroupBet::class);
}
```

- [ ] **Step 8: Run test to verify it passes**

Run: `docker exec oddly_php php artisan migrate:fresh --no-interaction && docker exec oddly_php php artisan test --compact tests/Feature/ResolveGroupBetsTest.php`
Expected: PASS (2 passed).

- [ ] **Step 9: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/migrations app/Models/GroupBet.php database/factories/GroupBetFactory.php app/Actions/Bet/ResolveGroupBetsAction.php app/Models/Season.php app/Models/User.php tests/Feature/ResolveGroupBetsTest.php
git commit -m "feat: add group-standings bonus bets with resolution action"
```

---

## Task 10: PoolStandings service (leaderboard)

**Files:**
- Create: `app/Services/PoolStandings.php`
- Test: `tests/Feature/PoolStandingsTest.php`

The service computes, for each participant of a pool, total points = score-bet
points (over the pool's season fixtures) + champion bonus + group bonus, applying
the pool's configured point values. Returns a collection ordered by points desc.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Enums\Fixture\Status;
use App\Models\Bet;
use App\Models\ChampionBet;
use App\Models\Fixture;
use App\Models\Pool;
use App\Models\Season;
use App\Models\Stage;
use App\Models\Team;
use App\Models\User;
use App\Services\PoolStandings;

it('ranks participants by total points using pool rules', function (): void {
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    $fixture = Fixture::factory()->for($stage)->create([
        'status' => Status::Finished, 'home_score' => 2, 'away_score' => 1,
    ]);

    $pool = Pool::factory()->public()->create([
        'season_id' => $season->id,
        'points_exact' => 10, 'points_result' => 5, 'points_champion' => 25,
    ]);

    $exactUser = User::factory()->create();
    $resultUser = User::factory()->create();
    app(JoinPoolAction::class)->handle($exactUser, $pool);
    app(JoinPoolAction::class)->handle($resultUser, $pool);

    // Exact bet: flags set as the resolver would.
    Bet::factory()->for($fixture)->create([
        'user_id' => $exactUser->id, 'home_score' => 2, 'away_score' => 1,
        'is_exact' => true, 'is_correct_result' => true,
    ]);
    // Correct-result-only bet.
    Bet::factory()->for($fixture)->create([
        'user_id' => $resultUser->id, 'home_score' => 3, 'away_score' => 0,
        'is_exact' => false, 'is_correct_result' => true,
    ]);
    // Champion bonus for the exact user only.
    $champion = Team::factory()->create();
    $season->update(['winner_id' => $champion->id]);
    ChampionBet::factory()->for($season)->create([
        'user_id' => $exactUser->id, 'team_id' => $champion->id, 'is_correct' => true,
    ]);

    $standings = app(PoolStandings::class)->for($pool);

    expect($standings->first()['user']->id)->toBe($exactUser->id)
        ->and($standings->first()['points'])->toBe(35) // 10 exact + 25 champion
        ->and($standings->last()['points'])->toBe(5);  // result only
});

it('returns zero points for a participant with no bets', function (): void {
    $pool = Pool::factory()->public()->create();
    $user = User::factory()->create();
    app(JoinPoolAction::class)->handle($user, $pool);

    $standings = app(PoolStandings::class)->for($pool);

    expect($standings)->toHaveCount(1)
        ->and($standings->first()['points'])->toBe(0);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/PoolStandingsTest.php`
Expected: FAIL — `PoolStandings` not found.

- [ ] **Step 3: Create the service**

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Pool;
use Illuminate\Support\Collection;

final class PoolStandings
{
    /**
     * @return Collection<int, array{user: \App\Models\User, points: int}>
     */
    public function for(Pool $pool): Collection
    {
        $season = $pool->season;
        $fixtureIds = $season->fixtures()->pluck('fixtures.id');
        $participants = $pool->participants()->get();
        $participantIds = $participants->pluck('id');

        $betPoints = \App\Models\Bet::query()
            ->whereIn('fixture_id', $fixtureIds)
            ->whereIn('user_id', $participantIds)
            ->get()
            ->groupBy('user_id')
            ->map(fn ($bets): int => $bets->sum(fn ($bet): int => match (true) {
                $bet->is_exact === true => $pool->points_exact,
                $bet->is_correct_result === true => $pool->points_result,
                default => 0,
            }));

        $championPoints = \App\Models\ChampionBet::query()
            ->where('season_id', $season->id)
            ->whereIn('user_id', $participantIds)
            ->where('is_correct', true)
            ->pluck('user_id')
            ->mapWithKeys(fn (int $userId): array => [$userId => $pool->points_champion]);

        $groupPoints = \App\Models\GroupBet::query()
            ->where('season_id', $season->id)
            ->whereIn('user_id', $participantIds)
            ->where('is_correct', true)
            ->get()
            ->groupBy('user_id')
            ->map(fn ($bets): int => $bets->count() * $pool->points_group_position);

        return $participants
            ->map(fn ($user): array => [
                'user' => $user,
                'points' => (int) ($betPoints[$user->id] ?? 0)
                    + (int) ($championPoints[$user->id] ?? 0)
                    + (int) ($groupPoints[$user->id] ?? 0),
            ])
            ->sortByDesc('points')
            ->values();
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/PoolStandingsTest.php`
Expected: PASS (2 passed).

- [ ] **Step 5: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Services/PoolStandings.php tests/Feature/PoolStandingsTest.php
git commit -m "feat: add pool standings leaderboard service"
```

---

## Task 11: PoolPolicy (visibility & membership)

**Files:**
- Create: `app/Policies/PoolPolicy.php`
- Test: `tests/Feature/PoolPolicyTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Models\Pool;
use App\Models\User;
use App\Policies\PoolPolicy;

it('lets anyone view a public pool', function (): void {
    $pool = Pool::factory()->public()->create();
    $user = User::factory()->create();

    expect((new PoolPolicy)->view($user, $pool))->toBeTrue();
});

it('hides a private pool from non-members', function (): void {
    $pool = Pool::factory()->create();
    $stranger = User::factory()->create();

    expect((new PoolPolicy)->view($stranger, $pool))->toBeFalse();
});

it('lets members and the owner view a private pool', function (): void {
    $owner = User::factory()->create();
    $pool = Pool::factory()->create(['owner_id' => $owner->id]);
    $member = User::factory()->create();
    app(JoinPoolAction::class)->handle($member, $pool, $pool->invite_code);

    expect((new PoolPolicy)->view($owner, $pool))->toBeTrue()
        ->and((new PoolPolicy)->view($member->fresh(), $pool))->toBeTrue();
});

it('only lets the owner update a pool', function (): void {
    $owner = User::factory()->create();
    $pool = Pool::factory()->create(['owner_id' => $owner->id]);
    $other = User::factory()->create();

    expect((new PoolPolicy)->update($owner, $pool))->toBeTrue()
        ->and((new PoolPolicy)->update($other, $pool))->toBeFalse();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/PoolPolicyTest.php`
Expected: FAIL — `PoolPolicy` not found.

- [ ] **Step 3: Create PoolPolicy**

```php
<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Pool;
use App\Models\User;

final class PoolPolicy
{
    public function view(User $user, Pool $pool): bool
    {
        if (! $pool->isPrivate()) {
            return true;
        }

        return $user->id === $pool->owner_id
            || $pool->participants()->whereKey($user->id)->exists();
    }

    public function update(User $user, Pool $pool): bool
    {
        return $user->id === $pool->owner_id;
    }

    public function delete(User $user, Pool $pool): bool
    {
        return $user->id === $pool->owner_id;
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/PoolPolicyTest.php`
Expected: PASS (4 passed).

- [ ] **Step 5: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Policies/PoolPolicy.php tests/Feature/PoolPolicyTest.php
git commit -m "feat: add pool policy for visibility and ownership"
```

---

## Task 12: Full suite green + phase wrap-up

**Files:** none (verification only)

- [ ] **Step 1: Run the entire test suite**

Run: `docker exec oddly_php php artisan test --compact`
Expected: all tests PASS (Phase 1 suites + the existing `AdminPanelSmokeTest`).

- [ ] **Step 2: Rebuild and reseed to confirm migrations are clean**

Run: `docker exec oddly_php php artisan migrate:fresh --seed --no-interaction`
Expected: all migrations run; existing seeders complete without error (the seeded admin user now has `role = admin`).

- [ ] **Step 3: Confirm formatting is clean**

Run: `vendor/bin/pint --test --format agent`
Expected: no files need changes (or run `vendor/bin/pint --format agent` then commit).

- [ ] **Step 4: Final commit if anything changed**

```bash
git add -A
git commit -m "chore: phase 1 domain green" || echo "nothing to commit"
```

---

## Self-Review (completed during authoring)

- **Spec coverage:** users.role + canAccessPanel (T2); pools + config points (T3); participants + join public/private/code (T4); global score bets + unique (T5); lock enforcement (T6, policy + action); fixture flag resolution + observer + idempotency (T7); champion bonus (T8); group bonus (T9); leaderboard applying pool rules (T10); visibility/ownership policy (T11). Flux UI and Filament admin are explicitly Phases 2–3, not in this plan.
- **Type consistency:** action method names (`handle`), service method (`for`), relation names (`participants`, `bets`, `championBets`, `groupBets`, `pools`, `ownedPools`) are used consistently across tasks. Standings array shape `['user' => ..., 'points' => int]` matches the test assertions.
- **Placeholder scan:** no TBD/TODO; every code step contains full code and exact commands.

## Next phases (separate plans, authored after Phase 1 lands)

- **Phase 2 — Filament admin:** `PoolResource` (CRUD + scoring config + participants relation + standings view), read-only bet views, fixture "Encerrar & pontuar" action, manual "Recalcular pontuação" (incl. `ResolveGroupBetsAction` trigger).
- **Phase 3 — Flux player UI + auth:** register/login/logout, dashboard, pool browse/create/join, score-bet entry respecting lock, champion/group bonus entry, leaderboard.
