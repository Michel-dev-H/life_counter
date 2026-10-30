<?php

namespace App\Livewire;

use App\Models\Duel;
use App\Models\DuelPlayer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Configuração de Duelo')]
class DuelSetup extends Component
{
    /** @var array<int, string> */
    public array $playerNames = ['', ''];

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
            'startingLifePoints' => ['required', 'integer', 'min:1', 'max:'.DuelPlayer::MAX_LIFE_POINTS],
        ];
    }

    public function setPlayerCount(int $count): void
    {
        $count = max(Duel::MIN_PLAYERS, min(Duel::MAX_PLAYERS, $count));

        while (count($this->playerNames) < $count) {
            $this->playerNames[] = '';
        }

        $this->playerNames = array_slice($this->playerNames, 0, $count);
    }

    public function addPlayer(): void
    {
        if (count($this->playerNames) < Duel::MAX_PLAYERS) {
            $this->playerNames[] = '';
        }
    }

    public function removePlayer(int $index): void
    {
        if (count($this->playerNames) <= Duel::MIN_PLAYERS || ! array_key_exists($index, $this->playerNames)) {
            return;
        }

        unset($this->playerNames[$index]);
        $this->playerNames = array_values($this->playerNames);
    }

    public function start(): void
    {
        $this->validate();

        $duel = Duel::startWith(
            array_map(fn (string $name): array => ['name' => trim($name)], $this->playerNames),
            $this->startingLifePoints,
        );

        $this->redirectRoute('duel.arena', $duel);
    }

    public function render(): View
    {
        return view('livewire.duel-setup');
    }
}
