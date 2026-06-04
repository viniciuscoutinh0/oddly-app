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
        ->set('name', 'Zé')->set('email', 'not-an-email')
        ->set('password', 'password123')->set('password_confirmation', 'password123')
        ->call('register')->assertHasErrors(['email' => 'email']);

    Livewire::test(Register::class)
        ->set('name', 'Zé')->set('email', 'taken@example.com')
        ->set('password', 'password123')->set('password_confirmation', 'password123')
        ->call('register')->assertHasErrors(['email' => 'unique']);
});

it('requires a matching, min-8 password confirmation', function (): void {
    Livewire::test(Register::class)
        ->set('name', 'Zé')->set('email', 'ze@example.com')
        ->set('password', 'short')->set('password_confirmation', 'mismatch')
        ->call('register')->assertHasErrors(['password']);
});

it('registers, logs in as a player, and redirects to dashboard', function (): void {
    Livewire::test(Register::class)
        ->set('name', 'Zé Palpiteiro')->set('email', 'ze@example.com')
        ->set('password', 'password123')->set('password_confirmation', 'password123')
        ->call('register')
        ->assertHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $user = User::where('email', 'ze@example.com')->first();
    expect($user)->not->toBeNull()->and($user->role)->toBe(Role::Player);
    $this->assertAuthenticatedAs($user);
});
