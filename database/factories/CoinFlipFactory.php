<?php

namespace Database\Factories;

use App\Models\CoinFlip;
use App\Models\Duel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoinFlip>
 */
class CoinFlipFactory extends Factory
{
    public function definition(): array
    {
        return [
            'duel_id' => Duel::factory(),
            'result' => fake()->randomElement(['heads', 'tails']),
        ];
    }
}
