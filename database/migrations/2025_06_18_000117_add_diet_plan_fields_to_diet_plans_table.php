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
        Schema::table('diet_plans', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('diet_plans', 'plan_type')) {
                $table->string('plan_type')->nullable()->after('description');
            }
            if (!Schema::hasColumn('diet_plans', 'meals_per_day')) {
                $table->integer('meals_per_day')->default(3)->after('description');
            }
            if (!Schema::hasColumn('diet_plans', 'meal_complexity')) {
                $table->string('meal_complexity')->default('moderate')->after('description');
            }
            if (!Schema::hasColumn('diet_plans', 'total_calories')) {
                $table->integer('total_calories')->nullable()->after('description');
            }
            if (!Schema::hasColumn('diet_plans', 'ai_prompt')) {
                $table->text('ai_prompt')->nullable()->after('generated_by_ai');
            }
            if (!Schema::hasColumn('diet_plans', 'ai_response')) {
                $table->longText('ai_response')->nullable()->after('generated_by_ai');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diet_plans', function (Blueprint $table) {
            $table->dropColumn([
                'plan_type',
                'meals_per_day', 
                'meal_complexity',
                'total_calories',
                'ai_prompt',
                'ai_response'
            ]);
        });
    }
};
