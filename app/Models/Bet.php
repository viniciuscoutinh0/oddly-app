<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Bet extends Model
{
    /** @use HasFactory<BetFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_exact' => 'boolean',
            'is_correct_result' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fixture(): BelongsTo
    {
        return $this->belongsTo(Fixture::class);
    }
}
