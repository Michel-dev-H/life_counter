<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function addLifePoints(int $amount): void
    {
        $this->changeLifePoints($this->life_points + $this->requirePositiveAmount($amount), 'add', $amount);
    }

    public function subtractLifePoints(int $amount): void
    {
        $this->changeLifePoints($this->life_points - $this->requirePositiveAmount($amount), 'subtract', $amount);
    }

    public function setLifePointsTo(int $value): void
    {
        $this->changeLifePoints($value, 'set', 0);
    }

    public function halveLifePoints(): void
    {
        $this->changeLifePoints(intdiv($this->life_points + 1, 2), 'halve', 0);
    }

    public function doubleLifePoints(): void
    {
        $this->changeLifePoints($this->life_points * 2, 'double', 0);
    }

    public function restoreStartingLifePoints(): void
    {
        $this->changeLifePoints($this->starting_life_points, 'restore', 0);
    }

    public function undoLifePointChange(): bool
    {
        $event = $this->lifePointEvents()->whereNull('undone_at')->latest('id')->first();

        if ($event === null) {
            return false;
        }

        DB::transaction(function () use ($event): void {
            $event->update(['undone_at' => now()]);
            $this->update(['life_points' => $event->before]);
        });

        return true;
    }

    public function redoLifePointChange(): bool
    {
        $event = $this->lifePointEvents()->whereNotNull('undone_at')->latest('id')->first();

        if ($event === null) {
            return false;
        }

        DB::transaction(function () use ($event): void {
            $event->update(['undone_at' => null]);
            $this->update(['life_points' => $event->after]);
        });

        return true;
    }

    private function changeLifePoints(int $newValue, string $operation, int $amount): void
    {
        $before = $this->life_points;
        $after = max(self::MIN_LIFE_POINTS, min(self::MAX_LIFE_POINTS, $newValue));

        DB::transaction(function () use ($before, $after, $operation, $amount): void {
            $this->lifePointEvents()->whereNotNull('undone_at')->delete();

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

        return $amount;
    }
}
