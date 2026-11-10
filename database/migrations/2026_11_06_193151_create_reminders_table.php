<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('duel_player_id')->nullable()->constrained('duel_players')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('trigger_turn')->nullable();
            $table->string('trigger_phase', 5)->nullable();
            $table->unsignedSmallInteger('duration_turns')->nullable();
            $table->string('removal_condition')->nullable();
            $table->boolean('completed')->default(false);
            $table->timestamps();

            $table->index(['duel_id', 'completed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
