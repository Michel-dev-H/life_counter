<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coin_flips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('duel_id')->constrained()->cascadeOnDelete();
            $table->string('result', 10); // 'heads' or 'tails'
            $table->timestamps();

            $table->index(['duel_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coin_flips');
    }
};
