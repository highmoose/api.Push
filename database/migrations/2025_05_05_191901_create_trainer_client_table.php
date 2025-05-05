<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainerClientTable extends Migration
{
    public function up()
    {
        Schema::create('trainer_client', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['trainer_id', 'client_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trainer_client');
    }
}
