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
        Schema::table('sessions', function (Blueprint $table) {
            $table->string('session_type')->nullable()->default('general');
            $table->string('location')->nullable();
            $table->decimal('rate', 8, 2)->nullable()->default(0);
            $table->text('equipment_needed')->nullable();
            $table->text('preparation_notes')->nullable();
            $table->text('goals')->nullable();
            $table->integer('duration')->nullable()->default(60); // in minutes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropColumn([
                'session_type',
                'location',
                'rate',
                'equipment_needed',
                'preparation_notes',
                'goals',
                'duration'
            ]);
        });
    }
};
