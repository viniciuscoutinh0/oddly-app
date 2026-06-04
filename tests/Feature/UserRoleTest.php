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
