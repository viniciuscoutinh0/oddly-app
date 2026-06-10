<?php

declare(strict_types=1);

namespace App\Services\Pool\Contracts;

use App\Models\Pool;
use Illuminate\Support\Collection;

interface PointSource
{
    public function pointsFor(Pool $pool, Collection $ids): Collection;
}
