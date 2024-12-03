<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('anecdotes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('userId')->constrained()->onDelete('cascade'); // Foreign key to users table
            $table->unsignedInteger('nbLikes')->default(0);
            $table->boolean('valid')->default(false);
            $table->unsignedTinyInteger('alert')->default(0);
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
