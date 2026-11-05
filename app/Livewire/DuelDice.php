<?php

namespace App\Livewire;

use App\Models\DiceRoll;
use App\Models\Duel;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class DuelDice extends Component
{
    public const MAX_DICE = 10;

    public Duel $duel;

    public int $diceCount = 1;

    public bool $showTotal = true;

    public bool $isOpen = false;

    public function mount(Duel $duel): void
    {
        $this->duel = $duel;
    }

    public function openRoller(): void
    {
        $this->isOpen = true;
        $this->resetValidation();
    }

    public function closeRoller(): void
    {
        $this->isOpen = false;
        $this->resetValidation();
    }

    public function increaseDiceCount(): void
    {
        $this->diceCount = min(self::MAX_DICE, $this->diceCount + 1);
        $this->resetValidation('diceCount');
    }

    public function decreaseDiceCount(): void
    {
        $this->diceCount = max(1, $this->diceCount - 1);
        $this->resetValidation('diceCount');
    }

    public function rollDice(): void
    {
        $this->isOpen = true;

        $validated = $this->validate([
            'diceCount' => ['required', 'integer', 'min:1', 'max:'.self::MAX_DICE],
        ], [
            'diceCount.required' => 'Escolha de 1 a 10 dados.',
            'diceCount.integer' => 'Escolha de 1 a 10 dados.',
            'diceCount.min' => 'Escolha de 1 a 10 dados.',
            'diceCount.max' => 'Escolha de 1 a 10 dados.',
        ]);

        $this->createRoll((int) $validated['diceCount']);
    }

    public function repeatDiceRoll(): void
    {
        $this->isOpen = true;
        $this->resetValidation('dice');

        $lastRoll = $this->duel->diceRolls()->latest('id')->first();

        if ($lastRoll === null) {
            $this->addError('dice', 'Ainda não há lançamento para repetir.');

            return;
        }

        $this->diceCount = $lastRoll->dice_count;
        $this->createRoll($lastRoll->dice_count);
    }

    public function render(): View
    {
        $rolls = $this->isOpen
            ? $this->duel->diceRolls()->latest('id')->limit(10)->get()
            : collect();

        return view('livewire.duel-dice', [
            'rolls' => $rolls,
            'latestDiceRoll' => $rolls->first(),
            'repeatDiceCount' => $rolls->first()?->dice_count,
        ]);
    }

    private function createRoll(int $diceCount): DiceRoll
    {
        $results = [];

        for ($index = 0; $index < $diceCount; $index++) {
            $results[] = random_int(1, 6);
        }

        return $this->duel->diceRolls()->create([
            'dice_count' => $diceCount,
            'results' => $results,
            'total' => array_sum($results),
        ]);
    }
}
