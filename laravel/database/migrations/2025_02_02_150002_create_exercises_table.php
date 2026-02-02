<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Nullable for system defaults
            $table->string('name');
            $table->string('muscle_group'); // e.g. Chest, Back, Legs
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Unique constraint: user specific
            $table->unique(['user_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('exercises');
    }
};
