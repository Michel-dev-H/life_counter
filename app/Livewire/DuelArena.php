<?php

namespace App\Livewire;

use App\Appearance;
use App\Enums\DuelPhase;
use App\Models\Duel;
use App\Models\DuelPlayer;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Arena de Duelo')]
class DuelArena extends Component
{
    public Duel $duel;

    public bool $rotateOpponent = true;

    public function mount(Duel $duel): void
    {
        $this->duel = $duel;
    }

    public function subtract(int $playerId, int $amount): void
    {
        $this->player($playerId)->subtractLifePoints($amount);
    }

    public function add(int $playerId, int $amount): void
    {
        $this->player($playerId)->addLifePoints($amount);
    }

    public function halve(int $playerId): void
    {
        $this->player($playerId)->halveLifePoints();
    }

    public function restore(int $playerId): void
    {
        $this->player($playerId)->restoreStartingLifePoints();
    }

    public function undo(): void
    {
        $this->duel->undoLastLifePointChange();
    }

    public function redo(): void
    {
        $this->duel->redoLastLifePointChange();
    }

    public function toggleOpponentRotation(): void
    {
        $this->rotateOpponent = ! $this->rotateOpponent;
    }

    public function setPhase(string $phase): void
    {
        $phase = DuelPhase::tryFrom($phase);

        if ($phase === null) {
            throw ValidationException::withMessages(['phase' => 'Fase desconhecida.']);
        }

        $this->duel->setPhase($phase);
    }

    public function advanceTurn(): void
    {
        $this->duel->advanceTurn();
    }

    public function toggleTimer(): void
    {
        $this->duel->isTimerRunning()
            ? $this->duel->pauseTimer()
            : $this->duel->startTimer();
    }

    public function resetTimer(): void
    {
        $this->duel->resetTimer();
    }

    public function render(): View
    {
        $players = $this->duel->duelPlayers()
            ->with('latestLifePointEvent')
            ->orderBy('seat')
            ->get();

        return view('livewire.duel-arena', [
            'players' => $players,
            'phases' => DuelPhase::cases(),
            'currentPhase' => $this->duel->phase,
            'turnOwner' => $this->duel->turnOwnerLabel(),
            'timerRemaining' => $this->duel->timerRemainingSeconds(),
            'timerRunning' => $this->duel->isTimerRunning(),
            'lastEvent' => $this->duel->lifePointEvents()
                ->with('duelPlayer')
                ->whereNull('undone_at')
                ->latest('life_point_events.id')
                ->first(),
            'canUndo' => $this->duel->canUndoLifePointChange(),
            'canRedo' => $this->duel->canRedoLifePointChange(),
        ]);
    }

    private function player(int $playerId): DuelPlayer
    {
        return $this->duel->duelPlayers()->findOrFail($playerId);
    }
}
