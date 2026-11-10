<?php

use App\Livewire\DuelArena;
use App\Livewire\DuelDice;
use App\Models\Duel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('provides a dice roller inside each duel arena', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    $this->get(route('duel.arena', $duel))
        ->assertOk()
        ->assertSeeLivewire(DuelArena::class)
        ->assertSee('Lançar dados');
});

it('records the requested number of six-sided dice in the current duel', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    Livewire::test(DuelDice::class, ['duel' => $duel])
        ->call('openRoller')
        ->set('diceCount', 4)
        ->call('rollDice')
        ->assertHasNoErrors()
        ->assertSee('Dado 1')
        ->assertSee('Soma:');

    $roll = $duel->diceRolls()->sole();

    expect($roll->dice_count)->toBe(4)
        ->and($roll->results)->toHaveCount(4)
        ->and(array_filter($roll->results, fn (int $result): bool => $result < 1 || $result > 6))->toBe([])
        ->and($roll->total)->toBe(array_sum($roll->results));
});

it('accepts both supported dice quantity boundaries', function (int $diceCount) {
    $duel = Duel::startWith([['name' => 'Ana']]);

    Livewire::test(DuelDice::class, ['duel' => $duel])
        ->set('diceCount', $diceCount)
        ->call('rollDice')
        ->assertHasNoErrors();

    expect($duel->diceRolls()->sole()->results)->toHaveCount($diceCount);
})->with([
    'one die' => 1,
    'ten dice' => 10,
]);

it('keeps the quantity controls within one and ten dice', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    Livewire::test(DuelDice::class, ['duel' => $duel])
        ->call('decreaseDiceCount')
        ->assertSet('diceCount', 1)
        ->set('diceCount', 10)
        ->call('increaseDiceCount')
        ->assertSet('diceCount', 10)
        ->call('decreaseDiceCount')
        ->assertSet('diceCount', 9);
});

it('rejects dice quantities outside the supported range without recording a roll', function (int $diceCount) {
    $duel = Duel::startWith([['name' => 'Ana']]);

    Livewire::test(DuelDice::class, ['duel' => $duel])
        ->set('diceCount', $diceCount)
        ->call('rollDice')
        ->assertHasErrors('diceCount')
        ->assertSee('Escolha de 1 a 10 dados.');

    $this->assertDatabaseCount('dice_rolls', 0);
})->with([
    'zero dice' => 0,
    'more than ten dice' => 11,
]);

it('repeats the last quantity and keeps rolls isolated to their duel', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $otherDuel = Duel::startWith([['name' => 'Bruno']]);

    Livewire::test(DuelDice::class, ['duel' => $duel])
        ->set('diceCount', 3)
        ->call('rollDice')
        ->call('repeatDiceRoll')
        ->assertHasNoErrors()
        ->assertSee('Repetir 3 dados');

    expect($duel->diceRolls()->count())->toBe(2)
        ->and($duel->diceRolls()->orderBy('id')->pluck('dice_count')->all())->toBe([3, 3])
        ->and($otherDuel->diceRolls()->count())->toBe(0);

    Livewire::test(DuelDice::class, ['duel' => $otherDuel])
        ->call('repeatDiceRoll')
        ->assertHasErrors('dice');

    expect($otherDuel->diceRolls()->count())->toBe(0);
});

it('shows persisted roll history and lets the player hide the total', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    Livewire::test(DuelDice::class, ['duel' => $duel])
        ->set('diceCount', 2)
        ->call('rollDice')
        ->call('rollDice')
        ->assertSee('Histórico')
        ->assertSee('Soma:')
        ->assertDontSee('Ainda não há lançamentos anteriores nesta partida.');

    Livewire::test(DuelDice::class, ['duel' => $duel])
        ->call('openRoller')
        ->assertSee('Histórico recente')
        ->assertDontSee('Ainda não há lançamentos anteriores nesta partida.')
        ->assertSee('Soma:')
        ->set('showTotal', false)
        ->assertDontSee('Soma:');

    expect($duel->diceRolls()->count())->toBe(2)
        ->and($duel->diceRolls()->latest('id')->firstOrFail()->results)->toHaveCount(2);
});

it('shows an error when asked to repeat before any roll exists', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    Livewire::test(DuelDice::class, ['duel' => $duel])
        ->call('repeatDiceRoll')
        ->assertHasErrors('dice')
        ->assertSee('Ainda não há lançamento para repetir.');

    expect($duel->diceRolls()->count())->toBe(0);
});
