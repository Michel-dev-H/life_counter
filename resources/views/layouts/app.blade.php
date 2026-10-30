<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#0f131d">
        @use(App\Appearance)
        <title>{{ $title ?? config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{ Vite::fonts() }}
        @livewireStyles
    </head>
    <body class="min-h-dvh bg-surface font-sans text-on-surface antialiased selection:bg-primary-container selection:text-on-primary" data-theme="{{ app(Appearance::class)->theme()->value }}">
        {{ $slot }}
        @livewireScripts
    </body>
</html>