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
            $table->text('ingredients')->nullable();
            $table->decimal('calories', 6, 2)->nullable();
            $table->string('time_of_day')->nullable(); // e.g., breakfast, lunch
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('diet_plan_items');
    }
}
