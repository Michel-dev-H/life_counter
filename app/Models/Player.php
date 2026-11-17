<?php

namespace App\Models;

use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    /** @use HasFactory<PlayerFactory> */
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * @return HasMany<DuelPlayer, $this>
     */
    public function duelPlayers(): HasMany
    {
        return $this->hasMany(DuelPlayer::class);
    }
}
