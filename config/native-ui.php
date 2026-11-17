<?php

/**
 * Native UI — Theme Tokens
 *
 * Published from `nativephp/mobile-ui` (`php artisan vendor:publish --tag=native-ui-config`).
 *
 * The palette below is the app's own design system ("Duel Companion"), not the
 * package default. `App\Enums\Theme::nativeTokens()` holds the per-theme
 * palettes and overwrites these blocks at runtime whenever the user picks a
 * theme in the Appearance screen.
 *
 * Token semantics:
 *   "on-X" = color of content placed ON a surface of color X.
 *   The map is open-ended: any key resolves `bg-theme-<key>`,
 *   `text-theme-<key>` and `border-theme-<key>`.
 *
 * Dark mode is auto-derived from `light` when `dark` is not set — both blocks
 * are declared here because the app is dark-first by design.
 *
 * @see https://nativephp.com/docs/mobile/4/digging-deeper/theming
 */

return [

    'theme' => [

        'light' => [
            // Base palette: "Holographic Duel" (App\Enums\Theme::Holographic).
            // The app is dark-first, so this block holds the dark palette too —
            // the user's chosen theme overwrites both blocks at runtime.
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

            // Open-ended tokens used by the duel arena: damage / heal feedback
            // and the gold accent of the phase ribbon.
            'damage' => '#FF4D5A',
            'on-damage' => '#080B12',
            'heal' => '#43D9A3',
            'on-heal' => '#080B12',
            'gold' => '#D6B56D',
            'on-gold' => '#080B12',

            // Player identity colours (App\Models\Duel::PLAYER_ACCENTS).
            // Constant across themes: they identify a player, not a surface.
            'player-cyan' => '#00DCE6',
            'player-gold' => '#EDC157',
            'player-rose' => '#FF6685',
            'player-mint' => '#00D99E',
            'player-violet' => '#B57BFF',
            'player-orange' => '#FF985E',
            'player-blue' => '#6AA9FF',
            'player-pink' => '#FF78C6',
        ],

        'dark' => [
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

            'player-cyan' => '#00DCE6',
            'player-gold' => '#EDC157',
            'player-rose' => '#FF6685',
            'player-mint' => '#00D99E',
            'player-violet' => '#B57BFF',
            'player-orange' => '#FF985E',
            'player-blue' => '#6AA9FF',
            'player-pink' => '#FF78C6',
        ],

        // Corner radii (points / dp).
        'radius-sm' => 4,
        'radius-md' => 12,
        'radius-lg' => 16,
        'radius-xl' => 20,
        'radius-full' => 9999,

        // Font size scale (points / sp).
        'font-sm' => 13,
        'font-md' => 15,
        'font-lg' => 18,
        'font-xl' => 24,

    ],

    /*
    |---------------------------------------------------------------------------
    | Fonts
    |---------------------------------------------------------------------------
    |
    | Semantic names for the bundled fonts in `resources/fonts/` (the file token
    | without the extension). Use an alias anywhere a font token works — the
    | `font` attribute (`font="display"`), the chrome `->font()` builders, or
    | the layout `$font` property.
    |
    | `default` is the app-wide default font. Per-element `font` attributes and
    | the font-medium / font-semibold / font-bold classes win over it.
    |
    | A family's `-Regular` token follows the weight classes by drawing the
    | sibling file bundled for that weight (Orbitron-Bold, Rajdhani-Bold).
    |
    | Files bundled here (Google Fonts, SIL Open Font License):
    |   Orbitron-Medium / SemiBold / Bold / ExtraBold / Regular
    |   Rajdhani-Medium / Bold
    |
    */

    'fonts' => [
        'default' => 'Rajdhani-Medium',
        'body' => 'Rajdhani-Medium',
        'display' => 'Orbitron-ExtraBold',
        'headline' => 'Orbitron-Bold',
        'numeric' => 'Orbitron-Bold',
    ],

];
