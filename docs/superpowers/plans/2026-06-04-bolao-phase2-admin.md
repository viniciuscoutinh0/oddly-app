# Bolão Phase 2 — Admin (Filament) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a read-only admin oversight area for betting pools in Filament — a dedicated "Bolão" cluster with a read-only PoolResource (list, view, participants) and a per-pool standings page with an idempotent "Recalcular pontuação" action.

**Architecture:** A new Filament cluster `Bolao` holds a read-only `PoolResource` (no create/edit/delete). A custom record page renders the computed `PoolStandings` collection in Blade and exposes a header action that runs `RecalculatePoolScoringAction`, which reuses the Phase 1 resolver actions for the pool's season.

**Tech Stack:** Laravel 12, PHP 8.4, Filament v5, Livewire v4, Pest 4 (+ pest-plugin-livewire). Run artisan/tests inside Docker (`docker exec oddly_php php artisan ...`); host PHP 8.3 fails. Pint on host (`vendor/bin/pint --dirty --format agent`).

---

## Conventions & important notes

- **Environment:** branch `feature/bolao-phase2` is already checked out. All artisan/test commands run via `docker exec oddly_php php artisan ...`. Pint on host.
- **Filament v5 API drift:** Use the existing sibling resources as the source of truth for exact signatures and namespaces — read `app/Filament/Clusters/Tournament/TournamentCluster.php` and `app/Filament/Clusters/Tournament/Resources/Competitions/` (Resource, `Tables/CompetitionsTable.php`, `Pages/*`) before writing code. If a method signature in this plan differs from what those files use, **follow the in-repo pattern and the framework's v5 API** (confirm via the boost `search-docs` MCP tool through ToolSearch if unsure), keeping the same fields and behavior.
- **Namespaces** (per project CLAUDE.md): form/infolist layout in `Filament\Schemas\Components\`; infolist entries in `Filament\Infolists\Components\`; table columns in `Filament\Tables\Columns\`; table filters in `Filament\Tables\Filters\`; actions in `Filament\Actions\`; icons via `Filament\Support\Icons\Heroicon`.
- **Resource property types** must be preserved: `$navigationIcon` is `protected static string | BackedEnum | null`; on a `Page`, `$view` is `protected string` (not static).
- **Read-only:** do NOT register Create/Edit pages or expose create/edit/delete actions on PoolResource.
- **Models/services available from Phase 1:** `App\Models\Pool` (relations `season()`, `owner()`, `participants()` with pivot `joined_at`; integer point columns), `App\Services\PoolStandings::for(Pool): Collection<int,array{user:User,points:int}>`, actions `App\Actions\Bet\ResolveFixtureBetsAction`, `ResolveChampionBetsAction`, `ResolveGroupBetsAction`. Enum `App\Enums\Pool\Visibility` (HasLabel/HasColor). Fixture status enum `App\Enums\Fixture\Status` (Finished). `Season::fixtures()` is HasManyThrough.
- **Tests:** Pest in `tests/Feature` (auto `RefreshDatabase`). Use `use function Pest\Livewire\livewire;` for Filament component tests. Always `actingAs(User::factory()->admin()->create())` before panel tests. Factories: `PoolFactory` (with `public()` state), `BetFactory`, `ChampionBetFactory`, `GroupBetFactory`, plus Season/Stage/Fixture/Team/User.

---

## Task 1: Bolão cluster + read-only PoolResource (list page)

**Files:**
- Create: `app/Filament/Clusters/Bolao/BolaoCluster.php`
- Create: `app/Filament/Clusters/Bolao/Resources/Pools/PoolResource.php`
- Create: `app/Filament/Clusters/Bolao/Resources/Pools/Tables/PoolsTable.php`
- Create: `app/Filament/Clusters/Bolao/Resources/Pools/Pages/ListPools.php`
- Test: `tests/Feature/Bolao/PoolResourceTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Enums\Pool\Visibility;
use App\Filament\Clusters\Bolao\Resources\Pools\Pages\ListPools;
use App\Models\Pool;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

