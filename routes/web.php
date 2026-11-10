<?php

use App\Http\Controllers\DuelToolsController;
use App\Livewire\AppearanceSettings;
use App\Livewire\DuelArena;
use App\Livewire\DuelSetup;
use App\Livewire\HomeDashboard;
use Illuminate\Support\Facades\Route;

Route::livewire('/', HomeDashboard::class)->name('home');

Route::livewire('/duel/new', DuelSetup::class)->name('duel.setup');

Route::livewire('/duel/{duel}', DuelArena::class)->name('duel.arena');

Route::livewire('/appearance/settings', AppearanceSettings::class)->name('appearance.edit');

// Duel tools (fetch-based modals; kept outside the Livewire arena to avoid nested components)
Route::post('/duel/{duel}/coin-flip', [DuelToolsController::class, 'flipCoin'])->name('duel.coin-flip');
Route::get('/duel/{duel}/coin-flips', [DuelToolsController::class, 'coinFlipHistory'])->name('duel.coin-flips');

Route::post('/duel/{duel}/dice-roll', [DuelToolsController::class, 'rollDice'])->name('duel.dice-roll');
Route::get('/duel/{duel}/dice-rolls', [DuelToolsController::class, 'diceHistory'])->name('duel.dice-rolls');

Route::get('/duel/{duel}/reminders', [DuelToolsController::class, 'listReminders'])->name('duel.reminders');
Route::post('/duel/{duel}/reminders', [DuelToolsController::class, 'saveReminder'])->name('duel.reminders.store');
Route::post('/duel/{duel}/reminders/{reminder}/toggle', [DuelToolsController::class, 'toggleReminder'])->name('duel.reminders.toggle');
Route::delete('/duel/{duel}/reminders/{reminder}', [DuelToolsController::class, 'deleteReminder'])->name('duel.reminders.delete');
