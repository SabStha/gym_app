<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('workouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('routine_day_id')->nullable()->constrained('routine_days')->onDelete('set null'); // keep workout even if routine day deleted? Or nullable for free workout. User said: "routine_day_id FK routine_days (nullable if user logs free workout)"
            $table->dateTime('workout_date');
            $table->unsignedSmallInteger('duration_min')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'workout_date']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('workouts');
    }
};
