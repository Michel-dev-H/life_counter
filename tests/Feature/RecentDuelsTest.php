<?php

use App\Models\Duel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;

uses(RefreshDatabase::class);

it('lists the most recent duels with their players', function () {
    $older = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);
    $newer = Duel::startWith([['name' => 'Carla']]);

    Native::visit('/duels')
        ->assertSee('Duelo #'.$newer->id)
        ->assertSee('Carla')
        ->assertSee('Duelo #'.$older->id)
        ->assertSee('Ana vs Bruno');
});

it('shows an empty state when no duel exists yet', function () {
    Native::visit('/duels')
        ->assertSee('Nenhum duelo ainda');
});

it('opens the arena of the chosen duel', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    Native::visit('/duels')
        ->tap('Duelo #'.$duel->id)
        ->assertNavigatedTo('/duel/'.$duel->id);
});
