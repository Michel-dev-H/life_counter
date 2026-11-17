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

    /**
     * Palette applied to the native UI (nativephp/mobile-ui theme tokens).
     *
     * Each theme has a fixed appearance mode, so the same palette is used for
     * the `light` and the `dark` block — the app never follows the system
     * appearance. Token names are open-ended: `bg-theme-<token>`,
     * `text-theme-<token>` and `border-theme-<token>` resolve them.
     *
     * @return array{light: array<string, string>, dark: array<string, string>}
     */
    public function nativeTokens(): array
    {
        $palette = match ($this) {
            self::Holographic => [
                'primary' => '#35D8FF',
                'on-primary' => '#080B12',
                'secondary' => '#D6B56D',
                'on-secondary' => '#080B12',
                'surface' => '#111827',
                'on-surface' => '#F2F5FA',
                'background' => '#080B12',
                'on-background' => '#F2F5FA',
                'surface-variant' => '#1B2333',
                'on-surface-variant' => '#9AA6BC',
                'outline' => '#2A3446',
                'outline-variant' => '#1E2637',
                'destructive' => '#FF4D5A',
                'on-destructive' => '#080B12',
                'success' => '#43D9A3',
                'on-success' => '#080B12',
                'accent' => '#176BCE',
                'on-accent' => '#F2F5FA',
                'damage' => '#FF4D5A',
                'on-damage' => '#080B12',
                'heal' => '#43D9A3',
                'on-heal' => '#080B12',
                'gold' => '#D6B56D',
                'on-gold' => '#080B12',
            ],
            self::ShadowRealm => [
                'primary' => '#A78BFA',
                'on-primary' => '#0B0713',
                'secondary' => '#E879F9',
                'on-secondary' => '#0B0713',
                'surface' => '#17121F',
                'on-surface' => '#F5F0FA',
                'background' => '#0B0713',
                'on-background' => '#F5F0FA',
                'surface-variant' => '#241C30',
                'on-surface-variant' => '#A99BB8',
                'outline' => '#362B45',
                'outline-variant' => '#281E35',
                'destructive' => '#FF5C7A',
                'on-destructive' => '#0B0713',
                'success' => '#5EEAD4',
                'on-success' => '#0B0713',
                'accent' => '#7C3AED',
                'on-accent' => '#F5F0FA',
                'damage' => '#FF5C7A',
                'on-damage' => '#0B0713',
                'heal' => '#5EEAD4',
                'on-heal' => '#0B0713',
                'gold' => '#E4C98A',
                'on-gold' => '#0B0713',
            ],
            self::MinimalArena => [
                'primary' => '#2E7D5B',
                'on-primary' => '#FFFFFF',
                'secondary' => '#B08968',
                'on-secondary' => '#FFFFFF',
                'surface' => '#FFFFFF',
                'on-surface' => '#1A1C1A',
                'background' => '#F7F7F4',
                'on-background' => '#1A1C1A',
                'surface-variant' => '#ECEDE8',
                'on-surface-variant' => '#5A5F58',
                'outline' => '#D6D8D1',
                'outline-variant' => '#E4E5DF',
                'destructive' => '#C0392B',
                'on-destructive' => '#FFFFFF',
                'success' => '#2E7D5B',
                'on-success' => '#FFFFFF',
                'accent' => '#35618E',
                'on-accent' => '#FFFFFF',
                'damage' => '#C0392B',
                'on-damage' => '#FFFFFF',
                'heal' => '#2E7D5B',
                'on-heal' => '#FFFFFF',
                'gold' => '#B08968',
                'on-gold' => '#FFFFFF',
            ],
        };

        return [
            'light' => $palette,
            'dark' => $palette,
        ];
    }
}
