<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Pool\JoinPoolAction;
use App\Enums\Pool\Visibility;
use App\Models\Pool;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.dashboard')]
final class Browse extends Component
{
    #[Validate('nullable|string')]
    public string $inviteCode = '';

    public function join(int $poolId, JoinPoolAction $action): void
    {
        $pool = Pool::where('visibility', Visibility::Public)->findOrFail($poolId);

        $action->handle(auth()->user(), $pool);

        $this->redirectRoute('pools.show', $pool);
    }

    public function joinByCode(JoinPoolAction $action): void
    {
        $pool = Pool::where('invite_code', $this->inviteCode)->first();

        if ($pool === null) {
            $this->addError('inviteCode', 'Código de convite inválido.');

            return;
        }

        try {
            $action->handle(auth()->user(), $pool, $this->inviteCode);
        } catch (InvalidArgumentException $e) {
            $this->addError('inviteCode', $e->getMessage());

            return;
        }

        $this->redirectRoute('pools.show', $pool);
    }

    /**
     * @return Collection<int, Pool>
     */
    public function pools(): Collection
    {
        return Pool::query()
            ->where('visibility', Visibility::Public)
            ->withCount('participants')
            ->with('season')
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.pools.browse', ['pools' => $this->pools()]);
    }
}
