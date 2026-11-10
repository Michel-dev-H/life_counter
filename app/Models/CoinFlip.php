<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoinFlip extends Model
{
    use HasFactory;

    protected $fillable = ['duel_id', 'result'];

    public function duel(): BelongsTo
    {
        return $this->belongsTo(Duel::class);
    }
}
