<?php

use App\Models\Season;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Season::class)->constrained()->cascadeOnDelete();
            $table->string('name', 30);
            $table->unsignedTinyInteger('sort_order');
            $table->boolean('is_knockout')->default(false);
            $table->timestamps();

            $table->unique(['season_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
