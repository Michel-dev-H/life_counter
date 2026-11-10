<?php

namespace App\Livewire;

use App\Enums\DuelPhase;
use App\Models\Duel;
use App\Models\DuelPlayer;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Native\Mobile\Facades\Device;

#[Layout('layouts.app')]
#[Title('Arena de Duelo')]
class DuelArena extends Component
{
    public Duel $duel;

    public bool $rotateOpponent = true;

    public ?int $expandedPlayerId = null;

    public ?int $editingPlayerId = null;

    public string $editingPlayerName = '';

    public string $editingPlayerColor = 'cyan';

    public ?string $editingPlayerIcon = null;

    public ?int $lifePointCalculatorPlayerId = null;

    public bool $hapticFeedbackEnabled = false;

    public ?string $firstPlayerDrawn = null;

    public function mount(Duel $duel): void
    {
        $this->duel = $duel;
        $this->hapticFeedbackEnabled = Setting::read('duel.feedback.haptics') === '1';
    }

    public function subtract(int $playerId, int $amount): void
    {
        try {
            $this->changeLifePoints(
                $playerId,
                function (DuelPlayer $player) use ($amount): void {
                    $player->subtractLifePoints($amount);
                },
            );
        } catch (InvalidArgumentException $exception) {
            $this->addError('amount', $exception->getMessage());
        }
    }

    public function add(int $playerId, int $amount): void
    {
        try {
            $this->changeLifePoints(
                $playerId,
                function (DuelPlayer $player) use ($amount): void {
                    $player->addLifePoints($amount);
                },
            );
        } catch (InvalidArgumentException $exception) {
            $this->addError('amount', $exception->getMessage());
        }
    }

    public function halve(int $playerId): void
    {
        $this->changeLifePoints(
            $playerId,
            function (DuelPlayer $player): void {
                $player->halveLifePoints();
            },
        );
    }

    public function restore(int $playerId): void
    {
        $this->changeLifePoints(
            $playerId,
            function (DuelPlayer $player): void {
                $player->restoreStartingLifePoints();
            },
        );
    }

    public function doubleLifePoints(int $playerId): void
    {
        $this->changeLifePoints(
            $playerId,
            function (DuelPlayer $player): void {
                $player->doubleLifePoints();
            },
        );
    }

    public function toggleHapticFeedback(): void
    {
        $this->resetValidation('hapticFeedback');

        if ($this->hapticFeedbackEnabled) {
            $this->hapticFeedbackEnabled = false;
            Setting::write('duel.feedback.haptics', '0');

            return;
        }

        if (! Device::vibrate()) {
            $this->hapticFeedbackEnabled = false;
            Setting::write('duel.feedback.haptics', '0');
            $this->addError('hapticFeedback', 'Vibração indisponível neste ambiente; a preferência foi desativada.');

            return;
        }

        $this->hapticFeedbackEnabled = true;
        Setting::write('duel.feedback.haptics', '1');
    }

    public function openLifePointCalculator(int $playerId): void
    {
        $this->player($playerId);

        $this->lifePointCalculatorPlayerId = $playerId;
        $this->resetValidation();
    }

    public function closeLifePointCalculator(): void
    {
        $this->reset('lifePointCalculatorPlayerId');
        $this->resetValidation();
    }

    public function applyLifePointCalculation(int $playerId, string $input, string $operation): void
    {
        $validated = validator([
            'input' => $input,
            'operation' => $operation,
        ], [
            'input' => ['required', 'integer', 'min:0', 'max:'.DuelPlayer::MAX_LIFE_POINTS],
            'operation' => ['required', Rule::in(['add', 'subtract', 'set'])],
        ], [
            'input.required' => 'Informe um valor para continuar.',
            'input.integer' => 'Informe um número inteiro entre 0 e 999.999.',
            'input.min' => 'O valor não pode ser negativo.',
            'input.max' => 'O valor não pode exceder 999.999.',
            'operation.in' => 'Selecione uma operação válida.',
        ])->validate();

        try {
            $value = (int) $validated['input'];

            $this->changeLifePoints(
                $playerId,
                function (DuelPlayer $player) use ($validated, $value): void {
                    match ($validated['operation']) {
                        'add' => $player->addLifePoints($value),
                        'subtract' => $player->subtractLifePoints($value),
                        'set' => $player->setLifePointsTo($value),
                        default => throw new InvalidArgumentException('Selecione uma operação válida.'),
                    };
                },
            );
        } catch (InvalidArgumentException $exception) {
            $this->addError('input', $exception->getMessage());

            return;
        }

        $this->closeLifePointCalculator();
    }

    public function undo(): void
    {
        $event = $this->duel->lifePointEvents()
            ->whereNull('undone_at')
            ->latest('life_point_events.id')
            ->first();

        if ($event !== null && $this->duel->undoLastLifePointChange()) {
            $this->dispatchLifePointFeedback($event->duel_player_id, $event->after, $event->before);
        }
    }

    public function redo(): void
    {
        $event = $this->duel->lifePointEvents()
            ->whereNotNull('undone_at')
            ->oldest('life_point_events.id')
            ->first();

        if ($event !== null && $this->duel->redoLastLifePointChange()) {
            $this->dispatchLifePointFeedback($event->duel_player_id, $event->before, $event->after);
        }
    }

    public function toggleOpponentRotation(): void
    {
        $this->rotateOpponent = ! $this->rotateOpponent;
    }

    public function togglePlayerExpansion(int $playerId): void
    {
        $player = $this->player($playerId);

        $this->expandedPlayerId = $this->expandedPlayerId === $player->id
            ? null
            : $player->id;
    }

