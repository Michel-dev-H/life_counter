<?php

use App\Models\DuelPlayer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function duelPlayerWith(int $lifePoints, ?int $startingLifePoints = null): DuelPlayer
{
    return DuelPlayer::factory()->create([
        'starting_life_points' => $startingLifePoints ?? $lifePoints,
        'life_points' => $lifePoints,
    ]);
}

it('subtracts life points and records the change', function () {
    $player = duelPlayerWith(8000);

    $player->subtractLifePoints(1500);

    $event = $player->lifePointEvents()->sole();

    expect($player->fresh()->life_points)->toBe(6500)
        ->and($event->before)->toBe(8000)
        ->and($event->after)->toBe(6500)
        ->and($event->amount)->toBe(1500);
});

it('adds life points', function () {
    $player = duelPlayerWith(3000);

    $player->addLifePoints(750);

    expect($player->fresh()->life_points)->toBe(3750);
});

it('never drops below zero', function () {
    $player = duelPlayerWith(8000);

    $player->subtractLifePoints(12000);

    expect($player->fresh()->life_points)->toBe(0);
});

it('allows life points above the starting value', function () {
    $player = duelPlayerWith(8000);

    $player->addLifePoints(2500);

    expect($player->fresh()->life_points)->toBe(10500);
});

it('sets life points directly', function () {
    $player = duelPlayerWith(8000);

    $player->setLifePointsTo(1200);

    expect($player->fresh()->life_points)->toBe(1200);
});

it('halves rounding up', function () {
    $player = duelPlayerWith(2501);

    $player->halveLifePoints();

    expect($player->fresh()->life_points)->toBe(1251);
});

it('halves an even value exactly', function () {
    $player = duelPlayerWith(2500);

    $player->halveLifePoints();

    expect($player->fresh()->life_points)->toBe(1250);
});

it('doubles life points', function () {
    $player = duelPlayerWith(3200);

    $player->doubleLifePoints();

    expect($player->fresh()->life_points)->toBe(6400);
});

it('restores the starting life points', function () {
    $player = duelPlayerWith(1000, 8000);

    $player->restoreStartingLifePoints();

    expect($player->fresh()->life_points)->toBe(8000);
});

it('caps life points at the safe maximum', function () {
    $player = duelPlayerWith(999000);

    $player->doubleLifePoints();

    expect($player->fresh()->life_points)->toBe(DuelPlayer::MAX_LIFE_POINTS);
});

it('rejects a non positive amount', function () {
    $player = duelPlayerWith(8000);

    expect(fn () => $player->subtractLifePoints(0))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects an operation amount above the supported maximum', function (string $operation) {
    $player = duelPlayerWith(8000);

    expect(fn () => $player->{$operation}(DuelPlayer::MAX_LIFE_POINTS + 1))
        ->toThrow(InvalidArgumentException::class, 'O valor não pode exceder '.DuelPlayer::MAX_LIFE_POINTS.'.');
})->with([
    'adding' => 'addLifePoints',
    'subtracting' => 'subtractLifePoints',
]);

it('rejects an integer amount that would overflow the life point calculation', function () {
    $player = duelPlayerWith(8000);

    expect(fn () => $player->addLifePoints(PHP_INT_MAX))
        ->toThrow(InvalidArgumentException::class, 'O valor não pode exceder '.DuelPlayer::MAX_LIFE_POINTS.'.');
});

it('does not record an operation that leaves life points unchanged', function (int $lifePoints, string $operation) {
    $player = duelPlayerWith($lifePoints);

    $player->{$operation}(1);

    expect($player->fresh()->life_points)->toBe($lifePoints)
        ->and($player->lifePointEvents()->count())->toBe(0);
})->with([
    'at the maximum' => [DuelPlayer::MAX_LIFE_POINTS, 'addLifePoints'],
    'at zero' => [DuelPlayer::MIN_LIFE_POINTS, 'subtractLifePoints'],
]);

it('preserves redo history when an operation does not change life points', function () {
    $player = duelPlayerWith(DuelPlayer::MAX_LIFE_POINTS);
    $player->subtractLifePoints(1);
    $player->duel->undoLastLifePointChange();

    $player->addLifePoints(1);

    expect($player->fresh()->life_points)->toBe(DuelPlayer::MAX_LIFE_POINTS)
        ->and($player->lifePointEvents()->count())->toBe(1)
        ->and($player->duel->redoLastLifePointChange())->toBeTrue()
        ->and($player->fresh()->life_points)->toBe(DuelPlayer::MAX_LIFE_POINTS - 1);
});

