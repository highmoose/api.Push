<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('diet_plan_items', function (Blueprint $table) {
            if (!Schema::hasColumn('diet_plan_items', 'meal_type')) {
                $table->string('meal_type')->default('meal')->after('meal_name');
            }
            if (!Schema::hasColumn('diet_plan_items', 'instructions')) {
                $table->text('instructions')->nullable()->after('ingredients');
            }
            if (!Schema::hasColumn('diet_plan_items', 'protein')) {
                $table->integer('protein')->default(0)->after('calories');
            }
            if (!Schema::hasColumn('diet_plan_items', 'carbs')) {
                $table->integer('carbs')->default(0)->after('calories');
            }
            if (!Schema::hasColumn('diet_plan_items', 'fats')) {
                $table->integer('fats')->default(0)->after('calories');
            }
            if (!Schema::hasColumn('diet_plan_items', 'meal_order')) {
                $table->integer('meal_order')->default(1)->after('calories');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diet_plan_items', function (Blueprint $table) {
            $table->dropColumn([
                'meal_type',
                'instructions',
                'protein',
                'carbs',
                'fats',
                'meal_order'
            ]);
        });
    }
};
