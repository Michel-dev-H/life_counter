<?php

use App\Enums\DuelPhase;
use App\Models\Duel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;

uses(RefreshDatabase::class);

it('rolls the requested number of dice and records the roll', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    $screen = Native::visit('/duel/'.$duel->id.'/dice')
        ->set('diceCount', '3')
        ->tap('Lançar');

    $roll = $duel->diceRolls()->sole();

    expect($roll->dice_count)->toBe(3)
        ->and($roll->results)->toHaveCount(3)
        ->and($roll->total)->toBe(array_sum($roll->results));

    $this->travel(1)->seconds();

    $screen->firePolls()
        ->assertSee(implode(' · ', $roll->results))
        ->assertElement('text', fn (array $node) => ($node['props']['text'] ?? null) === (string) $roll->total);
});

it('tumbles the dice before showing the recorded results', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    $screen = Native::visit('/duel/'.$duel->id.'/dice')
        ->set('diceCount', '3')
        ->tap('Lançar');

    $roll = $duel->diceRolls()->sole();

    expect($screen->get('rollingStartedAt'))->toBeGreaterThan(0)
        ->and($screen->get('rollingDiceCount'))->toBe(3);

    $screen->assertMissingElement('text', fn (array $node) => ($node['props']['text'] ?? null) === (string) $roll->total);
});

it('renders one die face per rolled die', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    $screen = Native::visit('/duel/'.$duel->id.'/dice')
        ->set('diceCount', '10')
        ->tap('Lançar');

    $screen->assertElement('stack', fn (array $node) => str_starts_with($node['props']['a11y_label'] ?? '', 'Dado 10: '))
        ->assertMissingElement('stack', fn (array $node) => str_starts_with($node['props']['a11y_label'] ?? '', 'Dado 11: '));
});

it('clamps the dice count to the supported range', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    Native::visit('/duel/'.$duel->id.'/dice')
        ->set('diceCount', '99')
        ->tap('Lançar');

    expect($duel->diceRolls()->sole()->dice_count)->toBe(10);
});

it('lists the dice roll history', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $duel->diceRolls()->create(['dice_count' => 2, 'results' => [2, 5], 'total' => 7]);

    Native::visit('/duel/'.$duel->id.'/dice')
        ->assertSee('2 · 5')
        ->assertSee('7');
});

it('flips a coin and shows the result and the totals', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    $screen = Native::visit('/duel/'.$duel->id.'/coin')->tap('Lançar');

    $flip = $duel->coinFlips()->sole();

    $this->travel(1)->seconds();

    $screen->firePolls()
        ->assertElement('stack', fn (array $node) => ($node['props']['a11y_label'] ?? '') === 'Resultado da moeda: '.($flip->result === 'heads' ? 'Cara' : 'Coroa'))
        ->assertSee('Total: 1');
});

it('spins the coin before settling on the recorded face', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    $screen = Native::visit('/duel/'.$duel->id.'/coin')->tap('Lançar');

    expect($screen->get('flippingStartedAt'))->toBeGreaterThan(0);
});

it('lists the coin flip history', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $duel->coinFlips()->create(['result' => 'tails']);

    Native::visit('/duel/'.$duel->id.'/coin')
        ->assertSee('Coroa')
        ->assertSee('Coroa: 1');
});

it('creates a reminder of the duel', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    Native::visit('/duel/'.$duel->id.'/reminders')
        ->tap('Novo lembrete')
        ->set('name', 'Custo de manutenção')
        ->set('description', 'Pagar 700 pontos de vida')
        ->tap('Salvar');

    $reminder = $duel->reminders()->sole();

    expect($reminder->name)->toBe('Custo de manutenção')
        ->and($reminder->description)->toBe('Pagar 700 pontos de vida')
        ->and($reminder->completed)->toBeFalse();
});

it('rejects a reminder without a name', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    Native::visit('/duel/'.$duel->id.'/reminders')
        ->tap('Novo lembrete')
        ->tap('Salvar')
        ->assertSee('Informe o nome do lembrete.');

    expect($duel->reminders()->count())->toBe(0);
});

it('rejects a trigger phase outside the supported phases', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    Native::visit('/duel/'.$duel->id.'/reminders')
        ->tap('Novo lembrete')
        ->set('name', 'Lembrete')
        ->set('triggerPhase', 'XX')
        ->tap('Salvar')
        ->assertSee('Selecione uma fase válida.');

    expect($duel->reminders()->count())->toBe(0);
});

it('stores the turn and phase triggers of a reminder', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    Native::visit('/duel/'.$duel->id.'/reminders')
        ->tap('Novo lembrete')
        ->set('name', 'Limpar o campo')
        ->set('triggerTurn', '3')
        ->tap('EP')
        ->tap('Salvar');

    $reminder = $duel->reminders()->sole();

    expect($reminder->trigger_turn)->toBe(3)
        ->and($reminder->trigger_phase)->toBe(DuelPhase::End->value);
});

it('completes and deletes a reminder', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $reminder = $duel->reminders()->create(['name' => 'Lembrete']);

    Native::visit('/duel/'.$duel->id.'/reminders')
        ->press('delete('.$reminder->id.')');

    expect($duel->reminders()->count())->toBe(0);
});

it('marks a reminder as completed', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $reminder = $duel->reminders()->create(['name' => 'Lembrete']);

    Native::visit('/duel/'.$duel->id.'/reminders')
        ->toggle('toggle('.$reminder->id.')', true);

    expect($reminder->fresh()->completed)->toBeTrue();
});
