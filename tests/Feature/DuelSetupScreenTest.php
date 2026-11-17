<?php

use App\Models\Duel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;

uses(RefreshDatabase::class);

it('shows the arena capacity, the life point presets and the player slots', function () {
    Native::visit('/duel/new')
        ->assertSee('Capacidade da Arena')
        ->assertSee('Duelo')
        ->assertSee('Padrão TCG')
        ->assertSee('P1')
        ->assertSee('P2');
});

it('starts with two empty player slots and the default identity', function () {
    $screen = Native::visit('/duel/new');

    expect($screen->get('playerNames'))->toBe(['', ''])
        ->and($screen->get('playerColors'))->toBe(['cyan', 'gold'])
        ->and($screen->get('playerIcons'))->toBe([null, null]);
});

it('changes the number of players from the capacity options', function () {
    Native::visit('/duel/new')
        ->tap('4 · Tag/2v2')
        ->assertSee('P4')
        ->assertSee('Remover jogador 4');
});

it('adds and removes a player slot', function () {
    Native::visit('/duel/new')
        ->tap('Adicionar Duelista')
        ->assertSee('P3')
        ->tap('Remover jogador 3')
        ->assertDontSee('P3');
});

it('picks the colour and the icon of a player', function () {
    $screen = Native::visit('/duel/new')
        ->tap('Cor Rosa intenso')
        ->tap('Ícone Estrela');

    expect($screen->get('playerColors'))->toBe(['rose', 'gold'])
        ->and($screen->get('playerIcons'))->toBe(['star', null]);
});

it('rejects a player name longer than forty characters', function () {
    Native::visit('/duel/new')
        ->set('playerNames.0', str_repeat('a', 41))
        ->tap('Iniciar Arena de Duelo')
        ->assertSee('O nome deve ter no máximo 40 caracteres.');

    expect(Duel::count())->toBe(0);
});

it('creates the duel with the chosen seats and life points', function () {
    Native::visit('/duel/new')
        ->set('playerNames.0', 'Ana')
        ->set('playerNames.1', 'Bruno')
        ->set('startingLifePoints', '4000')
        ->tap('Iniciar Arena de Duelo');

    $duel = Duel::sole();

    expect($duel->duelPlayers->pluck('name')->all())->toBe(['Ana', 'Bruno'])
        ->and($duel->duelPlayers->pluck('color')->all())->toBe(['cyan', 'gold'])
        ->and($duel->duelPlayers->pluck('life_points')->all())->toBe([4000, 4000]);
});

it('falls back to the default name when a slot is left empty', function () {
    Native::visit('/duel/new')
        ->tap('Iniciar Arena de Duelo');

    expect(Duel::sole()->duelPlayers->pluck('name')->all())->toBe(['Jogador 1', 'Jogador 2']);
});

it('navigates to the arena of the created duel', function () {
    $screen = Native::visit('/duel/new')
        ->set('playerNames.0', 'Ana')
        ->set('playerNames.1', 'Bruno')
        ->tap('Iniciar Arena de Duelo');

    $screen->assertNavigatedTo('/duel/'.Duel::sole()->id);
});

it('persists each player identity in its own seat when starting a duel', function () {
    Native::visit('/duel/new')
        ->set('playerNames.0', 'Ana')
        ->set('playerNames.1', 'Bruno')
        ->set('playerColors.0', 'violet')
        ->set('playerColors.1', 'mint')
        ->set('playerIcons.0', 'shield')
        ->set('playerIcons.1', 'star')
        ->tap('Iniciar Arena de Duelo');

    $players = Duel::sole()->duelPlayers()->orderBy('seat')->get();

    expect($players->pluck('name')->all())->toBe(['Ana', 'Bruno'])
        ->and($players->pluck('seat')->all())->toBe([0, 1])
        ->and($players->pluck('color')->all())->toBe(['violet', 'mint'])
        ->and($players->pluck('icon')->all())->toBe(['shield', 'star']);
});

it('keeps each player identity aligned with their name when removing a seat', function () {
    $screen = Native::visit('/duel/new')
        ->set('playerNames.0', 'Ana')
        ->set('playerNames.1', 'Bruno')
        ->set('playerNames.2', 'Caio')
        ->set('playerColors.0', 'cyan')
        ->set('playerColors.1', 'violet')
        ->set('playerColors.2', 'mint')
        ->set('playerIcons.0', null)
        ->set('playerIcons.1', 'shield')
        ->set('playerIcons.2', 'star')
        ->tap('Remover jogador 2');

    expect($screen->get('playerNames'))->toBe(['Ana', 'Caio'])
        ->and($screen->get('playerColors'))->toBe(['cyan', 'mint'])
        ->and($screen->get('playerIcons'))->toBe([null, 'star']);
});

it('never removes the last player slot', function () {
    $screen = Native::visit('/duel/new')
        ->tap('Remover jogador 1');

    expect($screen->get('playerNames'))->toHaveCount(1);

    $screen->tap('Remover jogador 1');

    expect($screen->get('playerNames'))->toHaveCount(1);
});

it('never adds more than eight player slots', function () {
    $screen = Native::visit('/duel/new')
        ->set('playerNames', array_fill(0, 8, 'Duelista'))
        ->call('addPlayer');

    expect($screen->get('playerNames'))->toHaveCount(8);
});

it('rejects a duel without players before creating it', function () {
    Native::visit('/duel/new')
        ->set('playerNames', [])
        ->tap('Iniciar Arena de Duelo')
        ->assertSee('Selecione ao menos um jogador.');

    expect(Duel::count())->toBe(0);
});

it('rejects an unsupported player colour before creating a duel', function () {
    Native::visit('/duel/new')
        ->set('playerColors.0', 'not-a-color')
        ->tap('Iniciar Arena de Duelo')
        ->assertSee('Selecione uma cor de identificação disponível.');

    expect(Duel::count())->toBe(0);
});

it('rejects an unsupported player icon before creating a duel', function () {
    Native::visit('/duel/new')
        ->set('playerIcons.0', 'not-an-icon')
        ->tap('Iniciar Arena de Duelo')
        ->assertSee('Selecione um ícone disponível.');

    expect(Duel::count())->toBe(0);
});

it('rejects starting life points outside the supported range', function (int $lifePoints, string $message) {
    Native::visit('/duel/new')
        ->set('startingLifePoints', (string) $lifePoints)
        ->tap('Iniciar Arena de Duelo')
        ->assertSee($message);

    expect(Duel::count())->toBe(0);
})->with([
    'zero' => [0, 'Os pontos de vida precisam ser pelo menos 1.'],
    'above the limit' => [1000000, 'Os pontos de vida não podem exceder 999999.'],
]);
