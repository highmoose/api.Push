<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkoutPlansTable extends Migration
{
    public function up()
    {
        Schema::create('workout_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('generated_by_ai')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('workout_plans');
    }
}