    public function movePlayer(int $playerId, string $direction): void
    {
        if (! in_array($direction, ['up', 'down'], true)) {
            throw ValidationException::withMessages([
                'layout' => 'Escolha uma direção válida para reorganizar os participantes.',
            ]);
        }

        $this->player($playerId);

        $players = $this->duelPlayersInLayoutOrder();

        if ($players->count() <= 2) {
            return;
        }

        $currentIndex = $players->search(fn (DuelPlayer $player): bool => $player->id === $playerId);

        if ($currentIndex === false) {
            return;
        }

        $targetIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if ($targetIndex < 0 || $targetIndex >= $players->count()) {
            return;
        }

        $playerIds = $players->modelKeys();
        array_splice($playerIds, $currentIndex, 1);
        array_splice($playerIds, $targetIndex, 0, [$playerId]);

        Setting::write($this->playerOrderSettingKey(), implode(',', $playerIds));
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

    public function drawFirstPlayer(): void
    {
        $players = $this->duel->duelPlayers;

        if ($players->isEmpty()) {
            return;
        }

        $chosen = $players->random();

        $this->firstPlayerDrawn = $chosen->name;
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

    public function syncTimer(): array
    {
        $this->duel->refresh();

        return [
            'remaining' => $this->duel->timerRemainingSeconds(),
            'running' => $this->duel->isTimerRunning(),
        ];
    }

    public function editPlayerIdentity(int $playerId): void
    {
        $player = $this->player($playerId);

        $this->editingPlayerId = $player->id;
        $this->editingPlayerName = $player->name;
        $this->editingPlayerColor = $player->accentToken();
        $this->editingPlayerIcon = array_key_exists((string) $player->icon, Duel::PLAYER_ICONS)
            ? $player->icon
            : null;
    }

    public function savePlayerIdentity(): void
    {
        $validated = $this->validate([
            'editingPlayerId' => ['required', 'integer'],
            'editingPlayerName' => ['required', 'string', 'max:40'],
            'editingPlayerColor' => ['required', 'string', Rule::in(array_keys(Duel::PLAYER_ACCENTS))],
            'editingPlayerIcon' => ['nullable', 'string', Rule::in(array_keys(Duel::PLAYER_ICONS))],
        ], [
            'editingPlayerName.required' => 'Informe um nome para o participante.',
            'editingPlayerName.max' => 'O nome deve ter no máximo 40 caracteres.',
            'editingPlayerColor.in' => 'Selecione uma cor de identificação disponível.',
            'editingPlayerIcon.in' => 'Selecione um ícone disponível.',
        ]);

        $this->player($validated['editingPlayerId'])->update([
            'name' => trim($validated['editingPlayerName']),
            'color' => $validated['editingPlayerColor'],
            'icon' => $validated['editingPlayerIcon'],
        ]);

        $this->cancelPlayerIdentityEdit();
    }

    public function cancelPlayerIdentityEdit(): void
    {
        $this->reset([
            'editingPlayerId',
            'editingPlayerName',
            'editingPlayerColor',
            'editingPlayerIcon',
        ]);
    }

    public function render(): View
    {
        $players = $this->duelPlayersInLayoutOrder();
        $isTwoPlayerDuel = $players->count() === 2;

        if ($isTwoPlayerDuel) {
            $players = $players->sortByDesc('seat')->values();
        }

        $lifePointCalculatorPlayer = $this->lifePointCalculatorPlayerId === null
            ? null
            : $players->firstWhere('id', $this->lifePointCalculatorPlayerId);

        return view('livewire.duel-arena', [
            'players' => $players,
            'isTwoPlayerDuel' => $isTwoPlayerDuel,
            'lifePointCalculatorPlayer' => $lifePointCalculatorPlayer,
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

    /**
     * @return EloquentCollection<int, DuelPlayer>
     */
    private function duelPlayersInLayoutOrder(): EloquentCollection
    {
        $players = $this->duel->duelPlayers()
            ->with('latestLifePointEvent')
            ->orderBy('seat')
            ->get();
        $storedOrder = Setting::read($this->playerOrderSettingKey());

        if ($storedOrder === null) {
            return $players;
        }

        $orderedIds = array_map('intval', explode(',', $storedOrder));
        $playerIds = $players->modelKeys();

        if (count($orderedIds) !== count($playerIds)
            || count(array_unique($orderedIds)) !== count($playerIds)
            || array_diff($orderedIds, $playerIds) !== []) {
            return $players;
        }

        $positions = array_flip($orderedIds);

        return $players->sortBy(fn (DuelPlayer $player): int => $positions[$player->id])->values();
    }

    private function playerOrderSettingKey(): string
    {
        return 'duel.layout.order.'.$this->duel->id;
    }

    /**
     * @param  callable(DuelPlayer): void  $change
     */
    private function changeLifePoints(int $playerId, callable $change): void
    {
        $player = $this->player($playerId);
        $before = $player->life_points;

        $change($player);

        $player->refresh();
        $after = $player->life_points;

        if ($before === $after) {
            return;
        }

        $this->dispatchLifePointFeedback($player->id, $before, $after);
    }

    private function dispatchLifePointFeedback(int $playerId, int $before, int $after): void
    {
        $this->dispatch(
            'life-points-changed',
            playerId: $playerId,
            before: $before,
            after: $after,
            kind: $after < $before ? 'damage' : 'recovery',
        );

        if ($this->hapticFeedbackEnabled && ! Device::vibrate()) {
            $this->hapticFeedbackEnabled = false;
            Setting::write('duel.feedback.haptics', '0');
            $this->addError('hapticFeedback', 'Vibração indisponível neste ambiente; a preferência foi desativada.');
        }
    }
}
