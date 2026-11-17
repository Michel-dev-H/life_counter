<?php

namespace App\Layouts;

use Native\Mobile\Edge\Layouts\Builders\NavBar;
use Native\Mobile\Edge\Layouts\Builders\Tab;
use Native\Mobile\Edge\Layouts\Builders\TabBar;
use Native\Mobile\Edge\NativeComponent;

/**
 * Chrome shared by the app's top-level destinations: a native top bar and a
 * native bottom tab bar.
 *
 * Screens pushed on top of these (the arena, the duel tools, the recent
 * duels list) are registered outside this group, so the framework swaps the
 * chrome from tabs to a navigation stack on its own.
 *
 * It lives in `app/Layouts` rather than `app/NativeComponents/Layouts` because
 * `php artisan native:validate` scans every class under `app/NativeComponents`
 * and requires each one to extend NativeComponent — a layout is not a screen.
 */
class TabsLayout extends WallpaperLayout
{
    protected ?string $font = 'headline';

    public function navBar(NativeComponent $screen): ?NavBar
    {
        return NavBar::make()
            ->title($screen->navTitle())
            ->font('headline');
    }

    public function tabBar(NativeComponent $screen): ?TabBar
    {
        return TabBar::make()
            ->activeColor(theme('primary', '#35D8FF'))
            ->font('headline')
            ->add(Tab::link('Início', '/', icon: 'house.fill'))
            ->add(Tab::link('Partida', '/duel/new', icon: 'plus'))
            ->add(Tab::link('Aparência', '/appearance/settings', icon: 'paintbrush'));
    }

    public function usesNativeChrome(): bool
    {
        return true;
    }
}
