<?php

namespace Database\Factories;

use App\Models\DuelPlayer;
use App\Models\LifePointEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LifePointEvent>
 */
class LifePointEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'duel_player_id' => DuelPlayer::factory(),
            'before' => 8000,
            'after' => 7500,
            'operation' => 'subtract',
            'amount' => 500,
            'undone_at' => null,
        ];
    }
}
