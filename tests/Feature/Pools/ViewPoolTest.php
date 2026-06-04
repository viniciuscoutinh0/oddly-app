<?php

declare(strict_types=1);

use App\Filament\Clusters\Pools\Resources\Pools\Pages\ViewPool;
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
