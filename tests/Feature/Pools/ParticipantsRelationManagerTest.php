<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Filament\Clusters\Pools\Resources\Pools\Pages\ViewPool;
use App\Filament\Clusters\Pools\Resources\Pools\RelationManagers\ParticipantsRelationManager;
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
