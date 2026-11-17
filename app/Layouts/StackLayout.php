<?php

namespace App\Layouts;

use Native\Mobile\Edge\Layouts\Builders\NavBar;
use Native\Mobile\Edge\NativeComponent;

/**
 * Chrome for screens pushed on top of the tabbed destinations: a native top
 * bar with the screen title and the system back chevron.
 */
class StackLayout extends WallpaperLayout
{
    protected ?string $font = 'headline';

    public function navBar(NativeComponent $screen): ?NavBar
    {
        return NavBar::make()
            ->title($screen->navTitle())
            ->font('headline')
            ->back();
    }

    public function usesNativeChrome(): bool
    {
        return true;
    }
}
