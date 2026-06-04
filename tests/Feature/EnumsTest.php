<?php

declare(strict_types=1);

use App\Enums\Pool\Visibility;
use App\Enums\User\Role;

it('exposes role cases with labels', function (): void {
    expect(Role::Admin->value)->toBe('admin')
        ->and(Role::Player->value)->toBe('player')
        ->and(Role::Admin->getLabel())->toBe('Administrador');
});

it('exposes visibility cases with labels', function (): void {
    expect(Visibility::Public->value)->toBe('public')
        ->and(Visibility::Private->value)->toBe('private')
        ->and(Visibility::Private->getLabel())->toBe('Privado');
});
