<?php

use App\Livewire\DuelArena;
use App\Livewire\DuelSetup;
use App\Models\Duel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the duel setup page', function () {
    $this->get(route('duel.setup'))
        ->assertOk()
        ->assertSeeLivewire(DuelSetup::class);
});

it('starts the duel with the informed players and opens the arena', function () {
    Livewire::test(DuelSetup::class)
        ->set('playerNames', ['Ana', 'Bruno'])
        ->set('startingLifePoints', 4000)
        ->call('start')
        ->assertRedirect(route('duel.arena', Duel::sole()));

    $duel = Duel::sole();

    expect($duel->duelPlayers->pluck('name')->all())->toBe(['Ana', 'Bruno'])
        ->and($duel->duelPlayers->pluck('life_points')->all())->toBe([4000, 4000]);
});

it('starts with two empty player slots', function () {
    Livewire::test(DuelSetup::class)
        ->assertCount('playerNames', 2);
});

it('adds a player slot and never removes the last one', function () {
    Livewire::test(DuelSetup::class)
        ->call('addPlayer')
        ->assertCount('playerNames', 3)
        ->call('removePlayer', 0)
        ->call('removePlayer', 0)
        ->assertCount('playerNames', 1)
        ->call('removePlayer', 0)
        ->assertCount('playerNames', 1);
});

it('never adds more than eight player slots', function () {
    Livewire::test(DuelSetup::class)
        ->set('playerNames', array_fill(0, 8, 'Duelista'))
        ->call('addPlayer')
        ->assertCount('playerNames', 8);
});

it('rejects a duel without players', function () {
    Livewire::test(DuelSetup::class)
        ->set('playerNames', [])
        ->call('start')
        ->assertHasErrors('playerNames');

    expect(Duel::count())->toBe(0);
});

it('names the blank slots automatically', function () {
    Livewire::test(DuelSetup::class)
        ->set('playerNames', ['', '  '])
        ->call('start');

    expect(Duel::sole()->duelPlayers->pluck('name')->all())->toBe(['Jogador 1', 'Jogador 2']);
});

it('renders the arena of an existing duel', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    $this->get(route('duel.arena', $duel))
        ->assertOk()
        ->assertSeeLivewire(DuelArena::class)
        ->assertSee('Ana')
        ->assertSee('Bruno')
        ->assertSee('8000');
});

it('subtracts life points from the chosen player', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);
    $ana = $duel->duelPlayers()->where('seat', 0)->sole();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('subtract', $ana->id, 500)
        ->assertSee('7500');

    expect($ana->fresh()->life_points)->toBe(7500)
        ->and($duel->duelPlayers()->where('seat', 1)->sole()->life_points)->toBe(8000);
});

it('adds life points and halves them', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('add', $ana->id, 700)
        ->call('halve', $ana->id);

    expect($ana->fresh()->life_points)->toBe(4350);
});

it('restores the starting life points', function () {
    $duel = Duel::startWith([['name' => 'Ana']], 4000);
    $ana = $duel->duelPlayers()->sole();
    $ana->subtractLifePoints(2500);

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('restore', $ana->id);

    expect($ana->fresh()->life_points)->toBe(4000);
});

it('undoes and redoes the last change of the duel', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    $arena = Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('subtract', $ana->id, 900)
        ->call('undo');

    expect($ana->fresh()->life_points)->toBe(8000);

    $arena->call('redo');

    expect($ana->fresh()->life_points)->toBe(7100);
});

it('refuses to change a player from another duel', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $foreign = Duel::startWith([['name' => 'Intruso']])->duelPlayers->first();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('subtract', $foreign->id, 500)
        ->assertStatus(404);

    expect($foreign->fresh()->life_points)->toBe(8000);
});

it('renders every seat of an eight player duel', function () {
    $seats = array_map(fn (int $i) => ['name' => "Duelista {$i}"], range(1, 8));

    $duel = Duel::startWith($seats);

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->assertSee('P8')
        ->assertSee('Duelista 8');
});

it('shows the last life point change of each player', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();
    $ana->subtractLifePoints(1500);

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->assertSee('-1500');
});

it('keeps the arena shareable through a named route', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    expect(route('duel.arena', $duel))->toEndWith('/duel/'.$duel->id);
});