it('undoes the last change of the duel', function () {
    $player = duelPlayerWith(8000);
    $player->subtractLifePoints(1500);

    expect($player->duel->undoLastLifePointChange())->toBeTrue()
        ->and($player->fresh()->life_points)->toBe(8000)
        ->and($player->lifePointEvents()->sole()->undone_at)->not->toBeNull();
});

it('undoes several changes in order', function () {
    $player = duelPlayerWith(8000);
    $player->subtractLifePoints(1000);
    $player->subtractLifePoints(500);

    $player->duel->undoLastLifePointChange();
    expect($player->fresh()->life_points)->toBe(7000);

    $player->duel->undoLastLifePointChange();
    expect($player->fresh()->life_points)->toBe(8000);
});

it('redoes an undone change', function () {
    $player = duelPlayerWith(8000);
    $player->subtractLifePoints(1500);
    $player->duel->undoLastLifePointChange();

    expect($player->duel->redoLastLifePointChange())->toBeTrue()
        ->and($player->fresh()->life_points)->toBe(6500)
        ->and($player->lifePointEvents()->sole()->undone_at)->toBeNull();
});

it('redoes several undone changes in order', function () {
    $player = duelPlayerWith(8000);
    $player->subtractLifePoints(1000);
    $player->subtractLifePoints(500);

    $player->duel->undoLastLifePointChange();
    $player->duel->undoLastLifePointChange();

    $player->duel->redoLastLifePointChange();
    expect($player->fresh()->life_points)->toBe(7000);

    $player->duel->redoLastLifePointChange();
    expect($player->fresh()->life_points)->toBe(6500);
});

it('reports nothing to undo or redo', function () {
    $player = duelPlayerWith(8000);

    expect($player->duel->undoLastLifePointChange())->toBeFalse()
        ->and($player->duel->redoLastLifePointChange())->toBeFalse();
});

it('discards the redo history when a new change happens', function () {
    $player = duelPlayerWith(8000);
    $player->subtractLifePoints(1500);
    $player->duel->undoLastLifePointChange();

    $player->subtractLifePoints(200);

    expect($player->lifePointEvents()->count())->toBe(1)
        ->and($player->duel->redoLastLifePointChange())->toBeFalse()
        ->and($player->fresh()->life_points)->toBe(7800);
});

it('undoes changes of different players in reverse order', function () {
    $first = duelPlayerWith(8000);
    $second = DuelPlayer::factory()->create([
        'duel_id' => $first->duel_id,
        'seat' => 1,
        'starting_life_points' => 8000,
        'life_points' => 8000,
    ]);

    $first->subtractLifePoints(1000);
    $second->subtractLifePoints(500);

    $first->duel->undoLastLifePointChange();
    expect($second->fresh()->life_points)->toBe(8000)
        ->and($first->fresh()->life_points)->toBe(7000);

    $first->duel->undoLastLifePointChange();
    expect($first->fresh()->life_points)->toBe(8000);
});

it('discards the redo history of the whole duel when a new change happens', function () {
    $first = duelPlayerWith(8000);
    $second = DuelPlayer::factory()->create([
        'duel_id' => $first->duel_id,
        'seat' => 1,
        'starting_life_points' => 8000,
        'life_points' => 8000,
    ]);

    $first->subtractLifePoints(1000);
    $first->duel->undoLastLifePointChange();

    $second->subtractLifePoints(500);

    expect($first->duel->redoLastLifePointChange())->toBeFalse()
        ->and($first->fresh()->life_points)->toBe(8000);
});

it('keeps players isolated from each other', function () {
    $attacker = duelPlayerWith(8000);
    $defender = DuelPlayer::factory()->create([
        'duel_id' => $attacker->duel_id,
        'seat' => 1,
        'starting_life_points' => 8000,
        'life_points' => 8000,
    ]);

    $attacker->subtractLifePoints(1500);

    expect($defender->fresh()->life_points)->toBe(8000)
        ->and($defender->lifePointEvents()->count())->toBe(0);
});
