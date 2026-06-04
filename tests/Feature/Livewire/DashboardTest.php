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

    Pool::factory()->create(['owner_id' => $user->id, 'name' => 'Meu Bolao']);
    $joined = Pool::factory()->public()->create(['name' => 'Bolao Aberto']);
    app(JoinPoolAction::class)->handle($user, $joined);
    Pool::factory()->public()->create(['name' => 'Bolao Alheio']);

    Livewire::test(Dashboard::class)
        ->assertOk()
        ->assertSee('Meu Bolao')
        ->assertSee('Bolao Aberto')
        ->assertDontSee('Bolao Alheio');
});

it('shows an empty state when the user has no pools', function (): void {
    actingAs(User::factory()->create());

    Livewire::test(Dashboard::class)
        ->assertSee('Criar bolão')
        ->assertSee('Entrar em bolão');
});

it('links the empty-state buttons to the pool routes', function (): void {
    actingAs(User::factory()->create());

    Livewire::test(Dashboard::class)
        ->assertSee(route('pools.create'))
        ->assertSee(route('pools.index'));
});
