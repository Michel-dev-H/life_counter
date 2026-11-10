<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'duel_id',
        'duel_player_id',
        'name',
        'description',
        'trigger_turn',
        'trigger_phase',
        'duration_turns',
        'removal_condition',
        'completed',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'trigger_turn' => 'integer',
        'duration_turns' => 'integer',
    ];

    public function duel(): BelongsTo
    {
        return $this->belongsTo(Duel::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(DuelPlayer::class, 'duel_player_id');
    }
}
