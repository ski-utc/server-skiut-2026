<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('anecdotes_warns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('anecdote_id')->constrained('anecdotes')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'anecdote_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('anecdotesWarn');
    }
};
