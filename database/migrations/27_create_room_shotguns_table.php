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
        Schema::create('room_shotguns', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique();
            $table->integer('nb_places');
            $table->string('responsable_chambre')->nullable();
            $table->enum('ambiance', ['mega grosse night', 'grosse night', 'petite night', 'calme'])->nullable();
            $table->timestamp('locked_until')->nullable();
            $table->string('locked_by_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_shotguns');
    }
};
