<?php

use App\Enums\DuelPhase;
use App\Models\Duel;
use App\Models\Reminder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('flips a coin and records it in the duel history', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    $response = $this->postJson(route('duel.coin-flip', $duel))->assertOk();

    $result = $response->json('result');

    expect($result)->toBeIn(['heads', 'tails'])
        ->and($response->json('heads') + $response->json('tails'))->toBe(1)
        ->and($response->json('history.0.result'))->toBe($result)
        ->and($duel->coinFlips()->sole()->result)->toBe($result);
});

it('returns the coin flip history with updated totals', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);
    $duel->coinFlips()->create(['result' => 'tails']);

    $this->getJson(route('duel.coin-flips', $duel))
        ->assertOk()
        ->assertJsonPath('heads', 0)
        ->assertJsonPath('tails', 1)
        ->assertJsonCount(1, 'history');
});

it('rolls the requested number of dice within the supported range', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    $this->postJson(route('duel.dice-roll', $duel), ['count' => 99])
        ->assertOk()
        ->assertJsonPath('count', 10)
        ->assertJsonCount(10, 'results');

    $roll = $duel->diceRolls()->sole();

    expect($roll->dice_count)->toBe(10)
        ->and($roll->results)->toHaveCount(10)
        ->and($roll->total)->toBe(array_sum($roll->results));
});

it('returns the dice roll history for the duel', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);
    $duel->diceRolls()->create(['dice_count' => 2, 'results' => [2, 5], 'total' => 7]);

    $this->getJson(route('duel.dice-rolls', $duel))
        ->assertOk()
        ->assertJsonPath('history.0.count', 2)
        ->assertJsonPath('history.0.total', 7);
});

it('highlights active reminders that match the current turn and phase', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);
    $duel->update(['turn' => 2, 'phase' => DuelPhase::Battle]);

    $matching = Reminder::create([
        'duel_id' => $duel->id,
        'name' => 'Efeito ativo',
        'trigger_turn' => 2,
        'trigger_phase' => DuelPhase::Battle->value,
        'completed' => false,
    ]);
    Reminder::create([
        'duel_id' => $duel->id,
        'name' => 'Outro turno',
        'trigger_turn' => 5,
        'completed' => false,
    ]);
    Reminder::create([
        'duel_id' => $duel->id,
        'name' => 'Sem gatilho',
        'completed' => false,
    ]);

    $response = $this->getJson(route('duel.reminders', $duel))->assertOk();

    $reminders = collect($response->json('reminders'));

    expect($reminders->firstWhere('id', $matching->id)['highlighted'])->toBeTrue()
        ->and($reminders->where('highlighted', true))->toHaveCount(1);
});

it('creates, toggles and removes a reminder for the duel', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    $this->postJson(route('duel.reminders.store', $duel), [
        'name' => 'Lembrete',
        'description' => 'Descrição',
    ])->assertOk()->assertJsonCount(1, 'reminders');

    $reminder = $duel->reminders()->sole();

    expect($reminder->name)->toBe('Lembrete');

    $this->postJson(route('duel.reminders.toggle', [$duel, $reminder]))
        ->assertOk()
        ->assertJsonPath('reminders.0.completed', true);

    $this->deleteJson(route('duel.reminders.delete', [$duel, $reminder]))
        ->assertOk()
        ->assertJsonCount(0, 'reminders');

    expect($duel->reminders()->count())->toBe(0);
});
