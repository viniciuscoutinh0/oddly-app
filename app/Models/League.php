<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\League\Type;
use App\Observers\LeagueObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[ObservedBy(LeagueObserver::class)]
final class League extends Model
{
    /** @use HasFactory<\Database\Factories\LeagueFactory> */
    use HasFactory;

    #[Override]
    protected function casts(): array
    {
        return [
            'type' => Type::class,
        ];
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
