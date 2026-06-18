<?php

declare(strict_types=1);

use App\Livewire\Pools\PrizeDistribution;
use App\Models\Pool;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

it('prefills the form with existing distributions', function (): void {
    $pool = Pool::factory()->public()->create();

    $pool->distributions()->createMany([
        ['position' => 1, 'percentage' => 70],
        ['position' => 2, 'percentage' => 30],
    ]);

    actingAs($pool->owner);

    Livewire::test(PrizeDistribution::class, ['pool' => $pool])
        ->assertSet('form.distributions', [1 => 70, 2 => 30]);
});

it('lets the owner save a valid distribution', function (): void {
    $pool = Pool::factory()->public()->create();

    actingAs($pool->owner);

    Livewire::test(PrizeDistribution::class, ['pool' => $pool])
        ->set('form.distributions', [1 => 60, 2 => 40])
        ->call('save')
        ->assertHasNoErrors();

    expect($pool->distributions()->pluck('percentage', 'position')->all())
        ->toBe([1 => 60, 2 => 40]);
});

it('forbids a non-owner from saving', function (): void {
    $pool = Pool::factory()->public()->create();

    actingAs(User::factory()->create());

    Livewire::test(PrizeDistribution::class, ['pool' => $pool])
        ->set('form.distributions', [1 => 60, 2 => 40])
        ->call('save');

    expect($pool->distributions()->count())->toBe(0);
});

it('rejects a distribution that does not sum to 100', function (): void {
    $pool = Pool::factory()->public()->create();

    actingAs($pool->owner);

    Livewire::test(PrizeDistribution::class, ['pool' => $pool])
        ->set('form.distributions', [1 => 60, 2 => 30])
        ->call('save')
        ->assertHasErrors('form.distributions');

    expect($pool->distributions()->count())->toBe(0);
});

it('rejects non-sequential positions', function (): void {
    $pool = Pool::factory()->public()->create();

    actingAs($pool->owner);

    Livewire::test(PrizeDistribution::class, ['pool' => $pool])
        ->set('form.distributions', [1 => 50, 3 => 50])
        ->call('save')
        ->assertHasErrors('form.distributions');

    expect($pool->distributions()->count())->toBe(0);
});

it('prunes empty positions before validating', function (): void {
    $pool = Pool::factory()->public()->create();

    actingAs($pool->owner);

    Livewire::test(PrizeDistribution::class, ['pool' => $pool])
        ->set('form.distributions', [1 => 60, 2 => 40, 3 => 0])
        ->call('save')
        ->assertHasNoErrors();

    expect($pool->distributions()->pluck('percentage', 'position')->all())
        ->toBe([1 => 60, 2 => 40]);
});

it('rejects a percentage above 100', function (): void {
    $pool = Pool::factory()->public()->create();

    actingAs($pool->owner);

    Livewire::test(PrizeDistribution::class, ['pool' => $pool])
        ->set('form.distributions', [1 => 150])
        ->call('save')
        ->assertHasErrors('form.distributions.1');

    expect($pool->distributions()->count())->toBe(0);
});
