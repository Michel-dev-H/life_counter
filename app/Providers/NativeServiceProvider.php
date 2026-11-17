<?php

namespace App\Providers;

use App\Appearance;
use Illuminate\Support\ServiceProvider;
use Native\Mobile\Providers\CameraServiceProvider;
use Native\Mobile\UI\NativeUIServiceProvider;

class NativeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // The user picks the theme in the app; the native chrome paints with
        // it. Merging here (after nativephp/mobile-ui loaded the default
        // palette from config/native-ui.php) keeps both in sync at boot.
        //
        // Only on a native request: the theme is persisted in the database,
        // and this provider boots on every request — web or test — where the
        // native theme store is not what paints the screen.
        //
        // This also runs on the device's console boots (`optimize:clear`,
        // `migrate --force`, the caches) where the settings table may not
        // exist yet. applyNativeTheme() catches that failure and keeps the
        // palette from config, so boot never blocks the migration.
        //
        // The bridge sets NATIVEPHP_RUNNING in the request environment; it is
        // read from $_SERVER because the config cache would freeze an env()
        // lookup.
        if (! ($_SERVER['NATIVEPHP_RUNNING'] ?? false) || $this->app->runningUnitTests()) {
            return;
        }

        app(Appearance::class)->applyNativeTheme();
    }

    /**
     * The NativePHP plugins to enable.
     *
     * Only plugins listed here will be compiled into your native builds.
     * This is a security measure to prevent transitive dependencies from
     * automatically registering plugins without your explicit consent.
     *
     * @return array<int, class-string<ServiceProvider>>
     */
    public function plugins(): array
    {
        return [
            NativeUIServiceProvider::class,
            CameraServiceProvider::class,

        ];
    }
}
