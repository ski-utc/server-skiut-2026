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
        Schema::create('monoprut', function (Blueprint $table) {
            $table->id();
            $table->string('product');
            $table->string('quantity');
            $table->enum('type', ['fruit', 'veggie', 'drink', 'sweet', 'snack', 'dairy', 'bread', 'meat', 'fish', 'grain', 'other'])->default('other');
            $table->foreignId('giver_room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('receiver_room_id')->constrained('rooms')->onDelete('cascade')->nullable()->default(null);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monoprut');
    }
};


// fruit	apple	    Fruits, pommes, etc.
// drink	cup-soda	Boissons
// sweet	candy	    Sucreries
// snack	sandwich	Snacks / encas
// dairy	milk	    Produits laitiers
// bread	bread	    Pain
// meat	    drumstick	Viande
// veggie	carrot	    Légumes
// fish	    fish	    Poisson
// grain	wheat	    Céréales, riz, pâtes
// other	package	    Divers / autre