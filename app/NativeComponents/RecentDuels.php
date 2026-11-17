<?php

namespace App\NativeComponents;

use App\Models\Duel;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class RecentDuels extends NativeComponent
{
    public function navTitle(): string
    {
        return 'Duelos Recentes';
    }

    public function render(): View
    {
        return view('native.recent-duels', [
            'duels' => Duel::query()
                ->with(['duelPlayers' => fn ($query) => $query->orderBy('seat')])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ]);
    }
}
