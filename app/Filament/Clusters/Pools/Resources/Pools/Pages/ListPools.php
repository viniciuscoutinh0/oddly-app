<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Pools\Resources\Pools\Pages;

use App\Filament\Clusters\Pools\Resources\Pools\PoolResource;
use Filament\Resources\Pages\ListRecords;

final class ListPools extends ListRecords
{
    protected static string $resource = PoolResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
