<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('duels', function (Blueprint $table) {
            $table->string('phase', 2)->default('DP')->after('id');
            $table->unsignedSmallInteger('round')->default(1)->after('phase');
            $table->unsignedSmallInteger('turn')->default(1)->after('round');
            $table->unsignedTinyInteger('active_seat')->default(0)->after('turn');
            $table->unsignedInteger('timer_elapsed_seconds')->default(0)->after('active_seat');
            $table->timestamp('timer_started_at')->nullable()->after('timer_elapsed_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('duels', function (Blueprint $table) {
            $table->dropColumn([
                'phase',
                'round',
                'turn',
                'active_seat',
                'timer_elapsed_seconds',
                'timer_started_at',
            ]);
        });
    }
};
