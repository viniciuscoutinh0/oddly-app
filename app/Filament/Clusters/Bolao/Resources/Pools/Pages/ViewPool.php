<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Bolao\Resources\Pools\Pages;

use App\Filament\Clusters\Bolao\Resources\Pools\PoolResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewPool extends ViewRecord
{
    protected static string $resource = PoolResource::class;
}
