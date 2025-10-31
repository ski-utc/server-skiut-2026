<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('room_tour_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tour_binome_id');
            $table->string('room_id');
            $table->boolean('visited')->default(false);
            $table->timestamp('visited_at')->nullable();
            $table->integer('visit_order');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_tour_visits');
    }
};
