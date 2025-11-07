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
        Schema::create('challenge_proofs', function (Blueprint $table) {
            $table->id('id');
            $table->string('file');
            $table->string('media_type')->default('image');
            $table->unsignedInteger('nb_likes')->default(0);
            $table->boolean('valid')->default(false);
            $table->unsignedTinyInteger('alert')->default(0);
            $table->boolean('delete')->default(false);
            $table->foreignId('challenge_id')->constrained('challenges')->onDelete('cascade');
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
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
