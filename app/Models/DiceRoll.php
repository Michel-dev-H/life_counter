<?php

namespace App\Models;

use Database\Factories\DiceRollFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiceRoll extends Model
{
    /** @use HasFactory<DiceRollFactory> */
    use HasFactory;

    protected $fillable = ['duel_id', 'dice_count', 'results', 'total'];

    protected function casts(): array
    {
        return [
            'dice_count' => 'integer',
            'results' => 'array',
            'total' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Duel, $this>
     */
    public function duel(): BelongsTo
    {
        return $this->belongsTo(Duel::class);
    }
}
