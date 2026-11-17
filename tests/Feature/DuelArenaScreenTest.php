<?php

use App\Enums\DuelPhase;
use App\Models\Duel;
use App\Models\Setting;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Facades\Device;
use Native\Mobile\Testing\Native;

uses(RefreshDatabase::class);

it('renders the arena of an existing duel', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    Native::visit('/duel/'.$duel->id)
        ->assertSee('Ana')
        ->assertSee('Bruno')
        ->assertSee('8000')
        ->assertSee('TURNO 01');
});

it('subtracts and adds life points from the arena', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);
    $ana = $duel->duelPlayers()->where('name', 'Ana')->sole();

    Native::visit('/duel/'.$duel->id)
        ->tap('−1000')
        ->tap('+500');

    expect($ana->fresh()->life_points)->toBe(7500)
        ->and($duel->lifePointEvents()->count())->toBe(2);
});

it('halves, doubles and restores the life points of a player', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    $screen = Native::visit('/duel/'.$duel->id)->tap('÷2');
    expect($ana->fresh()->life_points)->toBe(4000);

    $screen->tap('×2');
    expect($ana->fresh()->life_points)->toBe(8000);

    $screen->tap('−1000');
    expect($ana->fresh()->life_points)->toBe(7000);

    $screen->tap('Rest.');
    expect($ana->fresh()->life_points)->toBe(8000);
});

it('shows an error and does not change life points for an oversized operation', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Native::visit('/duel/'.$duel->id)
        ->tap('Calc')
        ->set('calculatorInput', '1000000')
        ->tap('Aplicar')
        ->assertSee('O valor não pode exceder 999.999.');

    expect($ana->fresh()->life_points)->toBe(8000);
});

it('previews and sets custom life points with undo and redo', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    $screen = Native::visit('/duel/'.$duel->id)
        ->tap('Calc')
        ->set('calculatorOperation', 'set')
        ->set('calculatorInput', '1234')
        ->tap('Aplicar');

    expect($ana->fresh()->life_points)->toBe(1234);

    $screen->tap('Desfazer última alteração');
    expect($ana->fresh()->life_points)->toBe(8000);

    $screen->tap('Refazer alteração desfeita');
    expect($ana->fresh()->life_points)->toBe(1234);
});

it('rejects a custom life point value that is not a number', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Native::visit('/duel/'.$duel->id)
        ->tap('Calc')
        ->set('calculatorInput', 'abc')
        ->tap('Aplicar')
        ->assertSee('Informe um número inteiro entre 0 e 999.999.');

    expect($ana->fresh()->life_points)->toBe(8000);
});

it('undoes and redoes the last change of the duel', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    $screen = Native::visit('/duel/'.$duel->id)->tap('−1000');

    expect($ana->fresh()->life_points)->toBe(7000);

    $screen->tap('Desfazer última alteração');
    expect($ana->fresh()->life_points)->toBe(8000);

    $screen->tap('Refazer alteração desfeita');
    expect($ana->fresh()->life_points)->toBe(7000);
});

it('changes the phase from the arena', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    Native::visit('/duel/'.$duel->id)->tap('SP');

    expect($duel->fresh()->phase)->toBe(DuelPhase::Standby);
});

it('advances the turn from the arena', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    Native::visit('/duel/'.$duel->id)
        ->tap('Turno')
        ->assertSee('TURNO 02')
        ->assertSee('Vez do Oponente');

    expect($duel->fresh()->turn)->toBe(2)
        ->and($duel->fresh()->active_seat)->toBe(1);
});

it('draws a first player from the current duel and shows the auxiliary result', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    $screen = Native::visit('/duel/'.$duel->id)->tap('Sorteio');

    $drawn = $screen->get('firstPlayerDrawn');

    expect($drawn)->toBeIn(['Ana', 'Bruno']);

    $screen->assertSee($drawn.' começa.')
        ->assertSee('O sorteio é apenas um auxílio');
});

it('persists the haptic feedback preference and vibrates after a life point change', function () {
    Device::shouldReceive('vibrate')->twice()->andReturn(true);

    $duel = Duel::startWith([['name' => 'Ana']]);

    $screen = Native::visit('/duel/'.$duel->id)
        ->tap('Vibração')
        ->tap('−1000');

    expect($screen->get('hapticFeedbackEnabled'))->toBeTrue()
        ->and(Setting::read('duel.feedback.haptics'))->toBe('1');
});

