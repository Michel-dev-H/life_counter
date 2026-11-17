<?php

use App\Enums\DuelPhase;
use App\Models\Duel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('flips a coin and records it in the duel history', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    $flip = $duel->flipCoin();

    expect($flip->result)->toBeIn(['heads', 'tails'])
        ->and($duel->coinFlips()->sole()->result)->toBe($flip->result)
        ->and($duel->coinFlipCounts())->toBe([
            'heads' => $flip->result === 'heads' ? 1 : 0,
            'tails' => $flip->result === 'tails' ? 1 : 0,
        ]);
});

it('counts the coin flips of the duel', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $duel->coinFlips()->create(['result' => 'tails']);
    $duel->coinFlips()->create(['result' => 'heads']);

    expect($duel->coinFlipCounts())->toBe(['heads' => 1, 'tails' => 1]);
});

it('rolls the requested number of dice within the supported range', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    $roll = $duel->rollDice(99);

    expect($roll->dice_count)->toBe(10)
        ->and($roll->results)->toHaveCount(10)
        ->and($roll->total)->toBe(array_sum($roll->results));

    $single = $duel->rollDice(0);

    expect($single->dice_count)->toBe(1)
        ->and($single->results)->toHaveCount(1);
});

it('keeps dice rolls isolated to their duel and within six-sided range', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $otherDuel = Duel::startWith([['name' => 'Bruno']]);

    $roll = $duel->rollDice(3);

    expect($roll->results)->toHaveCount(3)
        ->and(array_filter($roll->results, fn (int $result): bool => $result < 1 || $result > 6))->toBe([])
        ->and($otherDuel->diceRolls()->count())->toBe(0);
});

it('highlights the reminders whose trigger matches the current turn and phase', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    $matching = $duel->reminders()->create(['name' => 'Custo de manutenção', 'trigger_turn' => 1]);
    $otherTurn = $duel->reminders()->create(['name' => 'Preparar o campo', 'trigger_turn' => 3]);
    $otherPhase = $duel->reminders()->create(['name' => 'Limpar o campo', 'trigger_phase' => DuelPhase::End->value]);
    $noTrigger = $duel->reminders()->create(['name' => 'Sempre visível']);
    $completed = $duel->reminders()->create(['name' => 'Já concluído', 'trigger_turn' => 1, 'completed' => true]);

    expect($duel->highlightedReminderIds())->toBe([$matching->id]);
});

it('highlights a reminder that matches only the current phase', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $duel->setPhase(DuelPhase::End);

    $matching = $duel->reminders()->create(['name' => 'Limpar o campo', 'trigger_phase' => DuelPhase::End->value]);

    expect($duel->highlightedReminderIds())->toBe([$matching->id]);
});

it('does not highlight a completed reminder', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $duel->reminders()->create(['name' => 'Já concluído', 'trigger_turn' => 1, 'completed' => true]);

    expect($duel->highlightedReminderIds())->toBe([]);
});

it('orders the reminders with the pending ones first', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $duel->reminders()->create(['name' => 'Concluído', 'completed' => true]);
    $duel->reminders()->create(['name' => 'Pendente']);

    $names = $duel->reminders()->orderBy('completed')->orderBy('created_at')->pluck('name')->all();

    expect($names)->toBe(['Pendente', 'Concluído']);
});

it('toggles and deletes a reminder of the duel', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $reminder = $duel->reminders()->create(['name' => 'Lembrete']);

    $reminder->update(['completed' => true]);
    expect($reminder->fresh()->completed)->toBeTrue();

    $duel->reminders()->where('id', $reminder->id)->delete();
    expect($duel->reminders()->count())->toBe(0);
});
