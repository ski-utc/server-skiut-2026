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
        Schema::create('user_room_shotguns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_shotgun_id')->constrained('room_shotguns')->onDelete('cascade');
            $table->string('email');
            $table->boolean('is_vegetarian')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_room_shotguns');
    }
};
