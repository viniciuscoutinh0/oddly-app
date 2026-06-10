<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Pools\Resources\Pools\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class PoolInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bolão')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nome'),
                        // TextEntry::make('visibility')->label('Visibilidade')->badge(),
                        TextEntry::make('owner.name')->label('Dono'),
                        TextEntry::make('season.name')->label('Temporada'),
                        TextEntry::make('invite_code')->label('Código de Convite')->placeholder('—'),
                        TextEntry::make('description')->label('Descrição')->placeholder('—')->columnSpanFull(),
                    ]),

                Section::make('Pontuação')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('points_exact')->label('Placar Exato'),
                        TextEntry::make('points_result')->label('Resultado'),
                        TextEntry::make('points_champion')->label('Campeão'),
                        TextEntry::make('points_group_position')->label('Posição no Grupo'),
                    ]),
            ]);
    }
}
