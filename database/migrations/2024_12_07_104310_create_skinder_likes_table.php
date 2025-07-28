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
        Schema::create('skinder_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_likeur')->references('id')->on('rooms')->onDelete('cascade');
            $table->foreignId('room_liked')->references('id')->on('rooms')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skinder_likes');
    }
};
