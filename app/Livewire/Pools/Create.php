<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Pool\CreatePoolAction;
use App\Enums\Pool\Visibility;
use App\Livewire\Forms\PoolForm;
use App\Models\Competition;
use App\Models\Season;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Create extends Component
{
    public PoolForm $form;

    public function mount(?Competition $competition = null): void
    {
        $this->form->setCompetition($competition);
    }

    public function create(CreatePoolAction $action): void
    {
        $data = $this->form->validate();

        $pool = $action->handle(Auth::user(), $data);

        Flux::toast(
            heading: 'Bolão criado! 🏆',
            text: 'O grupo "'.$pool->name.'" está pronto. Hora de convidar a galera!',
            variant: 'success',
        );

        $this->redirectRoute('pools.show', $pool, navigate: true);
    }

    #[Computed]
    public function competitions(): Collection
    {
        return Competition::query()->get(['id', 'name']);
    }

    #[Computed]
    public function seasons(): Collection
    {
        return Season::query()
            ->when($this->form->competition_id, fn (Builder $query, int $id): Builder => $query->where(
                'competition_id',
                $id,
            ))
            ->get();
    }

    #[Computed]
    public function visibilities(): array
    {
        return Visibility::cases();
    }

    public function render(): View
    {
        return view('livewire.pools.create');
    }
}
