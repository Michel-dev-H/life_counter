<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiceRoll extends Model
{
    use HasFactory;

    protected $fillable = ['dice_count', 'results', 'total'];

    protected function casts(): array
    {
        return [
            'dice_count' => 'integer',
            'results' => 'array',
            'total' => 'integer',
        ];
    }

    public function duel(): BelongsTo
    {
        return $this->belongsTo(Duel::class);
    }
}
