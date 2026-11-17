<?php

namespace App\NativeComponents;

use App\Models\Duel;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class DiceTools extends NativeComponent
{
    public Duel $duel;

    public string $diceCount = '1';

    public function navTitle(): string
    {
        return 'Lançar dados';
    }

    public function roll(): void
    {
        $count = max(1, min(10, (int) $this->diceCount));

        $this->diceCount = (string) $count;

        $this->duel->rollDice($count);
    }

    public function render(): View
    {
        return view('native.dice-tools', [
            'history' => $this->duel->diceRolls()->latest()->limit(10)->get(),
            'lastRoll' => $this->duel->diceRolls()->latest()->first(),
        ]);
    }
}
