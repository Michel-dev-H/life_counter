<?php

namespace App\NativeComponents;

use App\Models\Duel;
use App\Models\Setting;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Device;

class CoinTools extends NativeComponent
{
    private const FLIPPING_MS = 600;

    private const FRAME_MS = 90;

    public Duel $duel;

    public int $flippingStartedAt = 0;

    public int $flippingSeed = 0;

    public function navTitle(): string
    {
        return 'Lançar moeda';
    }

    public function flip(): void
    {
        $this->duel->flipCoin();

        $this->flippingSeed = random_int(1, 1_000_000);
        $this->flippingStartedAt = now()->getTimestampMs();

        if (Setting::read('duel.feedback.haptics') === '1') {
            Device::vibrate();
        }
    }

    public function render(): View
    {
        $lastFlip = $this->duel->coinFlips()->latest()->first();
        $elapsed = $this->flippingStartedAt > 0 ? now()->getTimestampMs() - $this->flippingStartedAt : 0;
        $flipping = $this->flippingStartedAt > 0 && $elapsed < self::FLIPPING_MS;
        $frame = $flipping ? intdiv($elapsed, self::FRAME_MS) : 0;
        $face = $flipping ? $this->flippingFace($frame) : $lastFlip?->result;

        return view('native.coin-tools', [
            'counts' => $this->duel->coinFlipCounts(),
            'history' => $this->duel->coinFlips()->latest()->limit(20)->get(),
            'lastFlip' => $lastFlip,
            'flipping' => $flipping,
            'frame' => $frame,
            'face' => $face,
        ]);
    }

    /**
     * Face shown while the coin spins. Derived from the seed and the
     * elapsed frame so every re-render paints a different side without
     * writing to the database.
     */
    private function flippingFace(int $frame): string
    {
        return ($this->flippingSeed + $frame * 7) % 2 === 0 ? 'heads' : 'tails';
    }
}
