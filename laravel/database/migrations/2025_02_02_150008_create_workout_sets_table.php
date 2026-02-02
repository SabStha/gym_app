<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('workout_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_exercise_id')->constrained('workout_exercises')->onDelete('cascade');
            $table->unsignedTinyInteger('set_number');
            $table->unsignedTinyInteger('reps');
            $table->decimal('weight_kg', 6, 2);
            $table->timestamps();

            $table->index(['workout_exercise_id', 'set_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('workout_sets');
    }
};
