<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDietPlansTable extends Migration
{
    public function up()
    {
        Schema::create('diet_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('plan_type')->nullable();
            $table->integer('meals_per_day')->default(3);
            $table->string('meal_complexity')->default('moderate');
            $table->integer('total_calories')->nullable();
            $table->boolean('generated_by_ai')->default(false);
            $table->text('ai_prompt')->nullable();
            $table->longText('ai_response')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('diet_plans');
    }
}
