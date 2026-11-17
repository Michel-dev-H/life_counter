<?php

use App\NativeComponents\Home;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;

uses(RefreshDatabase::class);

it('shows the duel companion entry actions', function () {
    Native::test(Home::class)
        ->assertSee('DUELO')
        ->assertSee('Prepare sua arena')
        ->assertSee('Iniciar Duelo')
        ->assertSee('Duelos Recentes')
        ->assertSee('Aparência');
});

it('navigates to the duel setup', function () {
    Native::test(Home::class)
        ->tap('Iniciar Duelo')
        ->assertNavigatedTo('/duel/new');
});

it('navigates to the recent duels screen', function () {
    Native::test(Home::class)
        ->tap('Duelos Recentes')
        ->assertNavigatedTo('/duels');
});

it('navigates to the appearance settings', function () {
    Native::test(Home::class)
        ->tap('Aparência')
        ->assertNavigatedTo('/appearance/settings');
});

it('shows the app tab bar on the entry screens', function () {
    Native::visit('/')
        ->assertHasTab('Início')
        ->assertHasTab('Partida')
        ->assertTabActive('Início');

    Native::visit('/duel/new')
        ->assertHasTab('Início')
        ->assertHasTab('Partida')
        ->assertTabActive('Partida');
});
