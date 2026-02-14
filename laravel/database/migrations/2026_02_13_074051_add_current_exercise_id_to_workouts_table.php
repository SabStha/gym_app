<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->unsignedBigInteger('current_workout_exercise_id')->nullable()->after('status');
            $table->foreign('current_workout_exercise_id')->references('id')->on('workout_exercises')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->dropForeign(['current_workout_exercise_id']);
            $table->dropColumn('current_workout_exercise_id');
        });
    }
};
