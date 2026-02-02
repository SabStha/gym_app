<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('day_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_day_id')->constrained('routine_days')->onDelete('cascade');
            // Restrict updates/deletes if exercise is in use by a plan
            $table->foreignId('exercise_id')->constrained('exercises')->onDelete('restrict');
            $table->integer('order_index')->default(0);
            $table->unsignedTinyInteger('target_sets');
            $table->unsignedTinyInteger('rep_min');
            $table->unsignedTinyInteger('rep_max');
            $table->decimal('increment_override_kg', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('day_exercises');
    }
};
