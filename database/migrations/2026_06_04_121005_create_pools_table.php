<?php

use App\Models\Season;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pools', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignIdFor(Season::class)->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('visibility', 10)->default('private')->index();
            $table->string('invite_code')->nullable()->unique();
            $table->unsignedSmallInteger('points_exact')->default(10);
            $table->unsignedSmallInteger('points_result')->default(5);
            $table->unsignedSmallInteger('points_champion')->default(25);
            $table->unsignedSmallInteger('points_group_position')->default(3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pools');
    }
};
