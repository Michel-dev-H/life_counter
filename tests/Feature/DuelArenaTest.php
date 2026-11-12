<?php

use App\Enums\DuelPhase;
use App\Livewire\DuelArena;
use App\Livewire\DuelSetup;
use App\Models\Duel;
use App\Models\DuelPlayer;
use App\Models\Player;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Native\Mobile\Facades\Device;

uses(RefreshDatabase::class);

it('renders the duel setup page', function () {
    $this->get(route('duel.setup'))
        ->assertOk()
        ->assertSeeLivewire(DuelSetup::class)
        ->assertSee('Cor de identificação do jogador 1')
        ->assertSee('Ícone do jogador 1');
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

it('persists each player identity in its own seat when starting a duel', function () {
    Livewire::test(DuelSetup::class)
        ->set('playerNames', ['Ana', 'Bruno'])
        ->set('playerColors', ['violet', 'mint'])
        ->set('playerIcons', ['shield', 'star'])
        ->call('start')
        ->assertRedirect(route('duel.arena', Duel::sole()));

    $players = Duel::sole()->duelPlayers()->orderBy('seat')->get();

    expect($players->pluck('name')->all())->toBe(['Ana', 'Bruno'])
        ->and($players->pluck('seat')->all())->toBe([0, 1])
        ->and($players->pluck('color')->all())->toBe(['violet', 'mint'])
        ->and($players->pluck('icon')->all())->toBe(['shield', 'star']);
});

it('keeps each player identity aligned with their name when removing a seat', function () {
    $setup = Livewire::test(DuelSetup::class)
        ->set('playerNames', ['Ana', 'Bruno', 'Caio'])
        ->set('playerColors', ['cyan', 'violet', 'mint'])
        ->set('playerIcons', [null, 'shield', 'star'])
        ->call('removePlayer', 1);

    $setup->assertSet('playerNames', ['Ana', 'Caio'])
        ->assertSet('playerColors', ['cyan', 'mint'])
        ->assertSet('playerIcons', [null, 'star']);
});

it('rejects an unsupported player color before creating a duel', function () {
    Livewire::test(DuelSetup::class)
        ->set('playerColors', ['not-a-color', 'gold'])
        ->call('start')
        ->assertHasErrors('playerColors.0')
        ->assertSee('Selecione uma cor de identificação disponível.');

    expect(Duel::count())->toBe(0);
});

it('rejects an unsupported player icon before creating a duel', function () {
    Livewire::test(DuelSetup::class)
        ->set('playerIcons', ['not-an-icon', null])
        ->call('start')
        ->assertHasErrors('playerIcons.0')
        ->assertSee('Selecione um ícone disponível.');

    expect(Duel::count())->toBe(0);
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
        ->assertSee('8000')
        ->assertSee('Vibração Off');
});

it('draws a first player from the current duel and shows the auxiliary result', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    $arena = Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('drawFirstPlayer');

    $drawn = $arena->get('firstPlayerDrawn');

    expect($drawn)->toBeIn(['Ana', 'Bruno'])
        ->and($duel->fresh()->turn)->toBe(1)
        ->and($duel->fresh()->phase)->toBe(DuelPhase::Draw);

    $arena->assertSee($drawn.'</span> começa', false)
        ->assertSee('O sorteio é apenas um auxílio');
});

it('draws a first player only from a duel with many seats', function () {
    $names = ['Ana', 'Bruno', 'Caio', 'Dora', 'Edu', 'Fabi', 'Gil', 'Hugo'];

    $duel = Duel::startWith(array_map(fn (string $name) => ['name' => $name], $names));
    Duel::startWith([['name' => 'Zoe']]);

    $arena = Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('drawFirstPlayer');

    expect($arena->get('firstPlayerDrawn'))->toBeIn($names);
});

it('places the opponent above the home player and lets their panel rotate', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    $arena = Livewire::test(DuelArena::class, ['duel' => $duel])
        ->assertSeeInOrder(['Bruno', 'Ana'])
        ->assertSee('rotate-180', false);

    $arena->call('toggleOpponentRotation')
        ->assertDontSee('rotate-180', false);

    $arena->call('toggleOpponentRotation')
        ->assertSee('rotate-180', false);
});

it('expands a player panel temporarily without changing duel data', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno'], ['name' => 'Caio']]);
    $ana = $duel->duelPlayers()->where('name', 'Ana')->sole();
    $ana->subtractLifePoints(300);
    $eventsBefore = $duel->lifePointEvents()->count();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('togglePlayerExpansion', $ana->id)
        ->assertSet('expandedPlayerId', $ana->id)
        ->assertSee('Recolher painel de Ana')
        ->assertSee('col-span-full', false)
        ->call('togglePlayerExpansion', $ana->id)
        ->assertSet('expandedPlayerId', null);

    expect($ana->fresh()->life_points)->toBe(7700)
        ->and($duel->lifePointEvents()->count())->toBe($eventsBefore);
});

