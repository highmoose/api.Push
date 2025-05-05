<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('custom_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->text('request');
            $table->enum('frequency', ['once', 'weekly', 'biweekly', 'monthly'])->default('once');
            $table->timestamp('due_at')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_requests');
    }
}
