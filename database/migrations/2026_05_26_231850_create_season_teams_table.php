<?php

use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('season_teams', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Season::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Team::class)->constrained()->cascadeOnDelete();
            $table->string('group_letter', 1)->nullable();
            $table->unsignedTinyInteger('group_position')->nullable();
            $table->timestamps();
            $table->unique(['season_id', 'team_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_teams');
    }
};
