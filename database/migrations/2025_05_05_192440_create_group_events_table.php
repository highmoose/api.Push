<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupEventsTable extends Migration
{
    public function up()
    {
        Schema::create('group_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('gym_id')->nullable()->constrained('gyms')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('group_events');
    }
}
