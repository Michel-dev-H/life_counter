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
        ->assertSee('Iniciar Duelo')
        ->assertSee('Aparência')
        ->assertSee(route('duel.setup'), false)
        ->assertSee(route('appearance.edit'), false);
});

it('keeps home and appearance navigation available from the arena', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    $this->get(route('duel.arena', $duel))
        ->assertOk()
        ->assertSee('Voltar para o início')
        ->assertSee(route('home'), false)
        ->assertSee(route('appearance.edit'), false);
});
