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
        Schema::create('challenge_proofs', function (Blueprint $table) {
            $table->id();
            $table->string('file'); // will contain jpg, png, mp4, etc. file path 
            $table->unsignedInteger('nbLikes')->default(0);
            $table->boolean('valid')->default(false); 
            $table->unsignedTinyInteger('alert')->default(0);
            $table->boolean('delete')->default(false);
            $table->boolean('active')->default(false);
            $table->foreignId('challengeId')->constrained(); // Foreign key to challenges table
            $table->foreignId('roomId')->constrained()->onDelete('cascade'); // Foreign key to room table (hésite avec users)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_proofs');
    }
};
