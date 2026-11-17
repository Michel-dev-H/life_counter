<?php

namespace Database\Factories;

use App\Models\DiceRoll;
use App\Models\Duel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiceRoll>
 */
class DiceRollFactory extends Factory
{
    public function definition(): array
    {
        $results = [fake()->numberBetween(1, 6), fake()->numberBetween(1, 6)];

        return [
            'duel_id' => Duel::factory(),
            'dice_count' => count($results),
            'results' => $results,
            'total' => array_sum($results),
        ];
    }
}