it('disables haptics and reports when the native vibration API is unavailable', function () {
    Device::shouldReceive('vibrate')->once()->andReturn(false);
    Setting::write('duel.feedback.haptics', '1');

    $duel = Duel::startWith([['name' => 'Ana']]);

    $screen = Native::visit('/duel/'.$duel->id)->tap('−1000');

    expect($screen->get('hapticFeedbackEnabled'))->toBeFalse()
        ->and(Setting::read('duel.feedback.haptics'))->toBe('0');

    $screen->assertSee('Vibração indisponível neste ambiente');
});

it('does not enable haptics when the native vibration API is unavailable', function () {
    Device::shouldReceive('vibrate')->once()->andReturn(false);

    $duel = Duel::startWith([['name' => 'Ana']]);

    $screen = Native::visit('/duel/'.$duel->id)->tap('Vibração');

    expect($screen->get('hapticFeedbackEnabled'))->toBeFalse()
        ->and(Setting::read('duel.feedback.haptics'))->toBe('0');

    $screen->assertSee('Vibração indisponível neste ambiente');
});

it('updates only the selected player identity', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);

    Native::visit('/duel/'.$duel->id)
        ->tap('Editar identidade')
        ->set('editingPlayerName', 'Ana Updated')
        ->tap('Salvar');

    $players = $duel->fresh()->duelPlayers()->orderBy('seat')->get();

    expect($players->pluck('name')->all())->toBe(['Ana Updated', 'Bruno']);
});

it('persists the player ordering without changing their identities', function () {
    $duel = Duel::startWith([
        ['name' => 'Ana'],
        ['name' => 'Bruno'],
        ['name' => 'Caio'],
    ]);

    $ana = $duel->duelPlayers()->where('name', 'Ana')->sole();

    Native::visit('/duel/'.$duel->id)
        ->tap('Mover Ana para baixo');

    expect(Setting::read('duel.layout.order.'.$duel->id))->toBe(
        $duel->duelPlayers()->orderBy('seat')->get()->modelKeys()[1].','
        .$duel->duelPlayers()->orderBy('seat')->get()->modelKeys()[0].','
        .$duel->duelPlayers()->orderBy('seat')->get()->modelKeys()[2]
    );

    expect($ana->fresh()->name)->toBe('Ana');
});

it('does not persist the player ordering when a move reaches either boundary', function () {
    $duel = Duel::startWith([
        ['name' => 'Ana'],
        ['name' => 'Bruno'],
        ['name' => 'Caio'],
    ]);

    Native::visit('/duel/'.$duel->id)
        ->tap('Mover Ana para cima')
        ->tap('Mover Caio para baixo');

    expect(Setting::read('duel.layout.order.'.$duel->id))->toBeNull();
});

it('does not allow reordering a player from another duel', function () {
    $duel = Duel::startWith([
        ['name' => 'Ana'],
        ['name' => 'Bruno'],
        ['name' => 'Caio'],
    ]);
    $outsider = Duel::startWith([['name' => 'Duda']])->duelPlayers()->sole();

    expect(fn () => Native::visit('/duel/'.$duel->id)->call('movePlayer', $outsider->id, 'up'))
        ->toThrow(ModelNotFoundException::class);

    expect(Setting::read('duel.layout.order.'.$duel->id))->toBeNull();
});

it('rejects an invalid player ordering direction without changing the saved order', function () {
    $duel = Duel::startWith([
        ['name' => 'Ana'],
        ['name' => 'Bruno'],
        ['name' => 'Caio'],
    ]);
    $bruno = $duel->duelPlayers()->where('name', 'Bruno')->sole();

    Native::visit('/duel/'.$duel->id)
        ->call('movePlayer', $bruno->id, 'sideways')
        ->assertSee('Escolha uma direção válida para reorganizar os participantes.');

    expect(Setting::read('duel.layout.order.'.$duel->id))->toBeNull();
});

