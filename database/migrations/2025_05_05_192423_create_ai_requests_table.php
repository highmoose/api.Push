<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['workout_plan', 'diet_plan'])->default('workout_plan');
            $table->json('input_data');
            $table->json('response_data')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_requests');
    }
}
