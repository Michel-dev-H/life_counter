<?php

use App\Appearance;
use App\Enums\Theme;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Native\Mobile\UI\Theme as NativeUiTheme;

uses(RefreshDatabase::class);

it('exposes a complete native palette for every theme', function () {
    $required = [
        'primary', 'on-primary',
        'secondary', 'on-secondary',
        'surface', 'on-surface',
        'background', 'on-background',
        'surface-variant', 'on-surface-variant',
        'outline', 'outline-variant',
        'destructive', 'on-destructive',
        'success', 'on-success',
        'accent', 'on-accent',
        'damage', 'on-damage',
        'heal', 'on-heal',
        'gold', 'on-gold',
    ];

    foreach (Theme::cases() as $theme) {
        $tokens = $theme->nativeTokens();

        expect($tokens)->toHaveKeys(['light', 'dark']);
        expect(array_keys($tokens['light']))->toEqual($required);
        expect(array_keys($tokens['dark']))->toEqual($required);

        foreach ($tokens['light'] as $token => $value) {
            expect($value)->toMatch('/^#[0-9A-F]{6}$/');
        }
    }
});

it('keeps each theme fixed in a single appearance mode', function () {
    foreach (Theme::cases() as $theme) {
        expect($theme->nativeTokens()['light'])->toEqual($theme->nativeTokens()['dark']);
    }
});

it('applies the selected theme to the native ui theme store', function () {
    Setting::write('appearance.theme', Theme::ShadowRealm->value);

    app(Appearance::class)->applyNativeTheme();

    expect(NativeUiTheme::get('light.primary'))->toBe('#A78BFA');
    expect(NativeUiTheme::get('dark.primary'))->toBe('#A78BFA');
});

it('falls back to the holographic theme when no theme is selected', function () {
    app(Appearance::class)->applyNativeTheme();

    expect(NativeUiTheme::get('light.primary'))->toBe('#35D8FF');
});
