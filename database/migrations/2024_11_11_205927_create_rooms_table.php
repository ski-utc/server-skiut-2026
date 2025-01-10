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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedInteger('roomNumber')->unique();
            $table->unsignedTinyInteger('capacity'); // 4 ou 6
            $table->string('name')->unique();
            $table->string('mood');
            $table->string('photoPath');
            $table->string('description')->charset('utf8mb4'); 
            $table->json('passions')->charset('utf8mb4')->default(json_encode([]));
            $table->unsignedTinyInteger('totalPoints')->default(0);
            $table->foreignId('userID')->nullable()->constrained('users', 'id')->onDelete('cascade'); // Foreign key to users table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
