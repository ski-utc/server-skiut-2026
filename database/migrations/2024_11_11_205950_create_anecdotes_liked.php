<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('anecdotes_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userId')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('anecdoteId')->references('id')->on('anecdotes')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['userId', 'anecdoteId']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('anecdotesLikes');
    }
};
