<?php

declare(strict_types=1);

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('logs out an authenticated user and redirects home', function (): void {
    actingAs(User::factory()->create());

    get('/logout')->assertRedirect(route('static.home'));

    $this->assertGuest();
});

it('redirects guests hitting logout to login', function (): void {
    get('/logout')->assertRedirect(route('login'));
});
