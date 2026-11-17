<?php

namespace App\Models;

use Database\Factories\CoinFlipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoinFlip extends Model
{
    /** @use HasFactory<CoinFlipFactory> */
    use HasFactory;

    protected $fillable = ['duel_id', 'result'];

    /**
     * @return BelongsTo<Duel, $this>
     */
    public function duel(): BelongsTo
    {
        return $this->belongsTo(Duel::class);
    }
}
