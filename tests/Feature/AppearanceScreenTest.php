<?php

use App\Appearance;
use App\Enums\Theme;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Native\Mobile\Testing\Native;

uses(RefreshDatabase::class);

it('defaults to the holographic theme', function () {
    expect(app(Appearance::class)->theme())->toBe(Theme::Holographic);
});

it('lists the three themes on the appearance screen', function () {
    Native::visit('/appearance/settings')
        ->assertSee('Holographic Duel')
        ->assertSee('Shadow Realm')
        ->assertSee('Minimal Arena');
});

it('lets the user pick a theme and persists it', function () {
    Native::visit('/appearance/settings')->tap('Shadow Realm');

    expect(app(Appearance::class)->theme())->toBe(Theme::ShadowRealm);
    expect(Setting::read('appearance.theme'))->toBe('shadow-realm');
});

it('applies the picked theme to the native ui theme store', function () {
    Native::visit('/appearance/settings')->tap('Minimal Arena');

    expect(\Native\Mobile\UI\Theme::get('light.primary'))->toBe('#2E7D5B');
});

it('keeps the scrim opacity inside the supported range', function () {
    Native::visit('/appearance/settings')
        ->call('updateScrimOpacity', 0.8);

    expect(app(Appearance::class)->scrimOpacity())->toBe(0.8);
});

it('removes the wallpaper from the app', function () {
    $path = 'wallpapers/teste.jpg';
    File::ensureDirectoryExists(public_path(dirname($path)));
    file_put_contents(public_path($path), 'conteudo');
    Setting::write('appearance.wallpaper', $path);

    Native::visit('/appearance/settings')
        ->tap('Remover papel de parede')
        ->tap('Apagar imagem');

    expect(app(Appearance::class)->wallpaperPath())->toBeNull();
    expect(file_exists(public_path($path)))->toBeFalse();
});

it('exposes the wallpaper path relative to the public directory', function () {
    $path = 'wallpapers/teste.jpg';
    File::ensureDirectoryExists(public_path(dirname($path)));
    file_put_contents(public_path($path), 'conteudo');
    Setting::write('appearance.wallpaper', $path);

    expect(app(Appearance::class)->wallpaperUrl())->toBe('/'.$path);
});
