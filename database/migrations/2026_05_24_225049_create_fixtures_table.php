<?php

use App\Models\Stage;
use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fixtures', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Stage::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Team::class, 'home_team_id')->nullable()->constrained('teams')->cascadeOnDelete();
            $table->foreignIdFor(Team::class, 'away_team_id')->nullable()->constrained('teams')->cascadeOnDelete();
            $table->integer('home_score')->nullable();
            $table->integer('away_score')->nullable();
            $table->datetime('match_date');
            $table->enum('status', ['scheduled', 'in_progress', 'finished'])->default('scheduled');
            $table->string('external_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
