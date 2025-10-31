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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->enum('type', ['global', 'targeted', 'room_based'])->default('global');
            $table->json('target_users')->nullable();
            $table->json('target_rooms')->nullable();
            $table->boolean('push_sent')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->json('firebase_response')->nullable();
            $table->text('title');
            $table->text('description');
            $table->boolean('general');
            $table->boolean('display');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
