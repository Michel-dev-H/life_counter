<?php

namespace Database\Factories;

use App\Enums\DuelPhase;
use App\Models\Duel;
use App\Models\Reminder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reminder>
 */
class ReminderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'duel_id' => Duel::factory(),
            'duel_player_id' => null,
            'name' => fake()->words(2, true),
            'description' => null,
            'trigger_turn' => null,
            'trigger_phase' => null,
            'duration_turns' => null,
            'removal_condition' => null,
            'completed' => false,
        ];
    }

    /**
     * A reminder that fires on a given turn of the duel.
     */
    public function onTurn(int $turn, DuelPhase $phase = DuelPhase::Battle): static
    {
        return $this->state(fn (array $attributes): array => [
            'trigger_turn' => $turn,
            'trigger_phase' => $phase->value,
        ]);
    }
}
