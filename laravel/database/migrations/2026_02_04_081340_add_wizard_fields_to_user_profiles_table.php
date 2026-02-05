<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('user_profiles', 'current_weight_kg')) {
                $table->decimal('current_weight_kg', 5, 2)->nullable();
            }
            if (!Schema::hasColumn('user_profiles', 'goal_type')) {
                // Use string instead of enum to avoid potential issues on some DBs
                $table->string('goal_type')->nullable();
            }
            if (!Schema::hasColumn('user_profiles', 'target_weight_kg')) {
                $table->decimal('target_weight_kg', 5, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['current_weight_kg', 'goal_type', 'target_weight_kg']);
        });
    }
};