it('applies custom additions and subtractions from the calculator', function (string $operation, int $expected, string $eventOperation) {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Native::visit('/duel/'.$duel->id)
        ->tap('Calc')
        ->set('calculatorOperation', $operation)
        ->set('calculatorInput', '250')
        ->tap('Aplicar');

    $event = $ana->lifePointEvents()->sole();

    expect($ana->fresh()->life_points)->toBe($expected)
        ->and($event->operation)->toBe($eventOperation)
        ->and($event->amount)->toBe(250);
})->with([
    'custom recovery' => ['add', 8250, 'add'],
    'custom damage' => ['subtract', 7750, 'subtract'],
]);

it('rejects zero for custom damage and recovery without changing life points', function (string $operation) {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Native::visit('/duel/'.$duel->id)
        ->tap('Calc')
        ->set('calculatorOperation', $operation)
        ->set('calculatorInput', '0')
        ->tap('Aplicar')
        ->assertSee('O valor precisa ser maior que zero.');

    expect($ana->fresh()->life_points)->toBe(8000)
        ->and($ana->lifePointEvents()->count())->toBe(0);
})->with([
    'add' => 'add',
    'subtract' => 'subtract',
]);

it('rejects an unknown calculator operation without changing life points', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Native::visit('/duel/'.$duel->id)
        ->tap('Calc')
        ->set('calculatorOperation', 'multiply')
        ->set('calculatorInput', '500')
        ->tap('Aplicar')
        ->assertSee('Selecione uma operação válida.');

    expect($ana->fresh()->life_points)->toBe(8000)
        ->and($ana->lifePointEvents()->count())->toBe(0);
});

it('refuses to change a player from another duel', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $foreign = Duel::startWith([['name' => 'Intruso']])->duelPlayers()->sole();

    expect(fn () => Native::visit('/duel/'.$duel->id)->call('subtract', $foreign->id, 500))
        ->toThrow(ModelNotFoundException::class);

    expect($foreign->fresh()->life_points)->toBe(8000);
});

it('rejects an unsupported identity colour without changing the player', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Native::visit('/duel/'.$duel->id)
        ->tap('Editar identidade')
        ->set('editingPlayerColor', 'not-a-color')
        ->tap('Salvar')
        ->assertSee('Selecione uma cor de identificação disponível.');

    expect($ana->fresh()->color)->toBe('cyan')
        ->and($ana->fresh()->name)->toBe('Ana');
});

it('does not allow editing a player from another duel identity', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $foreign = Duel::startWith([['name' => 'Intruso']])->duelPlayers()->sole();

    expect(fn () => Native::visit('/duel/'.$duel->id)->call('editPlayerIdentity', $foreign->id))
        ->toThrow(ModelNotFoundException::class);

    expect($foreign->fresh()->name)->toBe('Intruso');
});

it('disables a persisted haptic preference without invoking the device', function () {
    Device::shouldReceive('vibrate')->never();
    Setting::write('duel.feedback.haptics', '1');
    $duel = Duel::startWith([['name' => 'Ana']]);

    $screen = Native::visit('/duel/'.$duel->id);

    expect($screen->get('hapticFeedbackEnabled'))->toBeTrue();

    $screen->tap('Vibração');

    expect($screen->get('hapticFeedbackEnabled'))->toBeFalse()
        ->and(Setting::read('duel.feedback.haptics'))->toBe('0');
});

it('draws a first player only from the duel on the arena', function () {
    $names = ['Ana', 'Bruno', 'Caio', 'Dora', 'Edu', 'Fabi', 'Gil', 'Hugo'];

    $duel = Duel::startWith(array_map(fn (string $name) => ['name' => $name], $names));
    Duel::startWith([['name' => 'Zoe']]);

    $screen = Native::visit('/duel/'.$duel->id)->tap('Sorteio');

    expect($screen->get('firstPlayerDrawn'))->toBeIn($names);
});

it('renders every seat of an eight player duel', function () {
    $seats = array_map(fn (int $i) => ['name' => "Duelista {$i}"], range(1, 8));

    $duel = Duel::startWith($seats);

    Native::visit('/duel/'.$duel->id)
        ->assertSee('P8')
        ->assertSee('Duelista 8');
});

it('keeps the arena shareable through a named route', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    expect(route('duel.arena', $duel))->toEndWith('/duel/'.$duel->id);
});

it('shows the last change on the arena after a life point change', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);

    Native::visit('/duel/'.$duel->id)
        ->tap('−1000')
        ->assertSee('-1000')
        ->assertDontSee('Nenhuma alteração');
});

