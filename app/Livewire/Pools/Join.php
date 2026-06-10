<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Pool\JoinPoolAction;
use App\Models\Pool;
use Flux\Flux;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class Join extends Component
{
    #[Validate('required|string')]
    public ?string $code = null;

    public function join(JoinPoolAction $action): void
    {
        $validated = $this->validate();

        $pool = Pool::where('invite_code', $validated['code'])->first();

        if ($pool === null) {
            $this->addError('code', 'Código de convite inválido.');

            return;
        }

        try {
            $action->handle(Auth::user(), $pool, $validated['code']);
        } catch (InvalidArgumentException $e) {
            $this->addError('code', $e->getMessage());

            return;
        }

        $this->reset();

        Flux::toast(
            heading: 'Boa sorte! 🍀',
            text: 'Você já está no grupo '.$pool->name.'. Que comecem os palpites!',
            variant: 'success',
        );

        $this->redirectRoute('pools.show', $pool, navigate: true);
    }

    public function render(): View|Factory
    {
        return view('livewire.pools.join');
    }
}
