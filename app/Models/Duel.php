<?php

namespace App\Models;

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
