<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @use(App\Appearance)
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="{{ app(Appearance::class)->theme()->browserColor() }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;600;700;800;900&amp;family=Rajdhani:wght@500;600;700&amp;display=swap" rel="stylesheet">
        {{ Vite::fonts() }}
        @livewireStyles
    </head>
    <body class="min-h-dvh bg-surface font-sans text-on-surface antialiased selection:bg-primary-container selection:text-on-primary <?= app(Appearance::class)->wallpaperPath() ? ' wallpaper__scrim' : '' ?>" data-theme="{{ app(Appearance::class)->theme()->value }}" style="<?= app(Appearance::class)->wallpaperPath() ? '--wallpaper-scrim-opacity: ' . app(Appearance::class)->scrimOpacity() . '; background-image: url(' . app(Appearance::class)->wallpaperUrl() . '); background-size: cover; background-position: center; background-attachment: fixed;' : '' ?>">
        @if (request()->routeIs('duel.arena'))
            {{ $slot }}
        @else
            <div class="min-h-dvh md:flex">
                <aside class="hidden w-60 shrink-0 flex-col border-r border-white/10 bg-surface-container-lowest px-4 py-6 md:flex">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 px-2 text-on-surface">
                        <span class="flex size-10 items-center justify-center rounded-xl bg-primary-container/10 text-primary">
                            <x-icon name="shield" class="size-5" />
                        </span>
                        <span>
                            <span class="block text-base font-semibold">Life Counter</span>
                            <span class="block text-xs text-on-surface-variant">Duel Companion</span>
                        </span>
                    </a>

                    <div class="mt-8">
                        @include('layouts.primary-navigation', ['placement' => 'sidebar'])
                    </div>
                </aside>

                <div class="min-w-0 flex-1 pb-0 md:pb-0">
                    {{ $slot }}
                </div>

            </div>
            </div>
        @endif

        @livewireScripts
    </body>
</html>