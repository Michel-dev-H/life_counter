<?php

use App\Livewire\HomeDashboard;
use App\Models\Duel;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the working app sections on the home screen', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSeeLivewire(HomeDashboard::class)
        ->assertSee('Início')
        ->assertSee('Nova partida')
        ->assertSee('Aparência')
        ->assertSee(route('duel.setup'), false)
        ->assertSee(route('appearance.edit'), false);
});

it('shows recent duels with links back to their arenas', function () {
    $previousDuel = Duel::startWith([['name' => 'Duelista anterior']]);
    $recentDuel = Duel::startWith([['name' => 'Duelista recente'], ['name' => 'Rival recente']]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder([
            'Duelo #'.$recentDuel->id,
            'Duelista recente',
            'Duelo #'.$previousDuel->id,
            'Duelista anterior',
        ])
        ->assertSee(route('duel.arena', $recentDuel), false);
});

it('offers a new duel when no previous duels exist', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Nenhum duelo iniciado ainda.')
        ->assertSee(route('duel.setup'), false);
});

it('keeps home and appearance navigation available from the arena', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    $this->get(route('duel.arena', $duel))
        ->assertOk()
        ->assertSee('Voltar para o início')
        ->assertSee(route('home'), false)
        ->assertSee(route('appearance.edit'), false);
});
