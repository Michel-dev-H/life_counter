<?php

namespace App\Layouts;

use App\Appearance;
use Native\Mobile\Edge\Layouts\NativeLayout;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\UI\Builders\BackgroundLayer;
use Native\Mobile\UI\Concerns\HasBackgroundLayer;

/**
 * Base for the app layouts: paints the user's wallpaper behind every screen.
 *
 * The layer lives on the layout, so it is mounted once and survives tab
 * switches and pushes instead of being rebuilt per screen.
 */
abstract class WallpaperLayout extends NativeLayout
{
    use HasBackgroundLayer;

    public function backgroundLayer(NativeComponent $screen): ?BackgroundLayer
    {
        $appearance = app(Appearance::class);

        $url = $appearance->wallpaperUrl();

        if ($url === null) {
            return null;
        }

        return BackgroundLayer::make(view('native.wallpaper-layer', [
            'url' => $url,
            'scrim' => $appearance->scrimOpacity(),
        ]));
    }
}
