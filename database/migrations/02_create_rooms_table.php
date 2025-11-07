<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedInteger('roomNumber')->unique();
            $table->unsignedTinyInteger('capacity'); // 4 ou 6
            $table->string('name')->unique()->nullable();
            $table->string('mood')->nullable();
            $table->string('photoPath')->nullable();
            $table->string('description')->nullable();
            $table->json('passions')->nullable();
            $table->unsignedInteger('totalPoints')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->string('locked_by_email')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // Responsible user
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
