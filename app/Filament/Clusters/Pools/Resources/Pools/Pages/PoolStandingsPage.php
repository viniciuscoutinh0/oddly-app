<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Pools\Resources\Pools\Pages;

use App\Actions\Pool\RecalculatePoolScoringAction;
use App\Filament\Clusters\Pools\Resources\Pools\PoolResource;
use App\Services\Pool\PoolStandings;
use App\Services\Pool\ValueObjects\UserStanding;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

final class PoolStandingsPage extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PoolResource::class;

    protected string $view = 'filament.clusters.pools.pages.pool-standings';

    protected static ?string $title = 'Ranking';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    /**
     * @return Collection<int, UserStanding>
     */
    #[Computed]
    public function standings(): Collection
    {
        return app(PoolStandings::class)->for($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('recalculate')
                ->label('Recalcular pontuação')
                ->icon(Heroicon::ArrowPath)
                ->requiresConfirmation()
                ->action(function (): void {
                    app(RecalculatePoolScoringAction::class)->handle($this->record);

                    Notification::make()
                        ->title('Pontuação recalculada.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
