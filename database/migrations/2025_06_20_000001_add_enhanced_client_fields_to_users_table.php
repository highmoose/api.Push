<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add new fields for enhanced client management
            $table->string('address')->nullable()->after('location');
            $table->decimal('height', 5, 2)->nullable()->after('date_of_birth'); // in cm
            $table->decimal('weight', 5, 2)->nullable()->after('height'); // in kg
            $table->string('fitness_goals')->nullable()->after('weight');
            $table->string('fitness_experience')->nullable()->after('fitness_goals');
            $table->string('fitness_level')->nullable()->after('fitness_experience');
            $table->text('measurements')->nullable()->after('fitness_level');
            $table->text('food_likes')->nullable()->after('measurements');
            $table->text('food_dislikes')->nullable()->after('food_likes');
            $table->text('allergies')->nullable()->after('food_dislikes');
            $table->text('medical_conditions')->nullable()->after('allergies');
            $table->text('notes')->nullable()->after('medical_conditions');
            
            // For client invitations
            $table->string('invite_token')->nullable()->after('notes');
            $table->timestamp('invite_sent_at')->nullable()->after('invite_token');
            $table->timestamp('invite_accepted_at')->nullable()->after('invite_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'height',
                'weight',
                'fitness_goals',
                'fitness_experience',
                'fitness_level',
                'measurements',
                'food_likes',
                'food_dislikes',
                'allergies',
                'medical_conditions',
                'notes',
                'invite_token',
                'invite_sent_at',
                'invite_accepted_at',
            ]);
        });
    }
};
