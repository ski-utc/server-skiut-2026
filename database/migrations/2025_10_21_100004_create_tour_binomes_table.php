<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tour_binomes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('room_tour_id');
            $table->string('binome_name');
            $table->json('member_ids');
            $table->json('assigned_rooms');
            $table->json('visited_rooms');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_binomes');
    }
};
