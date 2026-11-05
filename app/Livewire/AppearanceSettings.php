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

    public ?string $wallpaperBase64 = null;

    protected $listeners = [
        'setWallpaper',
        'removeWallpaper',
    ];

    public function mount(): void
    {
        $this->selectedTheme = app(Appearance::class)->theme()->value;
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

    public function saveWallpaper(): void
    {
        if ($this->wallpaper === null || ! $this->wallpaper->isValid()) {
            $this->addError('wallpaper', 'Por favor, selecione uma imagem.');

            return;
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($this->wallpaper->getMimeType(), $allowedMimeTypes)) {
            $this->addError('wallpaper', 'Tipo de arquivo inválido. Apenas imagens são permitidas.');

            return;
        }

        // Remove previous wallpaper if exists
        app(Appearance::class)->removeWallpaper();

        $path = $this->wallpaper->store('wallpapers', 'public');
        $this->wallpaperBase64 = null;

        if ($path) {
            Setting::write('appearance.wallpaper', $path);
        }

        $this->dispatch('save-wallpaper', base64: null);
    }

    public function removeWallpaper(): void
    {
        app(Appearance::class)->removeWallpaper();

        $this->wallpaper = null;
        $this->wallpaperBase64 = null;
    }
}