it('lists pools for an admin', function (): void {
    actingAs(User::factory()->admin()->create());
    $pools = Pool::factory()->count(3)->create();

    livewire(ListPools::class)
        ->assertCanSeeTableRecords($pools);
});

it('filters pools by visibility', function (): void {
    actingAs(User::factory()->admin()->create());
    $public = Pool::factory()->public()->create();
    $private = Pool::factory()->create();

    livewire(ListPools::class)
        ->filterTable('visibility', Visibility::Public->value)
        ->assertCanSeeTableRecords([$public])
        ->assertCanNotSeeTableRecords([$private]);
});

it('forbids non-admins from the pools list route', function (): void {
    actingAs(User::factory()->create()); // default role: Player

    get(PoolResourceRoute())->assertForbidden();
});

function PoolResourceRoute(): string
{
    return \App\Filament\Clusters\Bolao\Resources\Pools\PoolResource::getUrl('index');
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Bolao/PoolResourceTest.php`
Expected: FAIL — cluster/resource classes not found.

- [ ] **Step 3: Create the cluster**

Read `app/Filament/Clusters/Tournament/TournamentCluster.php` first and mirror it.

```php
<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Bolao;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Icons\Heroicon;

final class BolaoCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Start;

    protected static ?string $clusterBreadcrumb = 'Bolão';

    public static function getNavigationLabel(): string
    {
        return 'Bolão';
    }
}
```

- [ ] **Step 4: Create the PoolsTable**

```php
<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Bolao\Resources\Pools\Tables;

use App\Enums\Pool\Visibility;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PoolsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('season.name')
                    ->label('Temporada')
                    ->sortable(),

                TextColumn::make('owner.name')
                    ->label('Dono')
                    ->searchable(),

                TextColumn::make('visibility')
                    ->label('Visibilidade')
                    ->badge(),

                TextColumn::make('participants_count')
                    ->label('Participantes')
                    ->counts('participants')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('visibility')
                    ->label('Visibilidade')
                    ->options(Visibility::class),

                SelectFilter::make('season')
                    ->label('Temporada')
                    ->relationship('season', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => $record->name),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
```

Note: no create/bulk/delete actions (read-only). If `recordActions([])`/`toolbarActions([])` is not the v5 method name, follow the sibling `CompetitionsTable` and simply omit action arrays.

- [ ] **Step 5: Create the ListPools page**

```php
<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Bolao\Resources\Pools\Pages;

use App\Filament\Clusters\Bolao\Resources\Pools\PoolResource;
use Filament\Resources\Pages\ListRecords;

final class ListPools extends ListRecords
{
    protected static string $resource = PoolResource::class;

    protected function getHeaderActions(): array
    {
        return []; // read-only: no create action
    }
}
```

- [ ] **Step 6: Create the PoolResource**

```php
<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Bolao\Resources\Pools;

