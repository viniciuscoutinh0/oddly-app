<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('renders the public home page', function (): void {
    get('/')->assertOk();
});
