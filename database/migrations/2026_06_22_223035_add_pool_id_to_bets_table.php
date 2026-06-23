<?php

use App\Models\Pool;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bets', function (Blueprint $table): void {
            $table->foreignIdFor(Pool::class)->nullable()->constrained()->cascadeOnDelete();
            $table->dropUnique(['user_id', 'fixture_id']);
            $table->unique(['user_id', 'fixture_id', 'pool_id']);
        });
    }

    public function down(): void
    {
        Schema::table('bets', function (Blueprint $table): void {
            $table->dropForeignIdFor(Pool::class);
            $table->dropUnique(['user_id', 'fixture_id', 'pool_id']);
            $table->unique(['user_id', 'fixture_id']);
        });
    }
};
