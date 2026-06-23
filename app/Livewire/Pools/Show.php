<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Models\Pool;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Show extends Component
{
    #[Locked]
    public Pool $pool;

    public function mount(Pool $pool): void
    {
        abort_unless(Gate::allows('view', $pool), code: 403);

        $this->pool = $pool
            ->load([
                'season' => fn ($builder) => $builder->with(['competition', 'teams']),
                'owner:id,name',
            ])
            ->loadCount('participants');
    }

    /**
     * When the bonus predictions lock (kick-off of the earliest fixture).
     */
    #[Computed]
    public function bonusLocksAt(): ?CarbonInterface
    {
        return $this->pool->season->bonusLocksAt();
    }

    #[Computed]
    public function bonusLocked(): bool
    {
        return $this->bonusLocksAt !== null && now()->gte($this->bonusLocksAt);
    }

    public function render(): View
    {
        return view('livewire.pools.show');
    }
}