it('keeps opponent rotation active while their panel is expanded', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno'], ['name' => 'Caio']]);
    $bruno = $duel->duelPlayers()->where('name', 'Bruno')->sole();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('togglePlayerExpansion', $bruno->id)
        ->assertSet('expandedPlayerId', $bruno->id)
        ->assertSee('Recolher painel de Bruno');
});

it('persists multiplayer player ordering without changing player identities or histories', function () {
    $duel = Duel::startWith([
        ['name' => 'Ana'],
        ['name' => 'Bruno'],
        ['name' => 'Caio'],
        ['name' => 'Duda'],
    ]);
    $players = $duel->duelPlayers()->orderBy('seat')->get();
    $players[0]->subtractLifePoints(100);
    $players[1]->subtractLifePoints(200);
    $players[2]->subtractLifePoints(300);
    $players[3]->subtractLifePoints(400);

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('movePlayer', $players[2]->id, 'up')
        ->assertSeeInOrder(['Ana', 'Caio', 'Bruno', 'Duda']);

    expect(Setting::read('duel.layout.order.'.$duel->id))
        ->toBe(implode(',', [$players[0]->id, $players[2]->id, $players[1]->id, $players[3]->id]));

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->assertSeeInOrder(['Ana', 'Caio', 'Bruno', 'Duda']);

    expect($duel->duelPlayers()->orderBy('seat')->get()->map(fn (DuelPlayer $player): array => [
        $player->name,
        $player->life_points,
        $player->lifePointEvents()->count(),
    ])->all())->toBe([
        ['Ana', 7900, 1],
        ['Bruno', 7800, 1],
        ['Caio', 7700, 1],
        ['Duda', 7600, 1],
    ]);
});

it('does not persist player ordering when a move reaches either boundary', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno'], ['name' => 'Caio']]);
    $players = $duel->duelPlayers()->orderBy('seat')->get();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('movePlayer', $players->first()->id, 'up')
        ->call('movePlayer', $players->last()->id, 'down');

    expect(Setting::read('duel.layout.order.'.$duel->id))->toBeNull();
});

it('keeps the two player opponent above the home player when reorder actions are requested', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);
    $ana = $duel->duelPlayers()->where('name', 'Ana')->sole();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('movePlayer', $ana->id, 'up')
        ->assertSeeInOrder(['Bruno', 'Ana']);

    expect(Setting::read('duel.layout.order.'.$duel->id))->toBeNull();
});

it('does not allow reordering a player from another duel', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno'], ['name' => 'Caio']]);
    $otherDuel = Duel::startWith([['name' => 'Duda'], ['name' => 'Eva'], ['name' => 'Fabi']]);
    $outsider = $otherDuel->duelPlayers()->firstOrFail();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('movePlayer', $outsider->id, 'up')
        ->assertNotFound();

    expect(Setting::read('duel.layout.order.'.$duel->id))->toBeNull();
});

it('rejects invalid player ordering directions without changing the saved order', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno'], ['name' => 'Caio']]);
    $bruno = $duel->duelPlayers()->where('name', 'Bruno')->sole();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('movePlayer', $bruno->id, 'sideways')
        ->assertHasErrors('layout');

    expect(Setting::read('duel.layout.order.'.$duel->id))->toBeNull();
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

it('dispatches visual feedback after a life point change is persisted', function (
    string $operation,
    int $amount,
    int $before,
    int $after,
    string $kind,
) {
    Device::shouldReceive('vibrate')->never();
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call($operation, $ana->id, $amount)
        ->assertDispatched('life-points-changed', fn (string $name, array $params): bool => $params === [
            'playerId' => $ana->id,
            'before' => $before,
            'after' => $after,
            'kind' => $kind,
        ]);

    expect($ana->fresh()->life_points)->toBe($after);
})->with([
    'damage' => ['subtract', 100, 8000, 7900, 'damage'],
    'recovery' => ['add', 100, 8000, 8100, 'recovery'],
]);

it('does not dispatch visual feedback when life points do not change', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();
    $ana->update(['life_points' => 0]);

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('subtract', $ana->id, 100)
        ->assertNotDispatched('life-points-changed');

    expect($ana->fresh()->life_points)->toBe(0);
});

