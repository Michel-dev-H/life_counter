<?php

use App\Layouts\StackLayout;
use App\Layouts\TabsLayout;
use App\NativeComponents\AppearanceSettings;
use App\NativeComponents\CoinTools;
use App\NativeComponents\DiceTools;
use App\NativeComponents\DuelArena;
use App\NativeComponents\DuelSetup;
use App\NativeComponents\Home;
use App\NativeComponents\RecentDuels;
use App\NativeComponents\Reminders;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Native routes
|--------------------------------------------------------------------------
|
| These routes are loaded by nativephp/mobile only where something native
| will use them: on the device, in the test suite, in a Jump session and in
| the `native:*` commands. On a plain web server the file is never loaded,
| so the app stays device-only.
|
| Screens registered inside the TabsLayout group share its chrome (top bar +
| bottom tabs). Screens pushed on top of them are registered outside the
| group, so the framework swaps the chrome to a navigation stack.
|
*/

Route::nativeGroup(TabsLayout::class, function () {
    Route::native('/', Home::class)->name('home');
    Route::native('/duel/new', DuelSetup::class)->name('duel.setup');
    Route::native('/appearance/settings', AppearanceSettings::class)->name('appearance.edit');
});

// Pushed screens: registered outside the tab group, so the chrome swaps
// from tabs to a navigation stack.
Route::native('/duels', RecentDuels::class)->name('duels');
Route::native('/duel/{duel}', DuelArena::class)->name('duel.arena')->layout(StackLayout::class);
Route::native('/duel/{duel}/dice', DiceTools::class)->name('duel.dice')->layout(StackLayout::class);
Route::native('/duel/{duel}/coin', CoinTools::class)->name('duel.coin')->layout(StackLayout::class);
Route::native('/duel/{duel}/reminders', Reminders::class)->name('duel.reminders')->layout(StackLayout::class);
