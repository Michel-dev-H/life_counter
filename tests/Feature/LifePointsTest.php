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

it('undoes the last change', function () {
    $player = duelPlayerWith(8000);
    $player->subtractLifePoints(1500);

    expect($player->undoLifePointChange())->toBeTrue()
        ->and($player->fresh()->life_points)->toBe(8000)
        ->and($player->lifePointEvents()->sole()->undone_at)->not->toBeNull();
});

it('undoes several changes in order', function () {
    $player = duelPlayerWith(8000);
    $player->subtractLifePoints(1000);
    $player->subtractLifePoints(500);

    $player->undoLifePointChange();
    expect($player->fresh()->life_points)->toBe(7000);

    $player->undoLifePointChange();
    expect($player->fresh()->life_points)->toBe(8000);
});

it('redoes an undone change', function () {
    $player = duelPlayerWith(8000);
    $player->subtractLifePoints(1500);
    $player->undoLifePointChange();

    expect($player->redoLifePointChange())->toBeTrue()
        ->and($player->fresh()->life_points)->toBe(6500)
        ->and($player->lifePointEvents()->sole()->undone_at)->toBeNull();
});

it('reports nothing to undo or redo', function () {
    $player = duelPlayerWith(8000);

    expect($player->undoLifePointChange())->toBeFalse()
        ->and($player->redoLifePointChange())->toBeFalse();
});

it('discards the redo history when a new change happens', function () {
    $player = duelPlayerWith(8000);
    $player->subtractLifePoints(1500);
    $player->undoLifePointChange();

    $player->subtractLifePoints(200);

    expect($player->lifePointEvents()->count())->toBe(1)
        ->and($player->redoLifePointChange())->toBeFalse()
        ->and($player->fresh()->life_points)->toBe(7800);
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