it('dispatches visual feedback for undo and redo using the persisted life point values', function () {
    Device::shouldReceive('vibrate')->never();
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();
    $ana->subtractLifePoints(100);

    $arena = Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('undo')
        ->assertDispatched('life-points-changed', fn (string $name, array $params): bool => $params === [
            'playerId' => $ana->id,
            'before' => 7900,
            'after' => 8000,
            'kind' => 'recovery',
        ]);

    expect($ana->fresh()->life_points)->toBe(8000);

    $arena->call('redo')
        ->assertDispatched('life-points-changed', fn (string $name, array $params): bool => $params === [
            'playerId' => $ana->id,
            'before' => 8000,
            'after' => 7900,
            'kind' => 'damage',
        ]);

    expect($ana->fresh()->life_points)->toBe(7900);
});

it('persists haptic feedback preference and vibrates after a life point change', function () {
    Device::shouldReceive('vibrate')->twice()->andReturn(true);
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    $arena = Livewire::test(DuelArena::class, ['duel' => $duel])
        ->assertSet('hapticFeedbackEnabled', false)
        ->call('toggleHapticFeedback')
        ->assertSet('hapticFeedbackEnabled', true)
        ->call('subtract', $ana->id, 100)
        ->assertDispatched('life-points-changed');

    expect(Setting::read('duel.feedback.haptics'))->toBe('1')
        ->and($ana->fresh()->life_points)->toBe(7900);

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->assertSet('hapticFeedbackEnabled', true);
});

it('disables haptics and reports when the native vibration API is unavailable', function () {
    Device::shouldReceive('vibrate')->once()->andReturn(false);
    Setting::write('duel.feedback.haptics', '1');
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('subtract', $ana->id, 100)
        ->assertSet('hapticFeedbackEnabled', false)
        ->assertHasErrors('hapticFeedback');

    expect(Setting::read('duel.feedback.haptics'))->toBe('0')
        ->and($ana->fresh()->life_points)->toBe(7900);
});

it('does not enable haptics when the native vibration API is unavailable', function () {
    Device::shouldReceive('vibrate')->once()->andReturn(false);
    $duel = Duel::startWith([['name' => 'Ana']]);

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('toggleHapticFeedback')
        ->assertSet('hapticFeedbackEnabled', false)
        ->assertHasErrors('hapticFeedback');

    expect(Setting::read('duel.feedback.haptics'))->toBe('0');
});

it('disables a persisted haptic preference without invoking the device', function () {
    Device::shouldReceive('vibrate')->never();
    Setting::write('duel.feedback.haptics', '1');
    $duel = Duel::startWith([['name' => 'Ana']]);

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->assertSet('hapticFeedbackEnabled', true)
        ->call('toggleHapticFeedback')
        ->assertSet('hapticFeedbackEnabled', false);

    expect(Setting::read('duel.feedback.haptics'))->toBe('0');
});

it('shows an error and does not change life points for an oversized arena operation', function (string $operation) {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call($operation, $ana->id, DuelPlayer::MAX_LIFE_POINTS + 1)
        ->assertHasErrors('amount');

    expect($ana->fresh()->life_points)->toBe(8000)
        ->and($ana->lifePointEvents()->count())->toBe(0);
})->with([
    'adding' => 'add',
    'subtracting' => 'subtract',
]);

it('previews and sets custom life points with undo and redo', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    $arena = Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('openLifePointCalculator', $ana->id)
        ->call('applyLifePointCalculation', $ana->id, '4200', 'set');

    $event = $ana->lifePointEvents()->sole();

    expect($ana->fresh()->life_points)->toBe(4200)
        ->and($event->operation)->toBe('set')
        ->and($event->before)->toBe(8000)
        ->and($event->after)->toBe(4200);

    $arena->call('undo');

    expect($ana->fresh()->life_points)->toBe(8000);

    $arena->call('redo');

    expect($ana->fresh()->life_points)->toBe(4200);
});

it('rejects invalid custom life points without changing state', function (string $value, string $message) {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('openLifePointCalculator', $ana->id)
        ->call('applyLifePointCalculation', $ana->id, $value, 'set')
        ->assertHasErrors('input');

    expect($ana->fresh()->life_points)->toBe(8000)
        ->and($ana->lifePointEvents()->count())->toBe(0);
})->with([
    'above the limit' => ['1000000', 'O valor não pode exceder 999.999.'],
    'decimal value' => ['1.5', 'Informe um número inteiro entre 0 e 999.999.'],
    'non-numeric value' => ['abc', 'Informe um número inteiro entre 0 e 999.999.'],
    'negative value' => ['-1', 'O valor não pode ser negativo.'],
]);

