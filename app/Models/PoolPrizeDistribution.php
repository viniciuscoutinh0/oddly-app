<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PoolPrizeDistribution extends Model
{
    /** @use HasFactory<\Database\Factories\PoolPrizeDistributionFactory> */
    use HasFactory;

    public function pool(): BelongsTo
    {
        return $this->belongsTo(Pool::class);
    }
}
