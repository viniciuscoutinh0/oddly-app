<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Pool\LeavePoolAction;
use App\Models\Pool;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class Leave extends Component
{
    #[Locked]
    public Pool $pool;

    public function leave(LeavePoolAction $action): void
    {
        $this->authorize('leave', $this->pool);

        $action->handle(Auth::user(), $this->pool);

        $this->redirectRoute('dashboard');
    }

    public function render(): View|Factory
    {
        return view('livewire.pools.leave');
    }
}
