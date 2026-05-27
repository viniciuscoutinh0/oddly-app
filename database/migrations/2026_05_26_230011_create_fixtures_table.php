<?php

use App\Models\Stage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fixtures', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Stage::class)->constrained()->cascadeOnDelete();
            $table->foreignId('home_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('away_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->unsignedTinyInteger('home_score')->nullable();
            $table->unsignedTinyInteger('away_score')->nullable();
            $table->unsignedTinyInteger('home_score_et')->nullable();
            $table->unsignedTinyInteger('away_score_et')->nullable();
            $table->unsignedTinyInteger('home_score_pen')->nullable();
            $table->unsignedTinyInteger('away_score_pen')->nullable();
            $table->string('group_letter', 1)->nullable();
            $table->unsignedTinyInteger('matchday')->nullable();
            $table->datetime('match_date')->index();
            $table->datetime('locked_at')->nullable();
            $table
                ->enum('status', ['scheduled', 'in_progress', 'finished', 'postponed'])
                ->default('scheduled')
                ->index();
            $table->unsignedInteger('external_id')->unique();
            $table->timestamps();
            $table->index(['stage_id', 'match_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
