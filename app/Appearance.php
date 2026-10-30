<?php

namespace App;

use App\Enums\Theme;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Appearance
{
    private const THEME_KEY = 'appearance.theme';

    private const WALLPAPER_KEY = 'appearance.wallpaper';

    private const WALLPAPER_DIRECTORY = 'wallpapers';

    public function theme(): Theme
    {
        return Theme::tryFrom(Setting::read(self::THEME_KEY) ?? '') ?? Theme::Holographic;
    }

    public function selectTheme(Theme $theme): void
    {
        Setting::write(self::THEME_KEY, $theme->value);
    }

    public function wallpaperPath(): ?string
    {
        $path = Setting::read(self::WALLPAPER_KEY);

        if ($path === null || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return $path;
    }

    public function wallpaperUrl(): ?string
    {
        $path = $this->wallpaperPath();

        return $path === null ? null : Storage::disk('public')->url($path);
    }

    public function storeWallpaper(UploadedFile $file): void
    {
        $this->removeWallpaper();

        $path = $file->store(self::WALLPAPER_DIRECTORY, 'public');

        Setting::write(self::WALLPAPER_KEY, $path === false ? null : $path);
    }

    public function removeWallpaper(): void
    {
        $path = $this->wallpaperPath();

        if ($path !== null) {
            Storage::disk('public')->delete($path);
        }

        Setting::write(self::WALLPAPER_KEY, null);
    }
}
