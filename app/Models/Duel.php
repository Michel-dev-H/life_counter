<?php

namespace App\Models;

use App\Enums\DuelPhase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class Duel extends Model
{
    use HasFactory;

    public const MIN_PLAYERS = 1;

    public const MAX_PLAYERS = 8;

    public const MATCH_TIME_SECONDS = 2400;

    /** @var array<string, mixed> */
    protected $attributes = [
        'phase' => 'DP',
        'round' => 1,
        'turn' => 1,
        'active_seat' => 0,
        'timer_elapsed_seconds' => 0,
    ];

    protected $fillable = [
        'phase',
        'round',
        'turn',
        'active_seat',
        'timer_elapsed_seconds',
        'timer_started_at',
    ];

    protected function casts(): array
    {
        return [
            'phase' => DuelPhase::class,
            'round' => 'integer',
            'turn' => 'integer',
            'active_seat' => 'integer',
            'timer_elapsed_seconds' => 'integer',
            'timer_started_at' => 'datetime',
        ];
    }

    /** @var array<int, string> */
    public const SEAT_COLORS = [
        '#00f2fe',
        '#ffd166',
        '#ff3366',
        '#00f5a0',
        '#a78bfa',
        '#fb923c',
        '#38bdf8',
        '#f472b6',
    ];

    public function duelPlayers(): HasMany
    {
        return $this->hasMany(DuelPlayer::class);
    }

    public function lifePointEvents(): HasManyThrough
    {
        return $this->hasManyThrough(LifePointEvent::class, DuelPlayer::class);
    }

    /**
     * @param  array<int, array{name?: string, player_id?: int|null, color?: string}>  $seats
     */
    public static function startWith(array $seats, int $startingLifePoints = 8000): self
    {
        $seatCount = count($seats);

        if ($seatCount < self::MIN_PLAYERS || $seatCount > self::MAX_PLAYERS) {
            throw new InvalidArgumentException('Um duelo precisa de 1 a 8 jogadores.');
        }

        if ($startingLifePoints < 1 || $startingLifePoints > DuelPlayer::MAX_LIFE_POINTS) {
            throw new InvalidArgumentException('Os pontos de vida iniciais precisam estar entre 1 e '.DuelPlayer::MAX_LIFE_POINTS.'.');
        }

        return DB::transaction(function () use ($seats, $startingLifePoints): self {
            $duel = self::create();

            foreach (array_values($seats) as $index => $seat) {
                $player = isset($seat['player_id']) ? Player::find($seat['player_id']) : null;
                $name = trim((string) ($seat['name'] ?? ''));

                $duel->duelPlayers()->create([
                    'player_id' => $player?->id,
                    'name' => $name !== '' ? $name : ($player?->name ?? 'Jogador '.($index + 1)),
                    'color' => $seat['color'] ?? self::SEAT_COLORS[$index],
                    'starting_life_points' => $startingLifePoints,
                    'life_points' => $startingLifePoints,
                    'seat' => $index,
                ]);
            }

            return $duel;
        });
    }

    public function positionLabel(): string
    {
        return sprintf('R%d · T%02d', $this->round, $this->turn);
    }

    public function turnLabel(): string
    {
        return sprintf('TURNO %02d', $this->turn);
    }

    public function turnOwnerLabel(): string
    {
        return match ($this->active_seat) {
            0 => 'Sua vez',
            1 => 'Vez do Oponente',
            default => 'Vez de '.($this->duelPlayers()->where('seat', $this->active_seat)->value('name') ?? 'Jogador'),
        };
    }

    public function setPhase(DuelPhase $phase): void
    {
        $this->update(['phase' => $phase]);
    }

    public function advanceTurn(): void
    {
        $playerCount = $this->duelPlayers()->count();
        $turn = $this->turn + 1;

        $this->update([
            'turn' => $turn,
            'round' => intdiv($turn - 1, $playerCount) + 1,
            'active_seat' => ($turn - 1) % $playerCount,
        ]);
    }

    public function startTimer(): void
    {
        if ($this->isTimerRunning() || $this->timerRemainingSeconds() === 0) {
            return;
        }

        $this->update(['timer_started_at' => now()]);
    }

    public function pauseTimer(): void
    {
        if ($this->timer_started_at === null) {
            return;
        }

        $elapsed = (int) $this->timer_started_at->diffInSeconds(now());

        $this->update([
            'timer_elapsed_seconds' => min(self::MATCH_TIME_SECONDS, $this->timer_elapsed_seconds + $elapsed),
            'timer_started_at' => null,
        ]);
    }

    public function resetTimer(): void
    {
        $this->update([
            'timer_elapsed_seconds' => 0,
            'timer_started_at' => null,
        ]);
    }

    public function timerRemainingSeconds(): int
    {
        $elapsed = $this->timer_elapsed_seconds;

        if ($this->timer_started_at !== null) {
            $elapsed += (int) $this->timer_started_at->diffInSeconds(now());
        }

        return max(0, self::MATCH_TIME_SECONDS - $elapsed);
    }

    public function formattedTimerRemaining(): string
    {
        $remaining = $this->timerRemainingSeconds();

        return sprintf('%02d:%02d', intdiv($remaining, 60), $remaining % 60);
    }

    public function isTimerRunning(): bool
    {
        return $this->timer_started_at !== null && $this->timerRemainingSeconds() > 0;
    }

    public function canUndoLifePointChange(): bool
    {
        return $this->lifePointEvents()->whereNull('undone_at')->exists();
    }

    public function canRedoLifePointChange(): bool
    {
        return $this->lifePointEvents()->whereNotNull('undone_at')->exists();
    }

    public function undoLastLifePointChange(): bool
    {
        $event = $this->lifePointEvents()
            ->whereNull('undone_at')
            ->latest('life_point_events.id')
            ->first();

        if ($event === null) {
            return false;
        }

        DB::transaction(function () use ($event): void {
            $event->update(['undone_at' => now()]);
            $event->duelPlayer->update(['life_points' => $event->before]);
        });

        return true;
    }

    public function redoLastLifePointChange(): bool
    {
        $event = $this->lifePointEvents()
            ->whereNotNull('undone_at')
            ->oldest('life_point_events.id')
            ->first();

        if ($event === null) {
            return false;
        }

        DB::transaction(function () use ($event): void {
            $event->update(['undone_at' => null]);
            $event->duelPlayer->update(['life_points' => $event->after]);
        });

        return true;
    }
}
