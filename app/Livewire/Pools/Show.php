<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Pool\LeavePoolAction;
use App\Models\Pool;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Show extends Component
{
    public Pool $pool;

    public function mount(Pool $pool): void
    {
        abort_unless(Gate::allows('view', $pool), 403);

        $this->pool = $pool->load('season', 'participants', 'owner');
    }

    public function isMember(): bool
    {
        return $this->pool->participants->contains(auth()->id());
    }

    public function isOwner(): bool
    {
        return $this->pool->owner_id === auth()->id();
    }

    public function canSeeInviteCode(): bool
    {
        return $this->pool->invite_code !== null && ($this->isOwner() || $this->isMember());
    }

    public function canLeave(): bool
    {
        return ! $this->isOwner() && $this->isMember();
    }

    public function leave(LeavePoolAction $action): void
    {
        abort_unless($this->canLeave(), 403);

        $action->handle(auth()->user(), $this->pool);

        $this->redirectRoute('dashboard');
    }

    public function render(): View
    {
        return view('livewire.pools.show');
    }
}