use App\Filament\Clusters\Bolao\BolaoCluster;
use App\Filament\Clusters\Bolao\Resources\Pools\Pages\ListPools;
use App\Filament\Clusters\Bolao\Resources\Pools\Tables\PoolsTable;
use App\Models\Pool;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class PoolResource extends Resource
{
    protected static ?string $model = Pool::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $cluster = BolaoCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Bolão';

    protected static ?string $pluralModelLabel = 'Bolões';

    public static function table(Table $table): Table
    {
        return PoolsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPools::route('/'),
        ];
    }
}
```

- [ ] **Step 7: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Bolao/PoolResourceTest.php`
Expected: PASS (3 passed). If the visibility filter assertion fails because of how the enum option value is passed, confirm the filter value with the sibling resources and adjust the `filterTable` argument (the stored column value is the enum's string value, e.g. `'public'`).

- [ ] **Step 8: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Clusters/Bolao tests/Feature/Bolao/PoolResourceTest.php
git commit -m "feat: add Bolão cluster and read-only pools list"
```

---

## Task 2: Pool view page + infolist

**Files:**
- Create: `app/Filament/Clusters/Bolao/Resources/Pools/Schemas/PoolInfolist.php`
- Create: `app/Filament/Clusters/Bolao/Resources/Pools/Pages/ViewPool.php`
- Modify: `app/Filament/Clusters/Bolao/Resources/Pools/PoolResource.php` (add `infolist()` + register `view` page)
- Test: `tests/Feature/Bolao/ViewPoolTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Filament\Clusters\Bolao\Resources\Pools\Pages\ViewPool;
use App\Models\Pool;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('shows a pool configuration on the view page', function (): void {
    actingAs(User::factory()->admin()->create());
    $pool = Pool::factory()->create([
        'name' => 'Bolão da Firma',
        'points_exact' => 12,
    ]);

    livewire(ViewPool::class, ['record' => $pool->id])
        ->assertOk()
        ->assertSee('Bolão da Firma')
        ->assertSee('12');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Bolao/ViewPoolTest.php`
Expected: FAIL — `ViewPool` not found.

- [ ] **Step 3: Create the PoolInfolist**

Read the sibling pattern for infolists/schemas in the Tournament resources first. Match the v5 `Schema` API.

```php
<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Bolao\Resources\Pools\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class PoolInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bolão')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nome'),
                        TextEntry::make('visibility')->label('Visibilidade')->badge(),
                        TextEntry::make('owner.name')->label('Dono'),
                        TextEntry::make('season.name')->label('Temporada'),
                        TextEntry::make('invite_code')->label('Código de Convite')->placeholder('—'),
                        TextEntry::make('description')->label('Descrição')->placeholder('—')->columnSpanFull(),
                    ]),

                Section::make('Pontuação')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('points_exact')->label('Placar Exato'),
                        TextEntry::make('points_result')->label('Resultado'),
                        TextEntry::make('points_champion')->label('Campeão'),
                        TextEntry::make('points_group_position')->label('Posição no Grupo'),
                    ]),
            ]);
    }
}
```

- [ ] **Step 4: Create the ViewPool page**

```php
<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Bolao\Resources\Pools\Pages;

use App\Filament\Clusters\Bolao\Resources\Pools\PoolResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewPool extends ViewRecord
{
    protected static string $resource = PoolResource::class;
}
```

- [ ] **Step 5: Wire infolist + register the view page in PoolResource**

Add to `PoolResource`:

```php
use App\Filament\Clusters\Bolao\Resources\Pools\Pages\ViewPool;
use App\Filament\Clusters\Bolao\Resources\Pools\Schemas\PoolInfolist;
use Filament\Schemas\Schema;
```

```php
public static function infolist(Schema $schema): Schema
{
    return PoolInfolist::configure($schema);
}
```

Update `getPages()`:

```php
public static function getPages(): array
{
    return [
        'index' => ListPools::route('/'),
        'view' => ViewPool::route('/{record}'),
    ];
}
```

Also add a `view` record action to `PoolsTable` so the table links to it — in `PoolsTable::configure`, set `->recordActions([\Filament\Actions\ViewAction::make()])` (use the v5 actions namespace as in sibling resources).

- [ ] **Step 6: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Bolao/ViewPoolTest.php`
Expected: PASS (1 passed).

