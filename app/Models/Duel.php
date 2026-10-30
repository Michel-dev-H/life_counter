<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    /**
     * @param  array<int, array{name?: string, player_id?: int|null, color?: string}>  $seats
     */
    public static function startWith(array $seats, int $startingLifePoints = 8000): self
    {
        $seatCount = count($seats);

        if ($seatCount < self::MIN_PLAYERS || $seatCount > self::MAX_PLAYERS) {
            throw new InvalidArgumentException('Um duelo precisa de 1 a 8 jogadores.');
        }

        $lifePoints = max(DuelPlayer::MIN_LIFE_POINTS, min(DuelPlayer::MAX_LIFE_POINTS, $startingLifePoints));

        return DB::transaction(function () use ($seats, $lifePoints): self {
            $duel = self::create();

            foreach (array_values($seats) as $index => $seat) {
                $player = isset($seat['player_id']) ? Player::find($seat['player_id']) : null;

                $duel->duelPlayers()->create([
                    'player_id' => $player?->id,
                    'name' => $seat['name'] ?? $player?->name ?? 'Jogador '.($index + 1),
                    'color' => $seat['color'] ?? self::SEAT_COLORS[$index],
                    'starting_life_points' => $lifePoints,
                    'life_points' => $lifePoints,
                    'seat' => $index,
                ]);
            }

            return $duel;
        });
    }
}
