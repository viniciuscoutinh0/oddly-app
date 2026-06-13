<?php

use App\Models\Pool;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pool_prize_distributions', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Pool::class)->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->unsignedTinyInteger('percentage');
            $table->timestamps();

            $table->unique(['pool_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pool_prize_distributions');
    }
};
