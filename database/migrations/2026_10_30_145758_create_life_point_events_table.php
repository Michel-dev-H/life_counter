<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('life_point_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duel_player_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('before');
            $table->unsignedInteger('after');
            $table->string('operation', 20);
            $table->unsignedInteger('amount')->default(0);
            $table->timestamp('undone_at')->nullable();
            $table->timestamps();

            $table->index(['duel_player_id', 'undone_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('life_point_events');
    }
};
