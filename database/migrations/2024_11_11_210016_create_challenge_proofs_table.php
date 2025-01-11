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
            $table->id('id');
            $table->string('file'); // File path for jpg, png, mp4, etc.
            $table->unsignedInteger('nb_likes')->default(0);
            $table->boolean('valid')->default(false);
            $table->unsignedTinyInteger('alert')->default(0); // Number of alerts
            $table->boolean('delete')->default(false); // Mark for deletion

            // Foreign keys
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade'); // Ensure cascading delete with challenges

            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade'); // Ensure cascading delete with rooms

            // Optionally, include user_id if proofs are tied to users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

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
