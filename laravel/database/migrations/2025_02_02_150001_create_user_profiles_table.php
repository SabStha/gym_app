<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->year('birth_year')->nullable();
            $table->enum('sex', ['male', 'female', 'other', 'na'])->nullable();
            $table->integer('height_cm')->nullable(); // stored as int cm
            $table->enum('goal_preset', ['strength', 'muscle', 'endurance'])->nullable();
            $table->decimal('default_increment_kg', 5, 2)->default(2.50);
            $table->enum('rounding_mode', ['down', 'nearest'])->default('down');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
};
