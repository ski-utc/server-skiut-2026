<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tour_binomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_tour_id')->constrained('room_tours')->onDelete('cascade');
            $table->string('binome_name');
            $table->foreignId('member_1_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('member_2_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_binomes');
    }
};
