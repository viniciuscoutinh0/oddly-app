<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Bolao\Resources\Pools;

use App\Filament\Clusters\Bolao\BolaoCluster;
use App\Filament\Clusters\Bolao\Resources\Pools\Pages\ListPools;
use App\Filament\Clusters\Bolao\Resources\Pools\Tables\PoolsTable;
use App\Models\Pool;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class PoolResource extends Resource
{
    protected static ?string $model = Pool::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $cluster = BolaoCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Bolão';

    protected static ?string $pluralModelLabel = 'Bolões';

    public static function table(Table $table): Table
    {
        return PoolsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPools::route('/'),
        ];
    }
}
