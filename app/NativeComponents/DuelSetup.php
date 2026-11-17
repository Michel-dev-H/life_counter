<?php

namespace App\NativeComponents;

use App\Models\Duel;
use App\Models\DuelPlayer;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Native\Mobile\Edge\Concerns\ValidatesInput;
use Native\Mobile\Edge\NativeComponent;

class DuelSetup extends NativeComponent
{
    use ValidatesInput;

    /** @var array<int, string> */
    public array $playerNames = ['', ''];

    /** @var array<int, string> */
    public array $playerColors = ['cyan', 'gold'];

    /** @var array<int, string|null> */
    public array $playerIcons = [null, null];

    public string $startingLifePoints = '8000';

    /** @var array<int, string> */
    public const CAPACITY_LABELS = [
        1 => '1P',
        2 => 'Duelo',
        3 => 'Trio',
        4 => 'Tag/2v2',
        5 => 'Battle',
        6 => 'Cluster',
        7 => 'Lobby',
        8 => 'Torneio',
    ];

    /** @var array<int, array{value: int, label: string, hint: string}> */
    public const LIFE_POINT_PRESETS = [
        ['value' => 8000, 'label' => 'Padrão TCG', 'hint' => 'Match Oficial & Avançado'],
        ['value' => 4000, 'label' => 'Speed Duel', 'hint' => 'Anime / Duel Links'],
        ['value' => 16000, 'label' => 'Tag Team', 'hint' => '2v2 Compartilhado'],
    ];

    /**
     * Native icon name per platform for every player icon
     * (App\Models\Duel::PLAYER_ICON_NAMES).
     *
     * @var array<string, array{ios: string, android: string}>
     */
    public const PLAYER_ICON_NAMES = Duel::PLAYER_ICON_NAMES;

    public function navTitle(): string
    {
        return 'Configuração de Duelo';
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'playerNames' => ['required', 'array', 'min:'.Duel::MIN_PLAYERS, 'max:'.Duel::MAX_PLAYERS],
            'playerNames.*' => ['nullable', 'string', 'max:40'],
            'playerColors' => ['required', 'array', 'size:'.count($this->playerNames)],
            'playerColors.*' => ['required', 'string', Rule::in(array_keys(Duel::PLAYER_ACCENTS))],
            'playerIcons' => ['required', 'array', 'size:'.count($this->playerNames)],
            'playerIcons.*' => ['nullable', 'string', Rule::in(array_keys(Duel::PLAYER_ICONS))],
            'startingLifePoints' => ['required', 'integer', 'min:1', 'max:'.DuelPlayer::MAX_LIFE_POINTS],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'playerNames.required' => 'Selecione ao menos um jogador.',
            'playerNames.min' => 'Selecione ao menos um jogador.',
            'playerNames.max' => 'A arena comporta no máximo '.Duel::MAX_PLAYERS.' jogadores.',
            'playerNames.*.max' => 'O nome deve ter no máximo 40 caracteres.',
            'playerColors.*.required' => 'Selecione uma cor para cada jogador.',
            'playerColors.*.in' => 'Selecione uma cor de identificação disponível.',
            'playerIcons.*.in' => 'Selecione um ícone disponível.',
            'startingLifePoints.required' => 'Informe os pontos de vida iniciais.',
            'startingLifePoints.integer' => 'Informe um número inteiro de pontos de vida.',
            'startingLifePoints.min' => 'Os pontos de vida precisam ser pelo menos 1.',
            'startingLifePoints.max' => 'Os pontos de vida não podem exceder '.DuelPlayer::MAX_LIFE_POINTS.'.',
        ];
    }

    /**
     * Keep the identity options aligned with the player slots.
     *
     * The slots can change from the native inputs (one added, removed or
     * renamed), so the colours and icons are re-synced to the same size
     * whenever the names change — otherwise the screen would ask for a
     * colour or an icon that does not exist for that seat.
     */
    public function __syncProperty(string $property, mixed $value): void
    {
        parent::__syncProperty($property, $value);

        if (str_starts_with($property, 'playerNames')) {
            $this->syncIdentityOptions(count($this->playerNames));
        }
    }

    public function setPlayerCount(int $count): void
    {
        $count = max(Duel::MIN_PLAYERS, min(Duel::MAX_PLAYERS, $count));

        while (count($this->playerNames) < $count) {
            $this->playerNames[] = '';
        }

        $this->playerNames = array_slice($this->playerNames, 0, $count);
        $this->syncIdentityOptions($count);
    }

    public function addPlayer(): void
    {
        if (count($this->playerNames) < Duel::MAX_PLAYERS) {
            $this->playerNames[] = '';
            $this->syncIdentityOptions(count($this->playerNames));
        }
    }

    public function removePlayer(int $index): void
    {
        if (count($this->playerNames) <= Duel::MIN_PLAYERS || ! array_key_exists($index, $this->playerNames)) {
            return;
        }

        unset($this->playerNames[$index]);
        unset($this->playerColors[$index], $this->playerIcons[$index]);
        $this->playerNames = array_values($this->playerNames);
        $this->playerColors = array_values($this->playerColors);
        $this->playerIcons = array_values($this->playerIcons);
    }

    public function setPlayerColor(int $playerIndex, int $accentIndex): void
    {
        $accent = array_keys(Duel::PLAYER_ACCENTS)[$accentIndex] ?? null;

        if ($accent === null || ! array_key_exists($playerIndex, $this->playerColors)) {
            return;
        }

        $this->playerColors[$playerIndex] = $accent;
    }

    public function setPlayerIcon(int $playerIndex, int $iconIndex): void
    {
        $icon = array_keys(Duel::PLAYER_ICONS)[$iconIndex] ?? null;

        if ($icon === null || ! array_key_exists($playerIndex, $this->playerIcons)) {
            return;
        }

        $this->playerIcons[$playerIndex] = $icon;
    }

    public function setStartingLifePoints(int $value): void
    {
        $this->startingLifePoints = (string) $value;
    }

    public function start(): void
    {
        $this->validate();

        $seats = [];

        foreach (array_values($this->playerNames) as $index => $name) {
            $seats[] = [
                'name' => trim((string) $name),
                'color' => $this->playerColors[$index],
                'icon' => $this->playerIcons[$index],
            ];
        }

        $duel = Duel::startWith($seats, (int) $this->startingLifePoints);

        $this->navigate('/duel/'.$duel->id);
    }

    public function render(): View
    {
        return view('native.duel-setup', [
            'capacityLabels' => self::CAPACITY_LABELS,
            'lifePointPresets' => self::LIFE_POINT_PRESETS,
            'playerAccents' => DuelArena::playerAccentOptions(),
            'playerIcons' => DuelArena::playerIconOptions(),
            'maxPlayers' => Duel::MAX_PLAYERS,
        ]);
    }

    private function syncIdentityOptions(int $count): void
    {
        $this->playerColors = array_slice($this->playerColors, 0, $count);
        $this->playerIcons = array_slice($this->playerIcons, 0, $count);
        $accentOptions = array_keys(Duel::PLAYER_ACCENTS);

        while (count($this->playerColors) < $count) {
            $this->playerColors[] = $accentOptions[count($this->playerColors) % count($accentOptions)];
        }

        while (count($this->playerIcons) < $count) {
            $this->playerIcons[] = null;
        }
    }
}
