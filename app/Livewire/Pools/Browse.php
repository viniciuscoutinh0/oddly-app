<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Pool\JoinPoolAction;
use App\Enums\Pool\Visibility;
use App\Models\Pool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Browse extends Component
{
    #[Url]
    public ?string $search = null;

    public function join(int $poolId, JoinPoolAction $action): void
    {
        $pool = Pool::where('visibility', Visibility::Public)->findOrFail($poolId);

        $action->handle(Auth::user(), $pool);

        $this->redirectRoute('pools.show', $pool);
    }

    #[Computed]
    public function pools(): Collection
    {
        return Pool::query()
            ->where('visibility', Visibility::Public)
            ->withCount('participants')
            ->with([
                'season' => fn ($query) => $query
                    ->with('competition:id,name')
                    ->withCount([
                        'fixtures',
                        'fixtures as fixtures_finished_count' => fn (Builder $query): Builder => $query->finished(),
                    ]),
            ])
            ->when($this->search, fn (Builder $query, string $value): Builder => $query->whereLike(
                'name',
                '%'.$value.'%',
            ))
            ->latest()
            ->get([
                'id',
                'name',
            ]);
    }

    public function render(): View
    {
        return view('livewire.pools.browse');
    }
}
