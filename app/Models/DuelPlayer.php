<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DuelPlayer extends Model
{
    use HasFactory;

    public const MIN_LIFE_POINTS = 0;

    public const MAX_LIFE_POINTS = 999999;

    protected $fillable = [
        'duel_id',
        'player_id',
        'name',
        'color',
        'icon',
        'starting_life_points',
        'life_points',
        'seat',
    ];

    protected function casts(): array
    {
        return [
            'starting_life_points' => 'integer',
            'life_points' => 'integer',
            'seat' => 'integer',
        ];
    }

    public function duel(): BelongsTo
    {
        return $this->belongsTo(Duel::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function lifePointEvents(): HasMany
    {
        return $this->hasMany(LifePointEvent::class);
    }

    public function latestLifePointEvent(): HasOne
    {
        return $this->hasOne(LifePointEvent::class)->latestOfMany();
    }

    public function isYou(): bool
    {
        return $this->seat === 0;
    }

    public function isOpponent(): bool
    {
        return $this->seat === 1;
    }

    public function seatLabel(): string
    {
        return 'P'.($this->seat + 1).' · '.match (true) {
            $this->isYou() => 'VOCÊ',
            $this->isOpponent() => 'RIVAL',
            default => 'ADVERSÁRIO',
        };
    }

    public function accentToken(): string
    {
        if (array_key_exists($this->color, Duel::PLAYER_ACCENTS)) {
            return $this->color;
        }

        return array_keys(Duel::PLAYER_ACCENTS)[$this->seat % count(Duel::PLAYER_ACCENTS)];
    }

    public function iconName(): string
    {
        return $this->icon !== null && array_key_exists($this->icon, Duel::PLAYER_ICONS)
            ? $this->icon
            : 'swords';
    }

    public function remainingPercentage(): int
    {
        return (int) round($this->life_points / max(1, $this->starting_life_points) * 100);
    }

    public function addLifePoints(int $amount): void
    {
        $amount = $this->requirePositiveAmount($amount);
        $this->changeLifePoints(fn (int $lifePoints): int => $lifePoints + $amount, 'add', $amount);
    }

    public function subtractLifePoints(int $amount): void
    {
        $amount = $this->requirePositiveAmount($amount);
        $this->changeLifePoints(fn (int $lifePoints): int => $lifePoints - $amount, 'subtract', $amount);
    }

    public function setLifePointsTo(int $value): void
    {
        $this->changeLifePoints(fn (): int => $value, 'set', 0);
    }

    public function halveLifePoints(): void
    {
        $this->changeLifePoints(fn (int $lifePoints): int => intdiv($lifePoints + 1, 2), 'halve', 0);
    }

    public function doubleLifePoints(): void
    {
        $this->changeLifePoints(fn (int $lifePoints): int => $lifePoints * 2, 'double', 0);
    }

    public function restoreStartingLifePoints(): void
    {
        $this->changeLifePoints(fn (): int => $this->starting_life_points, 'restore', 0);
    }

    /**
     * @param  callable(int): int  $calculate
     */
    private function changeLifePoints(callable $calculate, string $operation, int $amount): void
    {
        $this->refresh();

        $before = $this->life_points;
        $after = max(self::MIN_LIFE_POINTS, min(self::MAX_LIFE_POINTS, $calculate($before)));

        if ($after === $before) {
            return;
        }

        DB::transaction(function () use ($before, $after, $operation, $amount): void {
            LifePointEvent::query()
                ->whereIn('duel_player_id', $this->duel->duelPlayers()->select('id'))
                ->whereNotNull('undone_at')
                ->delete();

            $this->update(['life_points' => $after]);

            $this->lifePointEvents()->create([
                'before' => $before,
                'after' => $after,
                'operation' => $operation,
                'amount' => $amount,
            ]);
        });
    }

    private function requirePositiveAmount(int $amount): int
    {
        if ($amount < 1) {
            throw new InvalidArgumentException('O valor precisa ser maior que zero.');
        }

        if ($amount > self::MAX_LIFE_POINTS) {
            throw new InvalidArgumentException('O valor não pode exceder '.self::MAX_LIFE_POINTS.'.');
        }

        return $amount;
    }
}
