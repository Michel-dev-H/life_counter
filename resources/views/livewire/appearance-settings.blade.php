@props(['themes', 'selectedTheme', 'wallpaperUrl'])
@use(App\Appearance)

<div x-on:theme-updated.window="document.body.dataset.theme = $event.detail.theme; document.querySelector('meta[name=theme-color]').content = $event.detail.browserColor">
    <h2 class="font-mono text-label-md uppercase tracking-wider text-on-surface">Aparência</h2>

    <section class="mt-4 grid grid-cols-3 gap-2">
        @foreach ($themes as $theme)
            <button
                type="button"
                wire:click="selectTheme('{{ $theme->value }}')"
                @class([
                    'chamfer flex flex-col items-center justify-center gap-1 border rounded px-3 py-2 transition',
                    'border-primary-container bg-primary-container/10 text-primary-container' => $selectedTheme === $theme->value,
                    'border-white/10 bg-surface-container-low text-on-surface-variant hover:border-primary-container/40 hover:text-primary' => $selectedTheme !== $theme->value,
                ])
                data-theme-value="{{ $theme->value }}"
                aria-label="{{ $theme->label() }} em {{ $theme->description() }}"
            >
                <span class="font-mono text-label-lg font-bold">{{ $theme->label() }}</span>
                <span class="font-mono text-label-xs uppercase text-on-surface-variant">{{ $theme->description() }}</span>
            </button>
        @endforeach
    </section>

    <section class="mt-6">
        <h3 class="font-mono text-label-sm uppercase tracking-wider text-on-surface">Papel de parede</h3>

        @if ($wallpaperUrl)
            <div class="mt-2 rounded-lg bg-surface-container-lowest/80 p-3 text-center shadow-inner">
                <img src="{{ $wallpaperUrl }}" alt="Papel de parede do tema" class="max-w-full h-auto rounded-tl-md rounded-br-md">
                <button
                    type="button"
                    wire:click="removeWallpaper"
                    class="mt-2 flex size-8 items-center justify-center rounded bg-surface-container-high text-on-surface-variant transition hover:text-primary"
                    aria-label="Remover papel de parede"
                >
                    <x-icon name="x" class="size-4" />
                </button>
            </div>
        @else
            <div class="mt-2 flex items-center gap-2 rounded bg-surface-container-high px-3 py-2 text-on-surface-variant">
                <x-icon name="cloud-upload" class="size-4" />
                <span>Nenhum papel de parede</span>
            </div>
        @endif
    </section>

    <section class="mt-6">
        <h3 class="font-mono text-label-sm uppercase tracking-wider text-on-surface">Selecionar tema</h3>

        <p class="font-mono text-label-sm text-on-surface-variant">
            Tema atual: <strong>{{ app(Appearance::class)->theme()->label() }}</strong>.
        </p>

        <p class="mt-2 font-mono text-label-sm text-on-surface-variant">
            Escolha entre <strong>Holographic Duel</strong>, <strong>Shadow Realm</strong> ou <strong>Minimal Arena</strong>. A mudança é aplicada imediatamente e persistida entre sessões pelo layout (data-theme no <body>).
        </p>
    </section>
</div>
