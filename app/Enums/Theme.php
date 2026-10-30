<?php

namespace App\Enums;

enum Theme: string
{
    case Holographic = 'holographic';
    case ShadowRealm = 'shadow-realm';
    case MinimalArena = 'minimal-arena';

    public function label(): string
    {
        return match ($this) {
            self::Holographic => 'Holographic Duel',
            self::ShadowRealm => 'Shadow Realm',
            self::MinimalArena => 'Minimal Arena',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Holographic => 'HUD tático com vidro escuro e luz ciano.',
            self::ShadowRealm => 'Vidro obsidiana e violeta espectral, clima oculto.',
            self::MinimalArena => 'Papel claro, tinta escura e verde de campo.',
        };
    }

    /**
     * Surface color used by the browser chrome around the WebView.
     */
    public function browserColor(): string
    {
        return match ($this) {
            self::Holographic => '#0f131d',
            self::ShadowRealm => '#0b0713',
            self::MinimalArena => '#f7f7f4',
        };
    }
}
