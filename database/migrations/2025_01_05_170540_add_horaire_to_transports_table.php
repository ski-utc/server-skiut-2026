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
        Schema::table('transports', function (Blueprint $table) {
            $table->time('horaire_depart')->nullable(); // Champ horaire de départ
            $table->time('horaire_arrivee')->nullable(); // Champ horaire d'arrivée
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transports', function (Blueprint $table) {
            $table->dropColumn(['horaire_depart', 'horaire_arrivee']);
        });
    }
};