it('doubles life points from the arena and records an undoable event', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    $arena = Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('doubleLifePoints', $ana->id);

    $event = $ana->lifePointEvents()->sole();

    expect($ana->fresh()->life_points)->toBe(16000)
        ->and($event->operation)->toBe('double')
        ->and($event->before)->toBe(8000)
        ->and($event->after)->toBe(16000);

    $arena->call('undo');

    expect($ana->fresh()->life_points)->toBe(8000);

    $arena->call('redo');

    expect($ana->fresh()->life_points)->toBe(16000);
});

it('applies custom additions and subtractions from the keypad', function (string $operation, string $input, int $expected, string $eventOperation) {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    $arena = Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('openLifePointCalculator', $ana->id)
        ->call('applyLifePointCalculation', $ana->id, $input, $operation);

    $event = $ana->lifePointEvents()->sole();

    expect($ana->fresh()->life_points)->toBe($expected)
        ->and($event->operation)->toBe($eventOperation)
        ->and($event->amount)->toBe((int) $input);
})->with([
    'custom recovery' => ['add', '250', 8250, 'add'],
    'custom damage' => ['subtract', '250', 7750, 'subtract'],
]);

it('rejects zero for custom damage and recovery without changing life points', function (string $operation) {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('openLifePointCalculator', $ana->id)
        ->call('applyLifePointCalculation', $ana->id, '0', $operation)
        ->assertHasErrors('input');

    expect($ana->fresh()->life_points)->toBe(8000)
        ->and($ana->lifePointEvents()->count())->toBe(0);
})->with([
    'add' => 'add',
    'subtract' => 'subtract',
]);

it('rejects unknown calculator operations without changing life points', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('openLifePointCalculator', $ana->id)
        ->call('applyLifePointCalculation', $ana->id, '500', 'multiply')
        ->assertHasErrors('operation');

    expect($ana->fresh()->life_points)->toBe(8000)
        ->and($ana->lifePointEvents()->count())->toBe(0);
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

it('updates only the selected player identity without changing duel history', function () {
    $profile = Player::factory()->create(['name' => 'Michel']);
    $duel = Duel::startWith([
        ['player_id' => $profile->id, 'name' => 'Michel'],
        ['name' => 'Bruno'],
    ]);
    $michel = $duel->duelPlayers()->where('seat', 0)->sole();
    $bruno = $duel->duelPlayers()->where('seat', 1)->sole();
    $michel->subtractLifePoints(500);
    $event = $michel->lifePointEvents()->sole();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('editPlayerIdentity', $michel->id)
        ->set('editingPlayerName', 'Mika')
        ->set('editingPlayerColor', 'violet')
        ->set('editingPlayerIcon', 'star')
        ->assertHasNoErrors()
        ->call('savePlayerIdentity')
        ->assertHasNoErrors();

    expect($michel->fresh()->name)->toBe('Mika')
        ->and($michel->fresh()->player_id)->toBe($profile->id)
        ->and($michel->fresh()->color)->toBe('violet')
        ->and($michel->fresh()->icon)->toBe('star')
        ->and($michel->fresh()->life_points)->toBe(7500)
        ->and($michel->lifePointEvents()->sole()->id)->toBe($event->id)
        ->and($bruno->fresh()->name)->toBe('Bruno')
        ->and($bruno->fresh()->color)->toBe('gold')
        ->and($profile->fresh()->name)->toBe('Michel');
});

it('rejects an unsupported identity color without changing the player', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('editPlayerIdentity', $ana->id)
        ->set('editingPlayerColor', 'not-a-color')
        ->call('savePlayerIdentity')
        ->assertHasErrors('editingPlayerColor');

    expect($ana->fresh()->color)->toBe('cyan')
        ->and($ana->fresh()->name)->toBe('Ana');
});

it('does not allow editing a player from another duel identity', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $foreign = Duel::startWith([['name' => 'Intruso']])->duelPlayers->first();

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->call('editPlayerIdentity', $foreign->id)
        ->assertNotFound();

    expect($foreign->fresh()->name)->toBe('Intruso');
});

it('renders legacy player colors with the current theme palette', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);
    $ana = $duel->duelPlayers()->where('seat', 0)->sole();
    $ana->update(['color' => '#00f2fe']);

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->assertDontSee('#00f2fe', false);
});

it('renders every seat of an eight player duel', function () {
    $seats = array_map(fn (int $i) => ['name' => "Duelista {$i}"], range(1, 8));

    $duel = Duel::startWith($seats);

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->assertSee('P8')
        ->assertSee('Duelista 8');
});

it('renders seat labels without PHP expressions in Alpine attributes', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->assertSee('P1')
        ->assertSee('P2')
        ->assertDontSee('$player->seat', false);
});

it('keeps the arena shareable through a named route', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    expect(route('duel.arena', $duel))->toEndWith('/duel/'.$duel->id);
});
