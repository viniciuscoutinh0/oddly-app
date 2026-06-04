<?php

namespace App\Filament\Clusters\Tournament\Resources\Fixtures;

use App\Filament\Clusters\Tournament\Resources\Fixtures\Pages\CreateFixture;
use App\Filament\Clusters\Tournament\Resources\Fixtures\Pages\EditFixture;
use App\Filament\Clusters\Tournament\Resources\Fixtures\Pages\ListFixtures;
use App\Filament\Clusters\Tournament\Resources\Fixtures\Schemas\FixtureForm;
use App\Filament\Clusters\Tournament\Resources\Fixtures\Tables\FixturesTable;
use App\Filament\Clusters\Tournament\TournamentCluster;
use App\Models\Fixture;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FixtureResource extends Resource
{
    protected static ?string $model = Fixture::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = TournamentCluster::class;

    public static function getRecordTitle(?Model $record): ?string
    {
        if (! $record instanceof Fixture) {
            return null;
        }

        return sprintf(
            '%s x %s',
            $record->homeTeam?->name ?? 'A definir',
            $record->awayTeam?->name ?? 'A definir',
        );
    }

    public static function form(Schema $schema): Schema
    {
        return FixtureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FixturesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFixtures::route('/'),
            'create' => CreateFixture::route('/create'),
            'edit' => EditFixture::route('/{record}/edit'),
        ];
    }
}
