<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Pool\CreatePoolAction;
use App\Enums\Pool\Visibility;
use App\Models\Competition;
use App\Models\Season;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Create extends Component
{
    #[Locked]
    public ?Competition $competition = null;

    #[Validate('required|string|max:255')]
    public string $name = '';

    public ?string $description = null;

    #[Validate('required|exists:seasons,id')]
    public ?int $season_id = null;

    #[Validate('required')]
    public string $visibility = 'private';

    #[Validate('required|integer|min:0')]
    public int $points_exact = 10;

    #[Validate('required|integer|min:0')]
    public int $points_result = 5;

    #[Validate('required|integer|min:0')]
    public int $points_champion = 25;

    #[Validate('required|integer|min:0')]
    public int $points_group_position = 3;

    public function create(CreatePoolAction $action): void
    {
        $data = $this->validate();
        $data['visibility'] = Visibility::from($this->visibility);

        $pool = $action->handle(auth()->user(), $data);

        $this->redirectRoute('pools.show', $pool);
    }

    /**
     * @return Collection<int, Season>
     */
    public function seasons(): Collection
    {
        return Season::query()->get();
    }

    public function render(): View
    {
        return view('livewire.pools.create', ['seasons' => $this->seasons()]);
    }
}
