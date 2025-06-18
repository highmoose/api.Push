<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update any existing 'in-progress' tasks to 'pending'
        DB::table('tasks')->where('status', 'in-progress')->update(['status' => 'pending']);
        
        // Then modify the enum to remove 'in-progress'
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('pending', 'completed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the original enum with 'in-progress'
        DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('pending', 'in-progress', 'completed') DEFAULT 'pending'");
    }
};
