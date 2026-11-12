@props(['themes', 'selectedTheme', 'wallpaperUrl'])
@use(App\Appearance)

<div x-on:theme-updated.window="document.body.dataset.theme = $event.detail.theme; document.querySelector('meta[name=theme-color]').content = $event.detail.browserColor" class="min-h-screen flex flex-col font-body bg-preto-profundo text-branco-frio circuit-bg selection:bg-ciano-holografico selection:text-preto-profundo">
    <!-- TOP HEADER -->
    <header class="bg-grafite/90 border-b border-ciano-holografico/20 px-4 py-3 flex items-center justify-between sticky top-0 z-30 backdrop-blur-sm">
        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" class="p-1 rounded text-branco-frio/70 hover:text-ciano-holografico">
                <x-icon name="arrow-left" class="size-5" />
            </a>
            <div>
                <h1 class="font-headline text-label-lg text-ciano-holografico tracking-wider uppercase leading-tight">PERSONALIZAÇÃO VISUAL</h1>
                <p class="text-[11px] text-branco-frio/60 font-body">Estilos de Duel Disk e Superfícies Holográficas</p>
            </div>
        </div>

        <button
            type="button"
            @if ($wallpaperPreview) wire:click="saveWallpaper" @endif
            class="px-3 py-1 rounded bg-ciano-holografico text-preto-profundo text-xs font-headline font-bold uppercase shadow-[0_0_10px_#35D8FF] {{ $wallpaperPreview ? '' : 'opacity-50 cursor-default' }}"
        >
            SALVAR
        </button>
    </header>

    <main class="flex-1 p-4 pb-20 space-y-6 overflow-y-auto">
        <!-- SECTION 1: THEME ARCHETYPES -->
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="font-headline text-xs font-bold uppercase tracking-wider text-ciano-holografico flex items-center gap-1.5">
                    <x-icon name="style" class="size-4" />
                    ESTILOS DE DUEL DISK
                </h2>
                <span class="text-[11px] text-branco-frio/50 font-body">3 Modos de Interface</span>
            </div>

            @foreach ($themes as $theme)
                <label class="block bg-grafite border-2 {{ $selectedTheme === $theme->value ? 'border-ciano-holografico shadow-[0_0_15px_rgba(53,216,255,0.2)]' : 'border-branco-frio/15 hover:border-ciano-holografico/50' }} rounded-xl p-3.5 cursor-pointer relative transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <input type="radio" name="duel_theme" {{ $selectedTheme === $theme->value ? 'checked' : '' }} wire:click="selectTheme('{{ $theme->value }}')" class="text-ciano-holografico focus:ring-0">
                            <div>
                                <h3 class="font-headline text-sm font-bold text-branco-frio uppercase">{{ $theme->label() }}</h3>
                                <p class="font-body text-xs text-branco-frio/70">{{ $theme->description() }}</p>
                            </div>
                        </div>
                        @if ($selectedTheme === $theme->value)
                            <span class="text-[10px] font-headline bg-ciano-holografico/20 border border-ciano-holografico/50 text-ciano-holografico px-2 py-0.5 rounded uppercase">ATIVO</span>
                        @endif
                    </div>
                    <div class="flex gap-1.5 mt-3 pt-2 border-t border-branco-frio/10">
                        <span class="h-2 flex-1 rounded bg-[#080B12]"></span>
                        <span class="h-2 flex-1 rounded bg-[#176BCE]"></span>
                        <span class="h-2 flex-1 rounded bg-[#35D8FF]"></span>
                        <span class="h-2 flex-1 rounded bg-[#FF4D5A]"></span>
                    </div>
                </label>
            @endforeach
        </section>

        <!-- SECTION 2: WALLPAPER GALLERY -->
        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="font-headline text-xs font-bold uppercase tracking-wider text-ciano-holografico flex items-center gap-1.5">
                    <x-icon name="wallpaper" class="size-4" />
                    SUPERFÍCIE &amp; PAPÉIS DE PAREDE
                </h2>
                <span class="text-[11px] text-branco-frio/50 font-body">Preserva Legibilidade</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="relative rounded-xl overflow-hidden border-2 border-ciano-holografico bg-gradient-to-br from-preto-profundo via-azul-tecnologico/30 to-preto-profundo p-3 h-28 flex flex-col justify-between shadow-[0_0_12px_rgba(53,216,255,0.3)]">
                    <x-icon name="blur" class="size-5 text-ciano-holografico" />
                    <div>
                        <span class="font-headline text-xs font-bold text-branco-frio block">Arena Holográfica</span>
                        <span class="text-[9px] text-ciano-holografico font-headline uppercase">Selecionado</span>
                    </div>
                </div>

                <div class="relative rounded-xl overflow-hidden border border-branco-frio/15 bg-gradient-to-br from-preto-profundo via-purple-950/40 to-preto-profundo p-3 h-28 flex flex-col justify-between hover:border-ciano-holografico transition cursor-pointer">
                    <x-icon name="dark_mode" class="size-5 text-purple-400" />
                    <div>
                        <span class="font-headline text-xs font-bold text-branco-frio block">Dimensão Sombria</span>
                        <span class="text-[9px] text-branco-frio/50 font-headline uppercase">Padrão TCG</span>
                    </div>
                </div>

                <div class="relative rounded-xl overflow-hidden border border-branco-frio/15 bg-gradient-to-br from-preto-profundo via-amber-950/30 to-preto-profundo p-3 h-28 flex flex-col justify-between hover:border-ciano-holografico transition cursor-pointer">
                    <x-icon name="star" class="size-5 text-dourado-destaque" />
                    <div>
                        <span class="font-headline text-xs font-bold text-branco-frio block">Símbolos Místicos</span>
                        <span class="text-[9px] text-branco-frio/50 font-headline uppercase">Milenar</span>
                    </div>
                </div>

                <!-- Custom Upload -->
                <label class="relative rounded-xl overflow-hidden border border-dashed border-branco-frio/30 bg-grafite/50 p-3 h-28 flex flex-col items-center justify-center text-center hover:border-ciano-holografico transition cursor-pointer">
                    <x-icon name="photo" class="size-6 text-ciano-holografico  mb-1" />
                    <span class="font-headline text-[11px] text-branco-frio uppercase">Galeria do Celular</span>
                    <span class="text-[9px] text-branco-frio/50 font-body">Importar Imagem</span>
                    <input type="file" wire:model="wallpaper" accept="image/jpeg,image/png,image/webp" class="hidden">
                </label>
            </div>

            @if ($wallpaperUrl)
                <div class="mt-2 rounded-lg bg-surface-container-lowest/80 p-3 text-center shadow-inner">
                    <img src="{{ $wallpaperUrl }}" alt="Papel de parede atual" class="max-w-full h-auto rounded-tl-md rounded-br-md">
                    <button type="button" wire:click="confirmRemoveWallpaper" class="mt-2 flex size-8 items-center justify-center rounded bg-surface-container-high text-on-surface-variant transition hover:text-primary mx-auto" aria-label="Remover papel de parede">
                        <x-icon name="x" class="size-4" />
                    </button>
                </div>
            @endif

            @if ($showRemoveConfirm)
                <div class="mt-2 rounded border border-red-500/40 bg-red-500/10 p-3 text-center">
                    <p class="font-mono text-label-sm text-on-surface">Remover o papel de parede?</p>
                    <div class="mt-2 flex justify-center gap-2">
                        <button type="button" wire:click="removeWallpaper" class="rounded bg-red-500/80 px-3 py-1 text-on-surface">Confirmar</button>
                        <button type="button" wire:click="cancelRemoveWallpaper" class="rounded bg-surface-container-high px-3 py-1 text-on-surface-variant">Cancelar</button>
                    </div>
                </div>
            @endif

            @if ($wallpaperPreview)
                <div class="mt-2 rounded-lg bg-surface-container-lowest/80 p-3 text-center shadow-inner">
                    <img src="{{ $wallpaperPreview }}" alt="Pré-visualização" class="max-w-full h-auto rounded-tl-md rounded-br-md">
                    <div class="mt-2 flex justify-center gap-2">
                        <button type="button" wire:click="saveWallpaper" class="rounded bg-primary-container px-3 py-1 text-on-primary">Aplicar</button>
                        <button type="button" wire:click="cancelWallpaper" class="rounded bg-surface-container-high px-3 py-1 text-on-surface-variant">Cancelar</button>
                    </div>
                </div>
            @endif

            @error('wallpaper')
                <p class="mt-2 font-mono text-label-sm text-red-400">{{ $message }}</p>
            @enderror
        </section>

        <!-- SECTION 3: CONTRAST & LEGIBILITY -->
        <section class="bg-grafite border border-branco-frio/15 rounded-xl p-4 space-y-3">
            <h3 class="font-headline text-xs font-bold uppercase tracking-wider text-branco-frio flex items-center gap-1.5">
                <x-icon name="visibility" class="size-4" />
                AJUSTES DE CONTRASTE EM MESA
            </h3>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-body text-xs font-bold text-branco-frio block">Camada de Escurecimento (Dimming)</span>
                        <span class="text-[11px] text-branco-frio/60 font-body">Garante legibilidade sob luz forte de salão de eventos</span>
                    </div>
                    <input type="range" min="0" max="0.8" step="0.05" wire:model.live="scrimOpacity" class="accent-ciano-holografico w-24">
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-branco-frio/10">
                    <div>
                        <span class="font-body text-xs font-bold text-branco-frio block">Inversão 180° Automática</span>
                        <span class="text-[11px] text-branco-frio/60 font-body">Inverte automaticamente o P2 quando apoiado em mesa</span>
                    </div>
                    <input type="checkbox" class="rounded text-ciano-holografico focus:ring-0">
                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER NAV -->
    <footer class="fixed bottom-0 left-0 right-0 z-40 bg-grafite/95 border-t border-ciano-holografico/20 px-4 py-2 backdrop-blur flex items-center justify-around">
        <a href="{{ route('home') }}" class="flex flex-col items-center text-branco-frio/60 hover:text-ciano-holografico">
            <x-icon name="swords" class="size-5" />
            <span class="text-[10px] font-headline uppercase">Arena</span>
        </a>
        <a href="#" class="flex flex-col items-center text-branco-frio/60 hover:text-ciano-holografico">
            <x-icon name="toll" class="size-5" />
            <span class="text-[10px] font-headline uppercase">Contadores</span>
        </a>
        <a href="{{ route('duel.setup') }}" class="flex flex-col items-center text-branco-frio/60 hover:text-ciano-holografico">
            <x-icon name="users" class="size-5" />
            <span class="text-[10px] font-headline uppercase">Multi</span>
        </a>
        <a href="{{ route('appearance.edit') }}" class="flex flex-col items-center text-ciano-holografico">
            <x-icon name="palette" class="size-5" />
            <span class="text-[10px] font-headline uppercase">Estilos</span>
        </a>
    </footer>
</div>
