<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Models\Pool;
use App\Models\PoolPrizeDistribution;
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

    public array $distributions = [];

    protected function rules(): array
    {
        return [
            'distributions' => ['required', 'array'],
            'distributions.*' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function mount(): void
    {
        $this->distributions = $this->pool
            ->distributions
            ->mapWithKeys(fn (PoolPrizeDistribution $distribution): array => [
                $distribution->position => $distribution->percentage,
            ])
            ->all();
    }

    #[Computed]
    public function totalAward(): int
    {
        return $this->pool->totalAward();
    }

    public function save(): void
    {
        $this->distributions = collect($this->distributions)
            ->filter(fn (?int $value): bool => filled($value) || $value !== 0)
            ->all();

        try {
            $this->authorize('isOwner', $this->pool);

            $data = $this->validate();

            DB::beginTransaction();

            $this->pool->distributions()->delete();

            $this->pool
                ->distributions()
                ->createMany(
                    collect($data['distributions'])->map(fn (int $percentage, int $position): array => [
                        'position' => $position,
                        'percentage' => $percentage,
                    ]),
                );

            DB::commit();
        } catch (AuthorizationException) {
            Flux::toast(
                heading: 'Você não tem permissão',
                text: 'Você não tem permissão para executar está ação.',
                variant: 'danger',
            );

            return;
        } catch (ValidationException $exception) {
            DB::rollBack();

            Flux::toast(
                heading: 'Verifique as posições ⚠️',
                text: 'Alguns valores estão incorretos. Revise a distribuição antes de salvar.',
                variant: 'danger',
            );

            throw $exception;
        } catch (QueryException $exception) {
            DB::rollBack();

            Flux::toast(
                heading: 'Não foi possível salvar ⚠️',
                text: 'Houve um problema ao salvar a premiação. Por favor, tente novamente.',
                variant: 'danger',
            );

            return;
        }

        Flux::toast(
            heading: 'Tudo pronto para o pódio! 🎯',
            text: 'A premiação foi dividida e salva com sucesso.',
            variant: 'success',
        );

        Flux::modal('prize-distribution-manager')->close();
    }

    public function render(): View|Factory
    {
        return view('livewire.pools.prize-distribution');
    }
}
