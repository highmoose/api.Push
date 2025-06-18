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
        // Add 'none' to the priority enum and make it the default
        DB::statement("ALTER TABLE tasks MODIFY COLUMN priority ENUM('none', 'low', 'medium', 'high') DEFAULT 'none'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First update any 'none' priorities to 'medium' (the original default)
        DB::table('tasks')->where('priority', 'none')->update(['priority' => 'medium']);
        
        // Then restore the original enum without 'none'
        DB::statement("ALTER TABLE tasks MODIFY COLUMN priority ENUM('low', 'medium', 'high') DEFAULT 'medium'");
    }
};
