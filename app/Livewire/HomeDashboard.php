<?php

namespace App\Livewire;

use App\Models\Duel;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Início')]
class HomeDashboard extends Component
{
    public function render(): View
    {
        $recentDuels = Duel::query()
            ->with('duelPlayers:id,duel_id,name,seat')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('livewire.home-dashboard', [
            'recentDuels' => $recentDuels,
        ]);
    }
}
