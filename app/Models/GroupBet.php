<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GroupBetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class GroupBet extends Model
{
    /** @use HasFactory<GroupBetFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'predicted_position' => 'integer',
            'is_correct' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
