<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProgressLogsTable extends Migration
{
    public function up()
    {
        Schema::create('progress_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('trainer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('exercise_id')->nullable()->constrained('exercises')->nullOnDelete();
            $table->enum('type', ['weight', 'body_fat', 'exercise', 'note']);
            $table->decimal('value', 8, 2)->nullable();
            $table->unsignedSmallInteger('reps')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('progress_logs');
    }
}
