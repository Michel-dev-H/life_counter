<?php

namespace App\Enums;

enum DuelPhase: string
{
    case Draw = 'DP';
    case Standby = 'SP';
    case Main1 = 'M1';
    case Battle = 'BP';
    case Main2 = 'M2';
    case End = 'EP';

    public function label(): string
    {
        return match ($this) {
            self::Draw => 'Draw',
            self::Standby => 'Standby',
            self::Main1 => 'Main 1',
            self::Battle => 'Battle',
            self::Main2 => 'Main 2',
            self::End => 'End',
        };
    }
}
