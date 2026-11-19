<?php

namespace App\NativeComponents;

use App\Enums\DuelPhase;
use App\Models\Duel;
use App\Models\DuelPlayer;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use Native\Mobile\Attributes\Poll;
use Native\Mobile\Edge\Concerns\ValidatesInput;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Device;

class DuelArena extends NativeComponent
{
    use ValidatesInput;

    public Duel $duel;

    public bool $hapticFeedbackEnabled = false;

    public ?int $calculatorPlayerId = null;

    public string $calculatorInput = '';

    public string $calculatorOperation = 'add';

    public ?int $editingPlayerId = null;

    public string $editingPlayerName = '';

    public string $editingPlayerColor = 'cyan';

    public ?string $editingPlayerIcon = null;

    public ?string $firstPlayerDrawn = null;

    /** @var array<int, string> */
    public const CALCULATOR_OPERATIONS = ['add', 'subtract', 'set'];

    /** @var array<int, array{value: string, label: string}> */
    public const CALCULATOR_OPERATION_LABELS = [
        ['value' => 'add', 'label' => 'Somar'],
        ['value' => 'subtract', 'label' => 'Subtrair'],
        ['value' => 'set', 'label' => 'Definir'],
    ];

    public function mount(): void
    {
        $this->hapticFeedbackEnabled = Setting::read('duel.feedback.haptics') === '1';
    }

    public function navTitle(): string
    {
        return 'Duelo #'.$this->duel->id;
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

    public function openLifePointCalculator(int $playerId): void
    {
        $this->player($playerId);

        $this->calculatorPlayerId = $playerId;
        $this->calculatorInput = '';
        $this->calculatorOperation = 'add';
        $this->resetValidation();
    }

    public function closeLifePointCalculator(): void
    {
        $this->calculatorPlayerId = null;
        $this->calculatorInput = '';
        $this->calculatorOperation = 'add';
        $this->resetValidation();
    }

    /**
     * Pick the calculator operation by its position in the operation list.
     */
    public function setCalculatorOperation(int $index): void
    {
        $operation = self::CALCULATOR_OPERATIONS[$index] ?? null;

        if ($operation === null) {
            return;
        }

        $this->calculatorOperation = $operation;
    }

    /**
     * Pick the identity colour by its position in the accent list.
     */
    public function setEditingPlayerColor(int $index): void
    {
        $accent = array_keys(Duel::PLAYER_ACCENTS)[$index] ?? null;

        if ($accent === null) {
            return;
        }

        $this->editingPlayerColor = $accent;
    }

    /**
     * Pick the identity icon by its position in the icon list.
     */
    public function setEditingPlayerIcon(int $index): void
    {
        $icon = array_keys(Duel::PLAYER_ICONS)[$index] ?? null;

        if ($icon === null) {
            return;
        }

        $this->editingPlayerIcon = $icon;
    }

    public function applyLifePointCalculation(int $playerId): void
    {
        $validated = validator([
            'input' => $this->calculatorInput,
            'operation' => $this->calculatorOperation,
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
            $this->duel->refresh();
        }
    }

    public function redo(): void
    {
        $event = $this->duel->lifePointEvents()
            ->whereNotNull('undone_at')
            ->oldest('life_point_events.id')
            ->first();

        if ($event !== null && $this->duel->redoLastLifePointChange()) {
            $this->duel->refresh();
        }
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

    /**
     * Pick a phase by its position in the phase list.
     *
     * The native press callbacks only carry literal arguments, so the index
     * is what travels from the view; the phase itself is resolved here.
     */
    public function setPhase(int $index): void
    {
        $phase = DuelPhase::cases()[$index] ?? null;

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

        $this->firstPlayerDrawn = $players->random()->name;
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

    #[Poll(1000)]
    public function syncTimer(): void
    {
        $this->duel->refresh();
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
        $this->editingPlayerId = null;
        $this->editingPlayerName = '';
        $this->editingPlayerColor = 'cyan';
        $this->editingPlayerIcon = null;
    }

    public function render(): View
    {
        return view('native.duel-arena', [
            'players' => $this->duelPlayersInLayoutOrder(),
            'phases' => DuelPhase::cases(),
            'currentPhase' => $this->duel->phase,
            'turnOwner' => $this->duel->turnOwnerLabel(),
            'timerRemaining' => $this->duel->formattedTimerRemaining(),
            'timerRunning' => $this->duel->isTimerRunning(),
            'lastEvent' => $this->duel->lifePointEvents()
                ->with('duelPlayer')
                ->whereNull('undone_at')
                ->latest('life_point_events.id')
                ->first(),
            'canUndo' => $this->duel->canUndoLifePointChange(),
            'canRedo' => $this->duel->canRedoLifePointChange(),
            'calculatorPlayer' => $this->calculatorPlayerId === null
                ? null
                : $this->duel->duelPlayers->firstWhere('id', $this->calculatorPlayerId),
            'playerAccents' => self::playerAccentOptions(),
            'playerIcons' => self::playerIconOptions(),
            'calculatorOperations' => self::CALCULATOR_OPERATION_LABELS,
        ]);
    }

    private function player(int $playerId): DuelPlayer
    {
        return $this->duel->duelPlayers()->findOrFail($playerId);
    }

    /**
     * The player identity colours, as a list the view can walk by index.
     *
     * @return array<int, array{key: string, label: string}>
     */
    public static function playerAccentOptions(): array
    {
        return array_map(
            fn (string $key, string $label): array => ['key' => $key, 'label' => $label],
            array_keys(Duel::PLAYER_ACCENTS),
            array_values(Duel::PLAYER_ACCENTS),
        );
    }

    /**
     * The player identity icons, as a list the view can walk by index.
     *
     * @return array<int, array{key: string, label: string, ios: string, android: string}>
     */
    public static function playerIconOptions(): array
    {
        return array_map(
            fn (string $key, string $label): array => [
                'key' => $key,
                'label' => $label,
                'ios' => Duel::PLAYER_ICON_NAMES[$key]['ios'],
                'android' => Duel::PLAYER_ICON_NAMES[$key]['android'],
            ],
            array_keys(Duel::PLAYER_ICONS),
            array_values(Duel::PLAYER_ICONS),
        );
    }

    /**
     * Players in the order the user arranged them, falling back to the seat
     * order when no order was saved for this duel.
     *
     * @return Collection<int, DuelPlayer>
     */
    private function duelPlayersInLayoutOrder(): Collection
    {
        $players = $this->duel->duelPlayers()->orderBy('seat')->get();
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

        $this->duel->refresh();

        if ($this->hapticFeedbackEnabled && ! Device::vibrate()) {
            $this->hapticFeedbackEnabled = false;
            Setting::write('duel.feedback.haptics', '0');
            $this->addError('hapticFeedback', 'Vibração indisponível neste ambiente; a preferência foi desativada.');
        }
    }
    
    public function finishDuel(): void
    {
         $this->duel->finishDuel();    
    }
}
