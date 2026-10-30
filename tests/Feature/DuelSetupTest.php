<?php

use App\Models\Duel;
use App\Models\Player;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('starts a duel with one player', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    expect($duel->duelPlayers)->toHaveCount(1)
        ->and($duel->duelPlayers->first()->name)->toBe('Ana')
        ->and($duel->duelPlayers->first()->life_points)->toBe(8000)
        ->and($duel->duelPlayers->first()->starting_life_points)->toBe(8000);
});

it('starts a duel with eight players', function () {
    $seats = array_map(fn (int $i) => ['name' => "Jogador {$i}"], range(1, 8));

    $duel = Duel::startWith($seats);

    expect($duel->duelPlayers)->toHaveCount(8);
});

it('rejects a duel without players or with more than eight', function (int $count) {
    $seats = array_map(fn (int $i) => ['name' => "Jogador {$i}"], range(1, max($count, 1)));

    expect(fn () => Duel::startWith(array_slice($seats, 0, $count)))
        ->toThrow(InvalidArgumentException::class);
})->with([0, 9]);

it('seats players in the order they were given', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno'], ['name' => 'Caio']]);

    expect($duel->duelPlayers->pluck('seat')->all())->toBe([0, 1, 2])
        ->and($duel->duelPlayers->pluck('name')->all())->toBe(['Ana', 'Bruno', 'Caio']);
});

it('links a registered player and copies their name', function () {
    $player = Player::factory()->create(['name' => 'Michel']);

    $duel = Duel::startWith([['player_id' => $player->id]]);

    expect($duel->duelPlayers->first()->player_id)->toBe($player->id)
        ->and($duel->duelPlayers->first()->name)->toBe('Michel');
});

it('uses the custom starting life points', function () {
    $duel = Duel::startWith([['name' => 'Ana']], 4000);

    expect($duel->duelPlayers->first()->life_points)->toBe(4000)
        ->and($duel->duelPlayers->first()->starting_life_points)->toBe(4000);
});
