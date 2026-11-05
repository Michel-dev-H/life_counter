<?php

namespace App\Livewire;

use App\Models\Duel;
use App\Models\DuelPlayer;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuração de Duelo')]
class DuelSetup extends Component
{
    /** @var array<int, string> */
    public array $playerNames = ['', ''];

    /** @var array<int, string> */
    public array $playerColors = ['cyan', 'gold'];

    /** @var array<int, string|null> */
    public array $playerIcons = [null, null];

    public int $startingLifePoints = 8000;

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

    /**
     * @return array<string, array<int, string>>
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
            'playerColors.*.required' => 'Selecione uma cor para cada jogador.',
            'playerColors.*.in' => 'Selecione uma cor de identificação disponível.',
            'playerIcons.*.in' => 'Selecione um ícone disponível.',
        ];
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

        $duel = Duel::startWith($seats, $this->startingLifePoints);

        $this->redirectRoute('duel.arena', $duel);
    }

    public function render(): View
    {
        return view('livewire.duel-setup');
    }

    private function syncIdentityOptions(int $count): void
    {
        $this->playerColors = array_values(array_slice($this->playerColors, 0, $count));
        $this->playerIcons = array_values(array_slice($this->playerIcons, 0, $count));
        $accentOptions = array_keys(Duel::PLAYER_ACCENTS);

        while (count($this->playerColors) < $count) {
            $this->playerColors[] = $accentOptions[count($this->playerColors) % count($accentOptions)];
        }

        while (count($this->playerIcons) < $count) {
            $this->playerIcons[] = null;
        }
    }
}
