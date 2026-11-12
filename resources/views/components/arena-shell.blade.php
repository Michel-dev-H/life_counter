@props([
    'title' => '',
    'subtitle' => '',
    'backHref' => null,
    'active' => null,
])

<div class="min-h-screen flex flex-col font-body bg-preto-profundo text-branco-frio circuit-bg selection:bg-ciano-holografico selection:text-preto-profundo">
    <header class="bg-grafite/90 border-b border-ciano-holografico/20 px-4 py-3 flex items-center justify-between sticky top-0 z-30 backdrop-blur-sm">
        <div class="flex items-center gap-2 min-w-0">
            @if ($backHref)
                <a href="{{ $backHref }}" class="p-1 rounded text-branco-frio/70 hover:text-ciano-holografico shrink-0">
                    <x-icon name="arrow-left" class="size-5" />
                </a>
            @endif
            <div class="min-w-0">
                @if ($title)
                    <p class="font-headline text-[10px] uppercase tracking-widest text-ciano-holografico">{{ $title }}</p>
                @endif
                @if ($subtitle)
                    <h1 class="truncate font-headline text-label-lg text-ciano-holografico tracking-wider uppercase leading-tight">{{ $subtitle }}</h1>
                @endif
            </div>
        </div>
        @isset($status)
            @if ($status)
                <span class="flex items-center gap-1.5 rounded bg-preto-profundo/80 border border-branco-frio/15 px-2 py-1 font-headline text-[10px] text-branco-frio/60 uppercase whitespace-nowrap shrink-0">
                    {!! $status !!}
                </span>
            @endif
        @endisset
    </header>

    <main class="relative flex-1 mx-auto w-full max-w-2xl px-4 pb-24 pt-5">
        {{ $slot }}
    </main>

    <footer class="fixed bottom-0 left-0 right-0 z-40 bg-grafite/95 border-t border-ciano-holografico/20 px-4 py-2 backdrop-blur flex items-center justify-around">
        <a href="{{ route('home') }}" class="flex flex-col items-center {{ $active === 'home' ? 'text-ciano-holografico' : 'text-branco-frio/60 hover:text-ciano-holografico' }}">
            <x-icon name="home" class="size-5" />
            <span class="text-[10px] font-headline uppercase">Início</span>
        </a>
        <a href="{{ route('duel.setup') }}" class="flex flex-col items-center {{ $active === 'arena' ? 'text-ciano-holografico' : 'text-branco-frio/60 hover:text-ciano-holografico' }}">
            <x-icon name="swords" class="size-5" />
            <span class="text-[10px] font-headline uppercase">Arena</span>
        </a>
        <a href="#" class="flex flex-col items-center {{ $active === 'counters' ? 'text-ciano-holografico' : 'text-branco-frio/60 hover:text-ciano-holografico' }}">
            <x-icon name="toll" class="size-5" />
            <span class="text-[10px] font-headline uppercase">Contadores</span>
        </a>
        <a href="{{ route('appearance.edit') }}" class="flex flex-col items-center {{ $active === 'estilos' ? 'text-ciano-holografico' : 'text-branco-frio/60 hover:text-ciano-holografico' }}">
            <x-icon name="palette" class="size-5" />
            <span class="text-[10px] font-headline uppercase">Estilos</span>
        </a>
    </footer>
</div>
