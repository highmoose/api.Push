<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // bigint unsigned, auto-increment

            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->string('gym')->nullable();
            $table->date('date_of_birth')->nullable();

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->enum('role', ['client', 'trainer', 'gym_owner', 'admin'])->default('client');
            $table->tinyInteger('is_temp')->default(0);

            $table->unsignedBigInteger('gym_id')->nullable();
            $table->foreign('gym_id')->references('id')->on('gyms')->nullOnDelete();

            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            $table->softDeletes(); // deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
