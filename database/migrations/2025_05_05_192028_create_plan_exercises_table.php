<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanExercisesTable extends Migration
{
    public function up()
    {
        Schema::create('plan_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('workout_plans')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->unsignedTinyInteger('sets')->nullable();
            $table->unsignedTinyInteger('reps')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plan_exercises');
    }
}
