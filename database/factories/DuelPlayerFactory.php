<?php

namespace Database\Factories;

use App\Models\Duel;
use App\Models\DuelPlayer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DuelPlayer>
 */
class DuelPlayerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'duel_id' => Duel::factory(),
            'player_id' => null,
            'name' => fake()->firstName(),
            'color' => fake()->hexColor(),
            'icon' => null,
            'starting_life_points' => 8000,
            'life_points' => 8000,
            'seat' => 0,
        ];
    }
}
