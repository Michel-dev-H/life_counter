<?php

use App\Livewire\DuelArena;
use App\Livewire\DuelSetup;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/duel/new')->name('home');

Route::livewire('/duel/new', DuelSetup::class)->name('duel.setup');

Route::livewire('/duel/{duel}', DuelArena::class)->name('duel.arena');

Route::livewire('/appearance/settings', AppearanceSettings::class)->name('appearance.edit');