- [ ] **Step 7: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Clusters/Bolao tests/Feature/Bolao/ViewPoolTest.php
git commit -m "feat: add pool view page with config infolist"
```

---

## Task 3: Participants relation manager (read-only)

**Files:**
- Create: `app/Filament/Clusters/Bolao/Resources/Pools/RelationManagers/ParticipantsRelationManager.php`
- Modify: `app/Filament/Clusters/Bolao/Resources/Pools/PoolResource.php` (register relation)
- Test: `tests/Feature/Bolao/ParticipantsRelationManagerTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Filament\Clusters\Bolao\Resources\Pools\Pages\ViewPool;
use App\Filament\Clusters\Bolao\Resources\Pools\RelationManagers\ParticipantsRelationManager;
use App\Models\Pool;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('lists pool participants read-only', function (): void {
    actingAs(User::factory()->admin()->create());
    $pool = Pool::factory()->public()->create();
    $member = User::factory()->create(['name' => 'Joana Palpiteira']);
    app(JoinPoolAction::class)->handle($member, $pool);

    livewire(ParticipantsRelationManager::class, [
        'ownerRecord' => $pool,
        'pageClass' => ViewPool::class,
    ])
        ->assertCanSeeTableRecords([$member])
        ->assertSee('Joana Palpiteira');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Bolao/ParticipantsRelationManagerTest.php`
Expected: FAIL — relation manager not found.

- [ ] **Step 3: Create the relation manager**

Read a sibling relation manager (e.g. `app/Filament/Clusters/Tournament/Resources/Seasons/RelationManagers/StagesRelationManager.php`) for the v5 shape.

```php
<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Bolao\Resources\Pools\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    protected static ?string $title = 'Participantes';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Jogador')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),

                TextColumn::make('pivot.joined_at')
                    ->label('Entrou em')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
```

If `pivot.joined_at` does not render, use `->state(fn ($record) => $record->pivot->joined_at?->format('d/m/Y H:i'))` on the column instead.

- [ ] **Step 4: Register the relation manager**

In `PoolResource` add:

```php
use App\Filament\Clusters\Bolao\Resources\Pools\RelationManagers\ParticipantsRelationManager;
```

```php
public static function getRelations(): array
{
    return [
        ParticipantsRelationManager::class,
    ];
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Bolao/ParticipantsRelationManagerTest.php`
Expected: PASS (1 passed).

- [ ] **Step 6: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Clusters/Bolao tests/Feature/Bolao/ParticipantsRelationManagerTest.php
git commit -m "feat: add read-only participants relation manager"
```

---

## Task 4: RecalculatePoolScoringAction

**Files:**
- Create: `app/Actions/Pool/RecalculatePoolScoringAction.php`
- Test: `tests/Feature/Bolao/RecalculatePoolScoringActionTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Actions\Pool\RecalculatePoolScoringAction;
use App\Enums\Fixture\Status;
use App\Models\Bet;
use App\Models\ChampionBet;
use App\Models\Fixture;
use App\Models\GroupBet;
use App\Models\Pool;
use App\Models\Season;
use App\Models\Stage;
use App\Models\Team;
use App\Models\User;
use App\Services\PoolStandings;

it('resolves fixture, champion, and group bets for the pool season', function (): void {
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    $fixture = Fixture::factory()->for($stage)->create([
        'status' => Status::Finished, 'home_score' => 2, 'away_score' => 1,
    ]);

    $user = User::factory()->create();
    $pool = Pool::factory()->public()->create([
        'season_id' => $season->id,
        'points_exact' => 10, 'points_champion' => 25, 'points_group_position' => 3,
    ]);
    $pool->participants()->attach($user->id, ['joined_at' => now()]);

    // Unresolved score bet (flags null), set without firing the observer.
    $bet = Bet::factory()->for($fixture)->create([
        'user_id' => $user->id, 'home_score' => 2, 'away_score' => 1,
        'is_exact' => null, 'is_correct_result' => null,
    ]);

    // Champion: set winner directly (updateQuietly avoids the SeasonObserver) so the
    // action is what resolves the bet.
    $champion = Team::factory()->create();
    $season->updateQuietly(['winner_id' => $champion->id]);
    $championBet = ChampionBet::factory()->for($season)->create([
        'user_id' => $user->id, 'team_id' => $champion->id, 'is_correct' => null,
    ]);

    // Group: actual standings via pivot + a matching bet.
    $groupTeam = Team::factory()->create();
    $season->teams()->attach($groupTeam->id, ['group_letter' => 'A', 'group_position' => 1]);
    $groupBet = GroupBet::factory()->for($season)->create([
        'user_id' => $user->id, 'group_letter' => 'A', 'team_id' => $groupTeam->id,
        'predicted_position' => 1, 'is_correct' => null,
    ]);

    app(RecalculatePoolScoringAction::class)->handle($pool);

    expect($bet->refresh()->is_exact)->toBeTrue()
        ->and($championBet->refresh()->is_correct)->toBeTrue()
        ->and($groupBet->refresh()->is_correct)->toBeTrue();

    // 10 (exact) + 25 (champion) + 3 (group) = 38
    expect(app(PoolStandings::class)->for($pool)->first()['points'])->toBe(38);
});

it('is idempotent', function (): void {
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    $fixture = Fixture::factory()->for($stage)->create([
        'status' => Status::Finished, 'home_score' => 0, 'away_score' => 0,
    ]);
    $pool = Pool::factory()->public()->create(['season_id' => $season->id]);
    $user = User::factory()->create();
    $pool->participants()->attach($user->id, ['joined_at' => now()]);
    Bet::factory()->for($fixture)->create([
        'user_id' => $user->id, 'home_score' => 0, 'away_score' => 0,
    ]);

    $action = app(RecalculatePoolScoringAction::class);
    $action->handle($pool);
    $action->handle($pool);

    $points = app(PoolStandings::class)->for($pool)->first()['points'];
    expect($points)->toBe($pool->points_exact);
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Bolao/RecalculatePoolScoringActionTest.php`
Expected: FAIL — `RecalculatePoolScoringAction` not found.

- [ ] **Step 3: Create the action**

```php
<?php

declare(strict_types=1);

namespace App\Actions\Pool;

use App\Actions\Bet\ResolveChampionBetsAction;
use App\Actions\Bet\ResolveFixtureBetsAction;
use App\Actions\Bet\ResolveGroupBetsAction;
use App\Enums\Fixture\Status;
use App\Models\Pool;
use Illuminate\Support\Facades\DB;

final class RecalculatePoolScoringAction
{
    public function __construct(
        private ResolveFixtureBetsAction $resolveFixtureBets,
        private ResolveChampionBetsAction $resolveChampionBets,
        private ResolveGroupBetsAction $resolveGroupBets,
    ) {}

    public function handle(Pool $pool): void
    {
        $season = $pool->season;

        DB::transaction(function () use ($season): void {
            $season->fixtures()
                ->where('status', Status::Finished)
                ->each(fn ($fixture) => $this->resolveFixtureBets->handle($fixture));

            $this->resolveChampionBets->handle($season);
            $this->resolveGroupBets->handle($season);
        });
    }
}
```

Note: `Season::fixtures()` is HasManyThrough; `->where('status', ...)` filters on the `fixtures` table. `each()` streams in chunks. `ResolveFixtureBetsAction` re-checks `isFinished()` and null scores defensively.

- [ ] **Step 4: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Bolao/RecalculatePoolScoringActionTest.php`
Expected: PASS (2 passed).

- [ ] **Step 5: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Actions/Pool/RecalculatePoolScoringAction.php tests/Feature/Bolao/RecalculatePoolScoringActionTest.php
git commit -m "feat: add recalculate pool scoring action"
```

---

## Task 5: Standings page + recalc header action

**Files:**
- Create: `app/Filament/Clusters/Bolao/Resources/Pools/Pages/PoolStandingsPage.php`
- Create: `resources/views/filament/clusters/bolao/pages/pool-standings.blade.php`
- Modify: `app/Filament/Clusters/Bolao/Resources/Pools/PoolResource.php` (register `standings` page)
- Test: `tests/Feature/Bolao/PoolStandingsPageTest.php`

Note: the page class is named `PoolStandingsPage` to avoid colliding with the `App\Services\PoolStandings` service.

- [ ] **Step 1: Write the failing test**

```php
<?php

declare(strict_types=1);

use App\Enums\Fixture\Status;
use App\Filament\Clusters\Bolao\Resources\Pools\Pages\PoolStandingsPage;
use App\Models\Bet;
use App\Models\Fixture;
use App\Models\Pool;
use App\Models\Season;
use App\Models\Stage;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

it('shows ranked participants with points', function (): void {
    actingAs(User::factory()->admin()->create());

    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    $fixture = Fixture::factory()->for($stage)->create([
        'status' => Status::Finished, 'home_score' => 2, 'away_score' => 1,
    ]);
    $pool = Pool::factory()->public()->create(['season_id' => $season->id, 'points_exact' => 10]);

    $leader = User::factory()->create(['name' => 'Líder']);
    $pool->participants()->attach($leader->id, ['joined_at' => now()]);
    Bet::factory()->for($fixture)->create([
        'user_id' => $leader->id, 'home_score' => 2, 'away_score' => 1,
        'is_exact' => true, 'is_correct_result' => true,
    ]);

    livewire(PoolStandingsPage::class, ['record' => $pool->id])
        ->assertOk()
        ->assertSee('Líder')
        ->assertSee('10');
});

it('recalculates points via the header action', function (): void {
    actingAs(User::factory()->admin()->create());

    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    $fixture = Fixture::factory()->for($stage)->create([
        'status' => Status::Finished, 'home_score' => 1, 'away_score' => 0,
    ]);
    $pool = Pool::factory()->public()->create(['season_id' => $season->id, 'points_exact' => 10]);
    $user = User::factory()->create();
    $pool->participants()->attach($user->id, ['joined_at' => now()]);

    // Unresolved bet (flags null) — created without firing the fixture observer.
    Bet::factory()->for($fixture)->create([
        'user_id' => $user->id, 'home_score' => 1, 'away_score' => 0,
        'is_exact' => null, 'is_correct_result' => null,
    ]);

    livewire(PoolStandingsPage::class, ['record' => $pool->id])
        ->callAction('recalculate')
        ->assertHasNoActionErrors();

    expect(\App\Models\Bet::first()->refresh()->is_exact)->toBeTrue();
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Bolao/PoolStandingsPageTest.php`
Expected: FAIL — `PoolStandingsPage` not found.

- [ ] **Step 3: Create the page**

Read a sibling custom page if one exists; otherwise follow the Filament v5 `Resources\Pages\Page` + `InteractsWithRecord` pattern. Confirm the trait/import names via the framework if needed.

```php
<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Bolao\Resources\Pools\Pages;

use App\Actions\Pool\RecalculatePoolScoringAction;
use App\Filament\Clusters\Bolao\Resources\Pools\PoolResource;
use App\Services\PoolStandings;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Illuminate\Support\Collection;

final class PoolStandingsPage extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PoolResource::class;

    protected string $view = 'filament.clusters.bolao.pages.pool-standings';

    protected static ?string $title = 'Ranking';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    /**
     * @return Collection<int, array{user: \App\Models\User, points: int}>
     */
    public function standings(): Collection
    {
        return app(PoolStandings::class)->for($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('recalculate')
                ->label('Recalcular pontuação')
                ->icon(\Filament\Support\Icons\Heroicon::ArrowPath)
                ->requiresConfirmation()
                ->action(function (): void {
                    app(RecalculatePoolScoringAction::class)->handle($this->record);

                    Notification::make()
                        ->title('Pontuação recalculada.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
```

If `InteractsWithRecord` lives at a different namespace in this Filament v5 build, locate it (grep `vendor/filament` or check a sibling record page) and use the correct one; the behavior (resolve `{record}` route param into `$this->record`) is what matters.

- [ ] **Step 4: Create the Blade view**

```blade
<x-filament-panels::page>
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Jogador</th>
                    <th class="px-4 py-3 text-right">Pontos</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->standings() as $index => $row)
                    <tr class="border-t border-gray-100 dark:border-white/5">
                        <td class="px-4 py-3">{{ $index + 1 }}</td>
                        <td class="px-4 py-3">{{ $row['user']->name }}</td>
                        <td class="px-4 py-3 text-right font-semibold">{{ $row['points'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">
                            Nenhum participante ainda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
```

- [ ] **Step 5: Register the page in PoolResource**

Add import and route:

```php
use App\Filament\Clusters\Bolao\Resources\Pools\Pages\PoolStandingsPage;
```

```php
public static function getPages(): array
{
    return [
        'index' => ListPools::route('/'),
        'view' => ViewPool::route('/{record}'),
        'standings' => PoolStandingsPage::route('/{record}/standings'),
    ];
}
```

To surface it in the record sub-navigation, add to PoolResource (follow the v5 sub-navigation API used by sibling resources; if the project uses `getRecordSubNavigation()`, register `ViewPool` and `PoolStandingsPage` there). This is optional for tests to pass but expected for UX:

```php
public static function getRecordSubNavigation(\Filament\Schemas\Schema|\Filament\Navigation\NavigationItem ...$args): array
{
    return []; // If unsure of the exact v5 signature, skip sub-nav wiring; the route + page still work.
}
```

Keep sub-navigation wiring minimal: if the exact signature is uncertain, omit it (the `standings` route still resolves and the test passes via `livewire(PoolStandingsPage::class, ['record' => ...])`). Do not block the task on sub-nav cosmetics.

- [ ] **Step 6: Run test to verify it passes**

Run: `docker exec oddly_php php artisan test --compact tests/Feature/Bolao/PoolStandingsPageTest.php`
Expected: PASS (2 passed).

- [ ] **Step 7: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Clusters/Bolao resources/views/filament/clusters/bolao tests/Feature/Bolao/PoolStandingsPageTest.php
git commit -m "feat: add pool standings page with recalculate action"
```

---

## Task 6: Full suite green + phase wrap-up

**Files:** none (verification only)

- [ ] **Step 1: Run the entire suite**

Run: `docker exec oddly_php php artisan test --compact`
Expected: all tests PASS (Phase 1 + Phase 2 + existing AdminPanelSmokeTest).

- [ ] **Step 2: Confirm migrations + seed still clean**

Run: `docker exec oddly_php php artisan migrate:fresh --seed --no-interaction`
Expected: clean run, no errors.

- [ ] **Step 3: Confirm formatting clean on changed files**

Run: `vendor/bin/pint --dirty --format agent`
Expected: no changes (or it fixes and you commit).

- [ ] **Step 4: Final commit if anything changed**

```bash
git add -A
git commit -m "chore: phase 2 admin green" || echo "nothing to commit"
```

---

## Self-Review (completed during authoring)

- **Spec coverage:** Bolão cluster (T1); read-only PoolResource list + filters + no create/edit/delete (T1); view infolist with config + scoring (T2); read-only participants relation manager (T3); RecalculatePoolScoringAction reusing Phase 1 resolvers, idempotent, eager path (T4); custom per-pool standings page rendering `PoolStandings` + "Recalcular pontuação" header action (T5); full green (T6). No individual-bet inspection (out of scope) — correctly absent.
- **Type/name consistency:** page class `PoolStandingsPage` (distinct from `App\Services\PoolStandings`); `RecalculatePoolScoringAction::handle(Pool): void`; relation `participants`; resource pages keyed `index`/`view`/`standings`. Tests reference these exact names.
- **Placeholder scan:** no TBD/TODO; every code step has full code + exact commands. Filament v5 API-drift notes instruct following in-repo siblings rather than leaving anything unspecified.
- **Risk note:** Filament v5 method signatures (infolist/sub-nav/InteractsWithRecord namespace) may differ slightly; each such step points the implementer at the concrete sibling file and the framework to confirm, keeping behavior fixed.
```
