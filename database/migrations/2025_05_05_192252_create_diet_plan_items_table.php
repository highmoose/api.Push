<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDietPlanItemsTable extends Migration
{
    public function up()
    {
        Schema::create('diet_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diet_plan_id')->constrained()->cascadeOnDelete();
            $table->string('meal_name');
            $table->string('meal_type')->default('meal'); // breakfast, lunch, dinner, snack
            $table->json('ingredients')->nullable();
            $table->text('instructions')->nullable();
            $table->integer('calories')->default(0);
            $table->integer('protein')->default(0);
            $table->integer('carbs')->default(0);
            $table->integer('fats')->default(0);
            $table->integer('meal_order')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('diet_plan_items');
    }
}
