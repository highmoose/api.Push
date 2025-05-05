<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormResponsesTable extends Migration
{
    public function up()
    {
        Schema::create('form_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained('forms')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->json('answers');
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('form_responses');
    }
}
