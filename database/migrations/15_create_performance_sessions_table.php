<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('performance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->decimal('max_speed', 8, 2)->default(0);
            $table->decimal('average_speed', 8, 2)->default(0);
            $table->decimal('distance', 10, 2)->default(0);
            $table->integer('duration')->default(0);
            $table->timestamp('session_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_sessions');
    }
};
