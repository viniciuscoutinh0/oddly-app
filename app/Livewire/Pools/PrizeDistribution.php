<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Livewire\Forms\PrizeDistributionForm;
use App\Models\Pool;
use Flux\Flux;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class PrizeDistribution extends Component
{
    #[Locked]
    public Pool $pool;

    public PrizeDistributionForm $form;

    public function mount(): void
    {
        $this->form->setFromPool($this->pool);
    }

    #[Computed]
    public function totalAward(): int
    {
        return $this->pool->totalAward();
    }

    public function save(): void
    {
        $this->form->pruneEmpty();

        try {
            $this->authorize('isOwner', $this->pool);

            $data = $this->form->validate();

            DB::transaction(function () use ($data): void {
                $this->pool->distributions()->delete();

                $this->pool
                    ->distributions()
                    ->createMany(
                        collect($data['distributions'])->map(fn (int $percentage, int $position): array => [
                            'position' => $position,
                            'percentage' => $percentage,
                        ]),
                    );
            });
        } catch (AuthorizationException) {
            Flux::toast(
                heading: 'Acesso negado 🚫',
                text: 'Apenas o criador do bolão pode alterar a premiação.',
                variant: 'danger',
            );

            return;
        } catch (ValidationException $exception) {
            Flux::toast(
                heading: 'A conta não fechou! 🧮',
                text: 'Revise os valores e as posições antes de salvar.',
                variant: 'danger',
            );

            throw $exception;
        } catch (QueryException) {
            Flux::toast(
                heading: 'Instabilidade em campo 🪵',
                text: 'Não conseguimos salvar a premiação agora. Tente novamente em instantes.',
                variant: 'danger',
            );

            return;
        }

        Flux::toast(
            heading: 'Premiação definida! 🏆',
            text: 'A divisão do prêmio foi salva com sucesso.',
            variant: 'success',
        );

        Flux::modal('prize-distribution-manager')->close();

        $this->dispatch('prize-distribution::saved');
    }

    public function render(): View|Factory
    {
        return view('livewire.pools.prize-distribution');
    }
}
