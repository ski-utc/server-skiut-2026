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
        Schema::create('users', function (Blueprint $table) {
            $table->id('id');
            $table->uuid('uuid')->unique();
            $table->string('cas')->unique();
            $table->string('firstName');
            $table->string('lastName');
            $table->string('email')->unique();
            $table->foreignId('roomID'); //->constrained('rooms', 'id')->onDelete('cascade');
            $table->string('location')->nullable(); // pas encore sûre - pour la géolocalisation 
            $table->boolean('admin'); // true if team info
            $table->boolean('alumniOrExte'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
