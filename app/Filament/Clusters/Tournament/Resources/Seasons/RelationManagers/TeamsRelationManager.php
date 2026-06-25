<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Tournament\Resources\Seasons\RelationManagers;

use App\Actions\Bet\ResolveGroupBetsAction;
use App\Models\Team;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class TeamsRelationManager extends RelationManager
{
    protected static string $relationship = 'teams';

    protected static ?string $title = 'Classificação por Grupo';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pivot.group_letter')
                    ->label('Grupo')
                    ->badge()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Time')
                    ->searchable(),

                TextColumn::make('pivot.group_position')
                    ->label('Posição')
                    ->placeholder('—'),
            ])
            ->defaultGroup(
                Group::make('group_letter')
                    ->label('Grupo')
                    ->getTitleFromRecordUsing(fn (Team $record): string => "Grupo {$record->pivot->group_letter}")
                    ->groupQueryUsing(
                        fn (Builder $query): Builder => $query->orderByRaw(
                            'season_teams.group_letter, season_teams.group_position',
                        ),
                    )
                    ->orderQueryUsing(
                        fn (Builder $query): Builder => $query->orderByRaw(
                            'season_teams.group_letter, season_teams.group_position',
                        ),
                    ),
            )
            ->headerActions([
                Action::make('resolveGroupBets')
                    ->label('Resolver Palpites de Grupo')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->requiresConfirmation()
                    ->action(function (): void {
                        app(ResolveGroupBetsAction::class)->handle($this->getOwnerRecord());

                        Notification::make()
                            ->title('Palpites de grupo resolvidos com sucesso')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('define_position')
                    ->label('Definir Posição')
                    ->icon(Heroicon::PencilSquare)
                    ->modalWidth(Width::Small)
                    ->schema([
                        TextInput::make('group_position')
                            ->label('Posição no Grupo')
                            ->integer()
                            ->minValue(1)
                            ->maxLength(4)
                            ->required()
                            ->placeholder('Exemplo: 1'),
                    ])
                    ->fillForm(fn (Team $record): array => [
                        'group_position' => $record->pivot->group_position,
                    ])
                    ->action(function (Team $record, array $data): void {
                        $this
                            ->getOwnerRecord()
                            ->teams()
                            ->updateExistingPivot(
                                $record->id,
                                ['group_position' => $data['group_position']],
                            );
                    }),
            ]);
    }
}
