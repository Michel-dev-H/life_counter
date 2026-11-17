<?php

namespace App\Models;

use Database\Factories\LifePointEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePointEvent extends Model
{
    /** @use HasFactory<LifePointEventFactory> */
    use HasFactory;

    protected $fillable = ['before', 'after', 'operation', 'amount', 'undone_at'];

    protected function casts(): array
    {
        return [
            'before' => 'integer',
            'after' => 'integer',
            'amount' => 'integer',
            'undone_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<DuelPlayer, $this>
     */
    public function duelPlayer(): BelongsTo
    {
        return $this->belongsTo(DuelPlayer::class);
    }

    public function signedAmount(): int
    {
        return $this->after - $this->before;
    }
}
