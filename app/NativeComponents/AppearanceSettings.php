<?php

namespace App\NativeComponents;

use App\Appearance;
use App\Enums\Theme;
use Illuminate\View\View;
use Native\Mobile\Attributes\On;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Events\Gallery\MediaSelected;
use Native\Mobile\Facades\Camera;

class AppearanceSettings extends NativeComponent
{
    public string $selectedTheme = 'holographic';

    public float $scrimOpacity = 0.45;

    public string $wallpaperUrl = '';

    public bool $showRemoveConfirm = false;

    public string $wallpaperError = '';

    public function mount(): void
    {
        $appearance = app(Appearance::class);

        $this->selectedTheme = $appearance->theme()->value;
        $this->scrimOpacity = $appearance->scrimOpacity();
        $this->wallpaperUrl = $appearance->wallpaperUrl() ?? '';
    }

    public function navTitle(): string
    {
        return 'Aparência';
    }

    /**
     * Pick a theme by its position in the theme list.
     */
    public function selectTheme(int $index): void
    {
        $theme = Theme::cases()[$index] ?? null;

        if ($theme === null) {
            return;
        }

        $this->selectedTheme = $theme->value;

        $appearance = app(Appearance::class);
        $appearance->selectTheme($theme);
        $appearance->applyNativeTheme();
    }

    /**
     * Open the native gallery picker for the wallpaper.
     */
    public function pickWallpaper(): void
    {
        $this->wallpaperError = '';

        Camera::pickImages('images', false)->start();
    }

    /**
     * Handle the gallery picker result.
     *
     * @param  array<int, array{path?: string, mimeType?: string}|string>  $files
     */
    #[On(MediaSelected::class)]
    public function onMediaSelected(bool $success, array $files = [], int $count = 0): void
    {
        if (! $success || $files === []) {
            return;
        }

        $file = $files[0];
        $path = is_array($file) ? ($file['path'] ?? null) : $file;

        if (! is_string($path) || ! is_file($path)) {
            $this->wallpaperError = 'Não foi possível ler a imagem escolhida.';

            return;
        }

        try {
            app(Appearance::class)->storeWallpaperFromPath($path);
        } catch (\InvalidArgumentException $exception) {
            $this->wallpaperError = $exception->getMessage();

            return;
        }

        $this->wallpaperUrl = app(Appearance::class)->wallpaperUrl() ?? '';
    }

    public function updateScrimOpacity(float $value): void
    {
        $this->scrimOpacity = $value;

        app(Appearance::class)->setScrimOpacity($value);
    }

    public function confirmRemoveWallpaper(): void
    {
        $this->showRemoveConfirm = true;
    }

    public function cancelRemoveWallpaper(): void
    {
        $this->showRemoveConfirm = false;
    }

    public function removeWallpaper(): void
    {
        app(Appearance::class)->removeWallpaper();

        $this->wallpaperUrl = '';
        $this->showRemoveConfirm = false;
    }

    public function render(): View
    {
        return view('native.appearance-settings', [
            'themes' => Theme::cases(),
            'wallpaperUrl' => $this->wallpaperUrl,
        ]);
    }
}
