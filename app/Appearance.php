<?php

namespace App;

use App\Enums\Theme;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Native\Mobile\UI\Theme as NativeUiTheme;
use Throwable;

class Appearance
{
    private const THEME_KEY = 'appearance.theme';

    private const WALLPAPER_KEY = 'appearance.wallpaper';

    private const SCRIM_OPACITY_KEY = 'appearance.scrim_opacity';

    /**
     * Wallpapers live inside the app's `public/` directory.
     *
     * The native renderer resolves a relative image path against that
     * directory, and the app's own HTTP server serves it — so the same file
     * works on device, under Jump and in the web build, with no symlink to
     * create on the device filesystem.
     */
    private const WALLPAPER_DIRECTORY = 'wallpapers';

    /** Cached wallpaper path, relative to `public/`. */
    private ?string $wallpaperPath = null;

    private bool $wallpaperLoaded = false;

    public function theme(): Theme
    {
        return Theme::tryFrom(Setting::read(self::THEME_KEY) ?? '') ?? Theme::Holographic;
    }

    /**
     * Push the selected theme's palette to the native UI theme store, so the
     * native chrome (nav bar, tab bar, buttons, inputs) paints with the
     * theme the user picked.
     */
    public function applyNativeTheme(): void
    {
        try {
            NativeUiTheme::merge($this->theme()->nativeTokens());
        } catch (Throwable $exception) {
            // The pick lives in the database, which only exists after the
            // device runs its migrations. A boot before that — or a database
            // recreated mid-session — falls back to the palette published in
            // config/native-ui.php instead of taking the app down.
            report($exception);
        }
    }

    public function selectTheme(Theme $theme): void
    {
        Setting::write(self::THEME_KEY, $theme->value);
    }

    /**
     * The wallpaper path relative to `public/`, or null when there is none.
     *
     * The value is read once per application instance: the background layer
     * asks for it on every publish, and the arena polls every second.
     */
    public function wallpaperPath(): ?string
    {
        if (! $this->wallpaperLoaded) {
            $this->wallpaperPath = Setting::read(self::WALLPAPER_KEY);
            $this->wallpaperLoaded = true;
        }

        if ($this->wallpaperPath === null || ! is_file(public_path($this->wallpaperPath))) {
            return null;
        }

        return $this->wallpaperPath;
    }

    public function wallpaperUrl(): ?string
    {
        $path = $this->wallpaperPath();

        return $path === null ? null : '/'.$path;
    }

    public function scrimOpacity(): float
    {
        $value = Setting::read(self::SCRIM_OPACITY_KEY);

        if ($value === null) {
            return 0.45;
        }

        return max(0.0, min(0.8, (float) $value));
    }

    public function setScrimOpacity(float $value): void
    {
        Setting::write(self::SCRIM_OPACITY_KEY, (string) max(0.0, min(0.8, $value)));
    }

    public function storeWallpaper(UploadedFile $file): void
    {
        $this->removeWallpaper();

        $name = Str::uuid().'.'.($file->guessExtension() ?: 'jpg');

        File::ensureDirectoryExists(public_path(self::WALLPAPER_DIRECTORY));

        $file->move(public_path(self::WALLPAPER_DIRECTORY), $name);

        $this->rememberWallpaper(self::WALLPAPER_DIRECTORY.'/'.$name);
    }

    /**
     * Store a wallpaper picked on the device (native gallery picker).
     *
     * The file comes from the app-private storage of the device, so it is
     * copied into the app's public directory before the setting points at it.
     *
     * @throws InvalidArgumentException when the file is not a supported image
     */
    public function storeWallpaperFromPath(string $sourcePath): void
    {
        $this->assertValidWallpaper($sourcePath);

        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $extension = $extension !== '' ? strtolower($extension) : 'jpg';

        $destination = self::WALLPAPER_DIRECTORY.'/'.Str::uuid().'.'.$extension;

        File::ensureDirectoryExists(public_path(self::WALLPAPER_DIRECTORY));

        File::put(public_path($destination), (string) file_get_contents($sourcePath));

        $this->removeWallpaper();

        $this->rememberWallpaper($destination);
    }

    public function removeWallpaper(): void
    {
        $path = $this->wallpaperPath();

        if ($path !== null) {
            @unlink(public_path($path));
        }

        $this->rememberWallpaper(null);
    }

    private function rememberWallpaper(?string $path): void
    {
        Setting::write(self::WALLPAPER_KEY, $path);

        $this->wallpaperPath = $path;
        $this->wallpaperLoaded = true;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function assertValidWallpaper(string $path): void
    {
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

        if (! in_array(mime_content_type($path), $allowedMimeTypes, true)) {
            throw new InvalidArgumentException('Tipo de arquivo inválido. Apenas JPEG, PNG ou WebP.');
        }

        if (filesize($path) > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('Imagem muito grande. Limite: 2 MB.');
        }

        $dimensions = @getimagesize($path);

        if ($dimensions === false) {
            throw new InvalidArgumentException('Não foi possível ler a imagem.');
        }

        [$width, $height] = $dimensions;

        if ($width > 4096 || $height > 4096) {
            throw new InvalidArgumentException('Imagem muito grande. Limite: 4096×4096 px.');
        }
    }
}
