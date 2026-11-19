<?php

namespace App\NativeComponents;

use App\Models\Duel;
use App\Models\Setting;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Device;

class DiceTools extends NativeComponent
{
    private const ROLLING_MS = 600;

    private const FRAME_MS = 90;

    public Duel $duel;

    public string $diceCount = '1';

    public int $rollingStartedAt = 0;

    public int $rollingSeed = 0;

    public int $rollingDiceCount = 0;

    public function navTitle(): string
    {
        return 'Lançar dados';
    }

    public function roll(): void
    {
        $count = max(1, min(10, (int) $this->diceCount));

        $this->diceCount = (string) $count;

        $roll = $this->duel->rollDice($count);

        $this->rollingSeed = random_int(1, 1_000_000);
        $this->rollingDiceCount = $roll->dice_count;
        $this->rollingStartedAt = now()->getTimestampMs();

        if (Setting::read('duel.feedback.haptics') === '1') {
            Device::vibrate();
        }
    }

    public function render(): View
    {
        $lastRoll = $this->duel->diceRolls()->latest()->first();
        $elapsed = $this->rollingStartedAt > 0 ? now()->getTimestampMs() - $this->rollingStartedAt : 0;
        $rolling = $this->rollingStartedAt > 0 && $elapsed < self::ROLLING_MS;
        $frame = $rolling ? intdiv($elapsed, self::FRAME_MS) : 0;

        return view('native.dice-tools', [
            'history' => $this->duel->diceRolls()->latest()->limit(10)->get(),
            'lastRoll' => $lastRoll,
            'rolling' => $rolling,
            'frame' => $frame,
            'faces' => $rolling ? $this->rollingFaces($frame) : ($lastRoll->results ?? []),
        ]);
    }

    /**
     * Faces shown while the dice tumble. Derived from the seed and the
     * elapsed frame so every re-render paints a different face without
     * writing to the database.
     *
     * @return array<int, int>
     */
    private function rollingFaces(int $frame): array
    {
        $faces = [];

        for ($index = 0; $index < $this->rollingDiceCount; $index++) {
            $faces[] = (($this->rollingSeed + $index * 13 + $frame * 7) % 6) + 1;
        }

        return $faces;
    }
}
