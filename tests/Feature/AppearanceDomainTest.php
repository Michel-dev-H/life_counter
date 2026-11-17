<?php

use App\Appearance;
use App\Enums\Theme;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Native\Mobile\UI\Theme as NativeUiTheme;

uses(RefreshDatabase::class);

beforeEach(function () {
    // A small valid PNG on the temp filesystem, standing in for an image
    // picked from the device gallery.
    $path = tempnam(sys_get_temp_dir(), 'papel').'.png';

    $image = imagecreatetruecolor(12, 12);
    imagepng($image, $path);
    imagedestroy($image);

    $this->wallpaperSource = $path;
});

afterEach(function () {
    @unlink($this->wallpaperSource);

    File::deleteDirectory(public_path('wallpapers'));
    File::ensureDirectoryExists(public_path('wallpapers'));
});

it('defaults to the holographic theme', function () {
    expect(app(Appearance::class)->theme())->toBe(Theme::Holographic);
});

it('keeps the selected theme in the settings', function () {
    app(Appearance::class)->selectTheme(Theme::ShadowRealm);

    expect(app(Appearance::class)->theme())->toBe(Theme::ShadowRealm)
        ->and(Setting::read('appearance.theme'))->toBe('shadow-realm');
});

it('keeps the boot alive when the settings table does not exist yet', function () {
    Schema::drop('settings');

    app(Appearance::class)->applyNativeTheme();

    expect(NativeUiTheme::get('light.primary'))->toBe('#35D8FF');
});

it('stores a wallpaper picked on the device inside the public directory', function () {
    app(Appearance::class)->storeWallpaperFromPath($this->wallpaperSource);

    $path = app(Appearance::class)->wallpaperPath();

    expect($path)->not->toBeNull()
        ->and($path)->toStartWith('wallpapers/')
        ->and(file_exists(public_path($path)))->toBeTrue();
});

it('replaces the previous wallpaper when a new one is picked', function () {
    app(Appearance::class)->storeWallpaperFromPath($this->wallpaperSource);
    $previous = app(Appearance::class)->wallpaperPath();

    $second = $this->wallpaperSource.'-2.png';
    copy($this->wallpaperSource, $second);

    app(Appearance::class)->storeWallpaperFromPath($second);
    $current = app(Appearance::class)->wallpaperPath();

    expect($current)->not->toBe($previous)
        ->and(file_exists(public_path($previous)))->toBeFalse()
        ->and(file_exists(public_path($current)))->toBeTrue();

    @unlink($second);
});

it('rejects a file that is not a supported image', function () {
    $path = tempnam(sys_get_temp_dir(), 'papel').'.pdf';
    file_put_contents($path, 'nao e uma imagem');

    expect(fn () => app(Appearance::class)->storeWallpaperFromPath($path))
        ->toThrow(InvalidArgumentException::class, 'Tipo de arquivo inválido. Apenas JPEG, PNG ou WebP.');

    @unlink($path);
});

it('rejects an image that cannot be read', function () {
    $path = tempnam(sys_get_temp_dir(), 'papel').'.jpg';
    file_put_contents($path, 'nao e uma imagem');

    expect(fn () => app(Appearance::class)->storeWallpaperFromPath($path))
        ->toThrow(InvalidArgumentException::class);

    @unlink($path);
});

it('removes the wallpaper from the app', function () {
    app(Appearance::class)->storeWallpaperFromPath($this->wallpaperSource);
    $path = app(Appearance::class)->wallpaperPath();

    app(Appearance::class)->removeWallpaper();

    expect(app(Appearance::class)->wallpaperPath())->toBeNull()
        ->and(file_exists(public_path($path)))->toBeFalse();
});

it('keeps the scrim opacity between zero and eighty percent', function () {
    $appearance = app(Appearance::class);

    $appearance->setScrimOpacity(0.8);
    expect($appearance->scrimOpacity())->toBe(0.8);

    $appearance->setScrimOpacity(1.5);
    expect($appearance->scrimOpacity())->toBe(0.8);

    $appearance->setScrimOpacity(-1);
    expect($appearance->scrimOpacity())->toBe(0.0);
});
