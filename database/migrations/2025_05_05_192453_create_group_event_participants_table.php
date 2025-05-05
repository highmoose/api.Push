<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupEventParticipantsTable extends Migration
{
    public function up()
    {
        Schema::create('group_event_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_event_id')->constrained('group_events')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('confirmed')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('group_event_participants');
    }
}
