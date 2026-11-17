<?php

namespace App\NativeComponents;

use App\Models\Duel;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class CoinTools extends NativeComponent
{
    public Duel $duel;

    public function navTitle(): string
    {
        return 'Lançar moeda';
    }

    public function flip(): void
    {
        $this->duel->flipCoin();
    }

    public function render(): View
    {
        return view('native.coin-tools', [
            'counts' => $this->duel->coinFlipCounts(),
            'history' => $this->duel->coinFlips()->latest()->limit(20)->get(),
            'lastFlip' => $this->duel->coinFlips()->latest()->first(),
        ]);
    }
}
