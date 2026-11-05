<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dice_rolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duel_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('dice_count');
            $table->json('results');
            $table->unsignedSmallInteger('total');
            $table->timestamps();

            $table->index(['duel_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dice_rolls');
    }
};
