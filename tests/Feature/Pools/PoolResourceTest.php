<?php

declare(strict_types=1);

use App\Enums\Pool\Visibility;
use App\Filament\Clusters\Pools\Resources\Pools\Pages\ListPools;
use App\Filament\Clusters\Pools\Resources\Pools\PoolResource;
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

it('sorts pools by season without error', function (): void {
    actingAs(User::factory()->admin()->create());
    Pool::factory()->count(2)->create();

    livewire(ListPools::class)
        ->sortTable('season.name')
        ->assertOk();
});

it('forbids non-admins from the pools list route', function (): void {
    actingAs(User::factory()->create());

    get(PoolResource::getUrl('index'))->assertForbidden();
});