it('keeps the arena without a change when the life points do not change', function () {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();
    $ana->update(['life_points' => 0]);

    Native::visit('/duel/'.$duel->id)
        ->tap('−1000')
        ->assertSee('Nenhuma alteração');

    expect($ana->fresh()->life_points)->toBe(0)
        ->and($ana->lifePointEvents()->count())->toBe(0);
});

it('does not persist the player ordering when the duel has only two players', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);
    $ana = $duel->duelPlayers()->where('name', 'Ana')->sole();

    Native::visit('/duel/'.$duel->id)
        ->tap('Mover Ana para baixo');

    expect(Setting::read('duel.layout.order.'.$duel->id))->toBeNull();
});

it('rejects a decimal and a negative custom life point value', function (string $value, string $message) {
    $duel = Duel::startWith([['name' => 'Ana']]);
    $ana = $duel->duelPlayers()->sole();

    Native::visit('/duel/'.$duel->id)
        ->tap('Calc')
        ->set('calculatorInput', $value)
        ->tap('Aplicar')
        ->assertSee($message);

    expect($ana->fresh()->life_points)->toBe(8000)
        ->and($ana->lifePointEvents()->count())->toBe(0);
})->with([
    'above the limit' => ['1000000', 'O valor não pode exceder 999.999.'],
    'decimal value' => ['1.5', 'Informe um número inteiro entre 0 e 999.999.'],
    'negative value' => ['-1', 'O valor não pode ser negativo.'],
]);

it('updates only the selected player identity without changing duel history', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);
    $ana = $duel->duelPlayers()->where('name', 'Ana')->sole();
    $bruno = $duel->duelPlayers()->where('name', 'Bruno')->sole();
    $ana->subtractLifePoints(500);

    Native::visit('/duel/'.$duel->id)
        ->tap('Editar identidade')
        ->set('editingPlayerName', 'Ana Maria')
        ->set('editingPlayerColor', 'violet')
        ->set('editingPlayerIcon', 'star')
        ->tap('Salvar');

    expect($ana->fresh()->name)->toBe('Ana Maria')
        ->and($ana->fresh()->color)->toBe('violet')
        ->and($ana->fresh()->icon)->toBe('star')
        ->and($ana->fresh()->life_points)->toBe(7500)
        ->and($bruno->fresh()->name)->toBe('Bruno')
        ->and($bruno->fresh()->color)->toBe('gold')
        ->and($duel->lifePointEvents()->count())->toBe(1);
});

it('opens the identity editor of a legacy player on a theme-safe colour', function () {
    $duel = Duel::startWith([['name' => 'Ana'], ['name' => 'Bruno']]);
    $bruno = $duel->duelPlayers()->where('name', 'Bruno')->sole();
    $bruno->update(['color' => '#ff00ff', 'icon' => 'custom-image']);

    $screen = Native::visit('/duel/'.$duel->id)->call('editPlayerIdentity', $bruno->id);

    expect($screen->get('editingPlayerColor'))->toBe('gold')
        ->and($screen->get('editingPlayerIcon'))->toBeNull();
});

it('vibrates after a life point change when haptics are enabled', function () {
    Device::shouldReceive('vibrate')->once()->andReturnTrue();
    Setting::write('duel.feedback.haptics', '1');
    $duel = Duel::startWith([['name' => 'Ana']]);

    $screen = Native::visit('/duel/'.$duel->id)->tap('−1000');

    expect($screen->get('hapticFeedbackEnabled'))->toBeTrue()
        ->and(Setting::read('duel.feedback.haptics'))->toBe('1')
        ->and($duel->duelPlayers()->sole()->fresh()->life_points)->toBe(7000);
});

it('turns haptics off when the device refuses to vibrate after a change', function () {
    Device::shouldReceive('vibrate')->andReturnFalse();
    Setting::write('duel.feedback.haptics', '1');
    $duel = Duel::startWith([['name' => 'Ana']]);

    $screen = Native::visit('/duel/'.$duel->id)
        ->tap('−1000')
        ->assertSee('Vibração indisponível neste ambiente; a preferência foi desativada.');

    expect($screen->get('hapticFeedbackEnabled'))->toBeFalse()
        ->and(Setting::read('duel.feedback.haptics'))->toBe('0')
        ->and($duel->duelPlayers()->sole()->fresh()->life_points)->toBe(7000);
});
