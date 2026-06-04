<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Competition\Type;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Competition extends Model
{
    /** @use HasFactory<\Database\Factories\CompetitionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'type' => Type::class,
            'external_id' => 'integer',
        ];
    }

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }
}
