<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('duel_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('color', 7);
            $table->string('icon')->nullable();
            $table->unsignedInteger('starting_life_points');
            $table->unsignedInteger('life_points');
            $table->unsignedTinyInteger('seat');
            $table->timestamps();

            $table->unique(['duel_id', 'seat']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('duel_players');
    }
};
