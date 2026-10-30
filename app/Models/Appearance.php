<?php

namespace App;

use App\Enums\Theme;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class Appearance
{
    private const THEME_KEY = 'appearance.theme';

    private const WALLPAPER_KEY = 'appearance.wallpaper';

    private const WALLPAPER_DIRECTORY = 'wallpapers';

    public static function theme(): Theme
    {
        $value = static::read(self::THEME_KEY);

        return $value !== null ? Theme::tryFrom($value) ?? Theme::Holographic : Theme::Holographic;
    }

    public static function selectTheme(Theme $theme): void
    {
        static::write($theme->value);
    }

    public static function wallpaperPath(): ?string
    {
        $path = static::read(self::WALLPAPER_KEY);

        if ($path === null || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return $path;
    }

    public static function wallpaperUrl(): ?string
    {
        $path = static::wallpaperPath();

        return $path === null ? null : Storage::disk('public')->url($path);
    }

    public static function storeWallpaper(\Illuminate\Http\UploadedFile $file): void
    {
        static::removeWallpaper();

        $path = $file->store(self::WALLPAPER_DIRECTORY, 'public');

        static::write(self::WALLPAPER_KEY, $path === false ? null : $path);
    }

    public static function removeWallpaper(): void
    {
        $path = static::wallpaperPath();

        if ($path !== null) {
            Storage::disk('public')->delete($path);
        }

        static::write(self::WALLPAPER_KEY, null);
    }

    public static function seatAccentHex(): string
    {
        return match (self::theme()) {
            Theme::Holographic => '#b57bff',
            Theme::ShadowRealm => '#e8c06a',
            Theme::MinimalArena => '#2f6f4e',
        };
    }

    public static function scrimRgba(): string
    {
        return match (self::theme()) {
            Theme::Holographic => 'rgba(15,19,29,0.4)',
            Theme::ShadowRealm => 'rgba(11,7,19,0.4)',
            Theme::MinimalArena => 'rgba(247,247,244,0.1)',
        };
    }

    public static function browserBodyColor(): string
    {
        return match (self::theme()) {
            Theme::Holographic => '#0f131d',
            Theme::ShadowRealm => '#0b0713',
            Theme::MinimalArena => '#f7f7f4',
        };
    }

    private static function read(string $key): ?string
    {
        $value = Setting::query()->where('key', $key)->value('value');

        return $value === null ? null : (string) $value;
    }

    public static function write(string $key, ?string $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }
}