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
        Schema::create('anecdotes', function (Blueprint $table) {
            $table->id('id');
            $table->text('text')->charset('utf8mb4');
            $table->unsignedInteger('room');
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->boolean('valid')->default(false);
            $table->boolean('delete')->default(false);
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anecdotes');
    }
};
