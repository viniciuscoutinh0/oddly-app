<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pools', function (Blueprint $table): void {
            $table->unsignedInteger('entry_fee')->default(0)->after('points_group_position');
        });
    }

    public function down(): void
    {
        Schema::table('pools', function (Blueprint $table): void {
            $table->dropColumn('entry_fee');
        });
    }
};
