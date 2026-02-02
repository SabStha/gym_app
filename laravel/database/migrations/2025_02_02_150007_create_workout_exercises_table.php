<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_id')->constrained()->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained()->onDelete('restrict');
            $table->integer('order_index')->default(0);
            $table->enum('difficulty', ['ok', 'hard'])->default('ok');
            $table->json('recommendation_json')->nullable();
            $table->text('ai_explanation')->nullable();
            $table->timestamps();

            // Prefer unique exercises per workout unless needed otherwise
            // $table->unique(['workout_id', 'exercise_id']); // Leaving commented out or enabling based on request "unique: (workout_id, exercise_id) OR allow duplicates only if needed; prefer unique"
            $table->unique(['workout_id', 'exercise_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('workout_exercises');
    }
};
