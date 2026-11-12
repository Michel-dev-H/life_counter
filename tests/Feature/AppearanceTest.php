<?php

use App\Appearance;
use App\Enums\Theme;
use App\Livewire\AppearanceSettings;
use App\Livewire\DuelArena;
use App\Models\Duel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('defaults to the holographic theme', function () {
    expect(app(Appearance::class)->theme())->toBe(Theme::Holographic);
});

it('applies the default theme and no wallpaper to the layout', function () {
    $this->get(route('duel.setup'))
        ->assertOk()
        ->assertSee('data-theme="holographic"', false)
        ->assertSee('<meta name="theme-color" content="#0f131d">', false)
        ->assertDontSee('wallpaper__scrim', false);
});

it('lets the user pick a theme and persists it', function () {
    Livewire::test(AppearanceSettings::class)
        ->call('selectTheme', Theme::ShadowRealm->value)
        ->assertDispatched('theme-updated', fn (string $name, array $params): bool => $params === [
            'theme' => Theme::ShadowRealm->value,
            'browserColor' => '#0b0713',
        ])
        ->assertHasNoErrors();

    expect(app(Appearance::class)->theme())->toBe(Theme::ShadowRealm);

    $this->get(route('duel.setup'))
        ->assertOk()
        ->assertSee('data-theme="shadow-realm"', false)
        ->assertSee('<meta name="theme-color" content="#0b0713">', false);
});

it('rejects a theme that does not exist', function () {
    Livewire::test(AppearanceSettings::class)
        ->call('selectTheme', 'neon')
        ->assertHasErrors('theme');

    expect(app(Appearance::class)->theme())->toBe(Theme::Holographic);
});

it('lists the three themes on the appearance page', function () {
    $this->get(route('appearance.edit'))
        ->assertOk()
        ->assertSee('Holographic Duel')
        ->assertSee('Shadow Realm')
        ->assertSee('Minimal Arena');
});

it('stores an uploaded wallpaper on the public disk', function () {
    Storage::fake('public');

    Livewire::test(AppearanceSettings::class)
        ->set('wallpaper', UploadedFile::fake()->image('mesa.jpg', 1200, 2000))
        ->call('saveWallpaper')
        ->assertHasNoErrors();

    $path = app(Appearance::class)->wallpaperPath();

    expect($path)->not->toBeNull();
    Storage::disk('public')->assertExists($path);

    $this->get(route('duel.setup'))
        ->assertOk()
        ->assertSee('wallpaper__scrim', false);
});

it('rejects a wallpaper that is not an image', function () {
    Storage::fake('public');

    Livewire::test(AppearanceSettings::class)
        ->set('wallpaper', UploadedFile::fake()->create('regras.pdf', 120, 'application/pdf'))
        ->call('saveWallpaper')
        ->assertHasErrors('wallpaper');

    expect(app(Appearance::class)->wallpaperPath())->toBeNull();
});

it('replaces the previous wallpaper when a new one is uploaded', function () {
    Storage::fake('public');

    $settings = Livewire::test(AppearanceSettings::class);

    $settings->set('wallpaper', UploadedFile::fake()->image('primeira.jpg')->size(200))
        ->call('saveWallpaper')
        ->assertHasNoErrors();

    $previousPath = app(Appearance::class)->wallpaperPath();

    $settings->set('wallpaper', UploadedFile::fake()->image('segunda.jpg')->size(200))
        ->call('saveWallpaper')
        ->assertHasNoErrors();

    $currentPath = app(Appearance::class)->wallpaperPath();

    expect($currentPath)->not->toBe($previousPath);
    Storage::disk('public')->assertMissing($previousPath);
    Storage::disk('public')->assertExists($currentPath);
});

it('removes the wallpaper from the disk and the layout', function () {
    Storage::fake('public');

    Livewire::test(AppearanceSettings::class)
        ->set('wallpaper', UploadedFile::fake()->image('mesa.jpg'))
        ->call('saveWallpaper')
        ->assertHasNoErrors();

    $path = app(Appearance::class)->wallpaperPath();

    Livewire::test(AppearanceSettings::class)
        ->call('removeWallpaper');

    expect(app(Appearance::class)->wallpaperPath())->toBeNull();
    Storage::disk('public')->assertMissing($path);

    $this->get(route('duel.setup'))
        ->assertOk()
        ->assertDontSee('wallpaper__scrim', false);
});

it('renders seat accents through theme tokens instead of stored colors', function () {
    $duel = Duel::startWith([
        ['name' => 'Ana'],
        ['name' => 'Bruno'],
    ]);

    Livewire::test(DuelArena::class, ['duel' => $duel])
        ->assertDontSee('background-color: #00f2fe', false);
});
