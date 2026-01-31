<?php

declare(strict_types=1);

use Livewire\Livewire;
use function Pest\Laravel\actingAs;

it('should render the login component successfully', function (): void {
    Livewire::test('auth.login')
        ->assertStatus(200);
});

it('should display login form with email and password fields', function (): void {
    Livewire::test('auth.login')
        ->assertSee('form')
        ->assertSee('email')
        ->assertSee('password');
});

it('should redirect authenticated users to home page', function (): void {
    $user = App\Models\User::factory()->create();

    $response = actingAs($user);

    $response->get(uri: route('login'))
        ->assertStatus(302)
        ->assertRedirect(uri: route('static.home'));
});

it('should require email and password fields', function (): void {
    Livewire::test('auth.login')
        ->set('email', '')
        ->set('password', '')
        ->call('login')
        ->assertHasErrors([
            'email' => 'required',
            'password' => 'required',
        ]);
});

it('should validate email format', function (): void {
    Livewire::test('auth.login')
        ->set('email', 'foo-bar')
        ->set('password', 'foo')
        ->call('login')
        ->assertHasErrors([
            'email' => 'email',
        ]);
});

it('should fail login with incorrect password', function (): void {
    $user = App\Models\User::factory()->create([
        'email' => 'foo@bar.com',
        'password' => bcrypt('password123'),
    ]);

    Livewire::test('auth.login')
        ->set('email', $user->email)
        ->set('password', 'wrongpassword')
        ->call('login')
        ->assertSet('password', '')
        ->assertHasErrors([
            'email' => 'As credenciais fornecidas estão incorretas.',
        ]);
});

it('should login successfully with correct credentials', function (): void {
    App\Models\User::factory()->create([
        'email' => 'john@due.com',
        'password' => bcrypt('securepassword'),
    ]);

    Livewire::test('auth.login')
        ->set('email', 'john@due.com')
        ->set('password', 'securepassword')
        ->call('login')
        ->assertRedirect(uri: route('static.home'));
});
