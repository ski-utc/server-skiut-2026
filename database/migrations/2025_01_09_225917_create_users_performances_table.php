<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersPerformancesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users_performances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Référence à l'utilisateur
            $table->float('max_speed'); // Vitesse maximale (en km/h)
            $table->float('total_distance'); // Distance totale parcourue (en km)
            $table->timestamps();

            // Contraintes
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_performances');
    }
}
