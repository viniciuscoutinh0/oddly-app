<?php

namespace App\Filament\Clusters\Tournament\Resources\Teams;

use App\Filament\Clusters\Tournament\Resources\Teams\Pages\ManageTeams;
use App\Filament\Clusters\Tournament\TournamentCluster;
use App\Models\Team;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns;
use Filament\Tables\Table;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = TournamentCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Components\TextInput::make('name')
                    ->required()
                    ->maxLength(60)
                    ->live(onBlur: true)
                    ->afterStateUpdatedJs(<<<'JS'
                            $set('tla', $get('name')?.toUpperCase().substring(0,3));
                        JS),

                Components\TextInput::make('short_name')
                    ->label('Nome Curto')
                    ->required()
                    ->maxLength(60),

                Components\TextInput::make('tla')
                    ->label('TLA')
                    ->required()
                    ->maxLength(4),

                Components\TextInput::make('logo_url')
                    ->label('Logo')
                    ->required()
                    ->url()
                    ->prefixIcon(Heroicon::Link)
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\ImageColumn::make('logo_url')
                    ->label('')
                    ->circular(),

                Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Columns\TextColumn::make('tla')
                    ->label('TLA')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth(Width::Large),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTeams::route('/'),
        ];
    }
}
