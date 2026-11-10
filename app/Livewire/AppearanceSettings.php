<?php

namespace App\Livewire;

use App\Appearance;
use App\Enums\Theme;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class AppearanceSettings extends Component
{
    use WithFileUploads;

    public string $selectedTheme = 'holographic';

    public ?UploadedFile $wallpaper = null;

    public ?string $wallpaperPreview = null;

    public bool $showRemoveConfirm = false;

    public float $scrimOpacity = 0.45;

    public function mount(): void
    {
        $this->selectedTheme = app(Appearance::class)->theme()->value;
        $this->scrimOpacity = app(Appearance::class)->scrimOpacity();
    }

    public function render(): View
    {
        $theme = $this->selectedTheme;

        $wallpaperUrl = null;
        $wallpaperPath = Setting::read('appearance.wallpaper');
        if ($wallpaperPath && Storage::disk('public')->exists($wallpaperPath)) {
            $wallpaperUrl = Storage::disk('public')->url($wallpaperPath);
        }

        return view('livewire.appearance-settings', [
            'themes' => Theme::cases(),
            'selectedTheme' => $theme,
            'wallpaperUrl' => $wallpaperUrl,
        ]);
    }

    public function selectTheme(string $value): void
    {
        $theme = Theme::tryFrom($value);

        if ($theme === null) {
            $this->addError('theme', 'Tema inválido.');

            return;
        }

        $this->selectedTheme = $theme->value;
        app(Appearance::class)->selectTheme($theme);

        $this->dispatch(
            'theme-updated',
            theme: $theme->value,
            browserColor: $theme->browserColor(),
        );
    }

    public function updatedWallpaper(): void
    {
        $this->resetErrorBag('wallpaper');

        if ($this->wallpaper === null) {
            $this->wallpaperPreview = null;

            return;
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($this->wallpaper->getMimeType(), $allowedMimeTypes)) {
            $this->addError('wallpaper', 'Tipo de arquivo inválido. Apenas JPEG, PNG ou WebP.');
            $this->wallpaper = null;
            $this->wallpaperPreview = null;

            return;
        }

        if ($this->wallpaper->getSize() > 2 * 1024 * 1024) {
            $this->addError('wallpaper', 'Imagem muito grande. Limite: 2 MB.');
            $this->wallpaper = null;
            $this->wallpaperPreview = null;

            return;
        }

        $dimensions = @getimagesize($this->wallpaper->getRealPath());

        if ($dimensions === false) {
            $this->addError('wallpaper', 'Não foi possível ler a imagem.');
            $this->wallpaper = null;
            $this->wallpaperPreview = null;

            return;
        }

        [$width, $height] = $dimensions;

        if ($width > 4096 || $height > 4096) {
            $this->addError('wallpaper', 'Imagem muito grande. Limite: 4096×4096 px.');
            $this->wallpaper = null;
            $this->wallpaperPreview = null;

            return;
        }

        $this->wallpaperPreview = $this->wallpaper->temporaryUrl();
    }

    public function saveWallpaper(): void
    {
        if ($this->wallpaper === null || ! $this->wallpaper->isValid()) {
            $this->addError('wallpaper', 'Por favor, selecione uma imagem.');

            return;
        }

        app(Appearance::class)->storeWallpaper($this->wallpaper);

        $this->wallpaper = null;
        $this->wallpaperPreview = null;
        $this->resetErrorBag('wallpaper');

        $this->dispatch('wallpaper-updated');
    }

    public function cancelWallpaper(): void
    {
        $this->wallpaper = null;
        $this->wallpaperPreview = null;
        $this->resetErrorBag('wallpaper');
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

        $this->wallpaper = null;
        $this->wallpaperPreview = null;
        $this->showRemoveConfirm = false;
    }

    public function updatedScrimOpacity(): void
    {
        app(Appearance::class)->setScrimOpacity($this->scrimOpacity);
    }
}
