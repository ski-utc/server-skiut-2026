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
        Schema::create('transports', function (Blueprint $table) {
            $table->id();
            $table->string('departure'); // Paris / Compiègne / Les 2 Alpes
            $table->string('arrival'); // Paris / Compiègne / Les 2 Alpes
            $table->string('colour');
            $table->string('colourName');
            $table->string('type'); // aller / retour
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transports');
    }
};
