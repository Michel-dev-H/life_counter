<?php

use App\Enums\DuelPhase;
use App\Models\Duel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\Testing\Native;

uses(RefreshDatabase::class);

/**
 * A duel with the given number of seats, named after the seat order.
 */
function duelWithNativeSeats(int $players): Duel
{
    return Duel::startWith(
        array_map(fn (int $seat): array => ['name' => "Duelista {$seat}"], range(1, $players)),
    );
}

it('starts the duel on the first turn of the first round in the draw phase', function () {
    $duel = duelWithNativeSeats(2);

    expect($duel->turn)->toBe(1)
        ->and($duel->round)->toBe(1)
        ->and($duel->active_seat)->toBe(0)
        ->and($duel->phase)->toBe(DuelPhase::Draw);
});

it('sets the current phase', function () {
    $duel = duelWithNativeSeats(2);

    $duel->setPhase(DuelPhase::Battle);

    expect($duel->fresh()->phase)->toBe(DuelPhase::Battle);
});

it('passes the turn to the next player', function () {
    $duel = duelWithNativeSeats(2);

    $duel->advanceTurn();

    expect($duel->fresh()->turn)->toBe(2)
        ->and($duel->fresh()->active_seat)->toBe(1)
        ->and($duel->fresh()->phase)->toBe(DuelPhase::Draw);
});

it('starts a new round after the last player', function () {
    $duel = duelWithNativeSeats(2);

    $duel->advanceTurn();
    $duel->advanceTurn();

    expect($duel->fresh()->round)->toBe(2)
        ->and($duel->fresh()->turn)->toBe(3)
        ->and($duel->fresh()->active_seat)->toBe(0);
});

it('advances through eight players before starting a new round', function () {
    $duel = duelWithNativeSeats(8);

    for ($turn = 0; $turn < 8; $turn++) {
        $duel->advanceTurn();
    }

    expect($duel->fresh()->round)->toBe(2)
        ->and($duel->fresh()->turn)->toBe(9)
        ->and($duel->fresh()->active_seat)->toBe(0);
});

it('labels the turn owner', function () {
    $duel = duelWithNativeSeats(2);

    expect($duel->turnOwnerLabel())->toBe('Sua vez');

    $duel->advanceTurn();

    expect($duel->fresh()->turnOwnerLabel())->toBe('Vez do Oponente');
});

it('formats the header position', function () {
    $duel = duelWithNativeSeats(2);

    expect($duel->positionLabel())->toBe('R1 · T01');

    $duel->advanceTurn();

    expect($duel->fresh()->positionLabel())->toBe('R1 · T02');
});

it('exposes the phase codes and labels', function () {
    expect(array_map(fn (DuelPhase $phase): string => $phase->value, DuelPhase::cases()))
        ->toBe(['DP', 'SP', 'M1', 'BP', 'M2', 'EP'])
        ->and(DuelPhase::Main1->label())->toBe('Main 1')
        ->and(DuelPhase::End->label())->toBe('End');
});

it('counts down the match timer', function () {
    $duel = duelWithNativeSeats(2);

    $this->travelTo(now()->startOfSecond());
    $duel->startTimer();
    $this->travel(65)->seconds();

    expect($duel->timerRemainingSeconds())->toBe(Duel::MATCH_TIME_SECONDS - 65)
        ->and($duel->isTimerRunning())->toBeTrue();
});

it('pauses and resumes the match timer', function () {
    $duel = duelWithNativeSeats(2);

    $this->travelTo(now()->startOfSecond());
    $duel->startTimer();
    $this->travel(30)->seconds();
    $duel->pauseTimer();
    $this->travel(300)->seconds();

    expect($duel->fresh()->timerRemainingSeconds())->toBe(Duel::MATCH_TIME_SECONDS - 30)
        ->and($duel->fresh()->isTimerRunning())->toBeFalse();

    $duel->fresh()->startTimer();
    $this->travel(10)->seconds();

    expect($duel->fresh()->timerRemainingSeconds())->toBe(Duel::MATCH_TIME_SECONDS - 40);
});

it('never counts the match timer below zero', function () {
    $duel = duelWithNativeSeats(2);

    $this->travelTo(now()->startOfSecond());
    $duel->startTimer();
    $this->travel(Duel::MATCH_TIME_SECONDS + 120)->seconds();

    expect($duel->timerRemainingSeconds())->toBe(0);
});

it('refuses to start a finished match timer', function () {
    $duel = duelWithNativeSeats(2);

    $this->travelTo(now()->startOfSecond());
    $duel->startTimer();
    $this->travel(Duel::MATCH_TIME_SECONDS + 120)->seconds();
    $duel->pauseTimer();
    $duel->startTimer();

    expect($duel->fresh()->isTimerRunning())->toBeFalse();
});

it('resets the match timer', function () {
    $duel = duelWithNativeSeats(2);

    $this->travelTo(now()->startOfSecond());
    $duel->startTimer();
    $this->travel(90)->seconds();
    $duel->resetTimer();

    expect($duel->fresh()->timerRemainingSeconds())->toBe(Duel::MATCH_TIME_SECONDS)
        ->and($duel->fresh()->isTimerRunning())->toBeFalse();
});

it('formats the remaining time as minutes and seconds', function () {
    $duel = duelWithNativeSeats(2);

    expect($duel->formattedTimerRemaining())->toBe('40:00');

    $this->travelTo(now()->startOfSecond());
    $duel->startTimer();
    $this->travel(78)->seconds();

    expect($duel->fresh()->formattedTimerRemaining())->toBe('38:42');
});

it('shows every phase, the turn and the timer on the arena', function () {
    $duel = duelWithNativeSeats(2);

    Native::visit('/duel/'.$duel->id)
        ->assertSee('DP')
        ->assertSee('SP')
        ->assertSee('M1')
        ->assertSee('BP')
        ->assertSee('M2')
        ->assertSee('EP')
        ->assertSee('TURNO 01')
        ->assertSee('Sua vez')
        ->assertSee('40:00');
});

it('rejects an unknown phase from the arena', function () {
    $duel = duelWithNativeSeats(2);

    Native::visit('/duel/'.$duel->id)
        ->call('setPhase', 99)
        ->assertSee('Fase desconhecida.');

    expect($duel->fresh()->phase)->toBe(DuelPhase::Draw);
});

it('starts and pauses the timer from the arena', function () {
    $duel = duelWithNativeSeats(2);

    $this->travelTo(now()->startOfSecond());

    $arena = Native::visit('/duel/'.$duel->id)->tap('Iniciar');

    expect($duel->fresh()->isTimerRunning())->toBeTrue();

    $this->travel(5)->seconds();

    $arena->tap('Pausar');

    expect($duel->fresh()->isTimerRunning())->toBeFalse()
        ->and($duel->fresh()->timerRemainingSeconds())->toBe(Duel::MATCH_TIME_SECONDS - 5);
});

it('resets the timer from the arena', function () {
    $duel = duelWithNativeSeats(2);

    $this->travelTo(now()->startOfSecond());
    $duel->startTimer();
    $this->travel(45)->seconds();

    Native::visit('/duel/'.$duel->id)
        ->tap('Zerar')
        ->assertSee('40:00');

    expect($duel->fresh()->isTimerRunning())->toBeFalse();
});
