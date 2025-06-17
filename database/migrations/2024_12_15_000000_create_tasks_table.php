<?php
// Create this file: database/migrations/2024_12_15_000000_create_tasks_table.php

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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The trainer who owns the task
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('category', ['general', 'client-related', 'equipment', 'administrative'])->default('general');
            $table->enum('status', ['pending', 'in-progress', 'completed'])->default('pending');
            $table->json('reminder')->nullable(); // Store reminder settings as JSON
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};