<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoalsTable extends Migration
{
    public function up()
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['weight_loss', 'muscle_gain', 'performance', 'general'])->default('general');
            $table->decimal('target_value', 8, 2)->nullable();
            $table->date('target_date')->nullable();
            $table->boolean('achieved')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('goals');
    }
}
