<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('anecdotes_warn', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('anecdote_id')->references('id')->on('anecdotes')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'anecdote_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('anecdotes_warn');
    }
};
