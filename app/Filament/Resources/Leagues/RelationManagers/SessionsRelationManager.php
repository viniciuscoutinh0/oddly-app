<?php

namespace App\Filament\Resources\Leagues\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns;
use Filament\Tables\Table;

class SessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Components\TextInput::make('year')
                    ->label('Ano')
                    ->required()
                    ->maxLength(9),

                Components\Toggle::make('is_current')
                    ->label('Atual?'),

                Components\Repeater::make('stages')
                    ->relationship()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Components\TextInput::make('name')
                                    ->label('Nome da Fase/Rodada')
                                    ->placeholder('Ex: Rodada 1, Quartas de Final')
                                    ->required()
                                    ->columnSpan(2),

                                Components\TextInput::make('external_id')
                                    ->label('External')
                                    ->placeholder('Ex: Rodada 1, Quartas de Final')
                                    ->required(),
                            ]),

                        Components\Toggle::make('is_knockout')
                            ->label('Mata-mata (Prorrogação/Pênaltis)?')
                            ->inline(false),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('year')
            ->columns([
                Columns\TextColumn::make('year')
                    ->label('Ano')
                    ->searchable(),

                Columns\TextColumn::make('stages.name')
                    ->label('Fases')
                    ->badge()
                    ->searchable(),

                Columns\ToggleColumn::make('is_current')
                    ->label('Atual?')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                // AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                // DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
