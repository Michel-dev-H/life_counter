@php
    $playerCount = $players->count();
    $layoutMode = $playerCount <= 1 ? '1p' : ($playerCount <= 4 ? '4p' : '8p');
@endphp

<div class="min-h-screen flex flex-col font-body bg-preto-profundo text-branco-frio circuit-bg selection:bg-ciano-holografico selection:text-preto-profundo">
    <!-- TOP HEADER WITH PLAYER COUNT SWITCHER -->
    <header class="bg-grafite/90 border-b border-ciano-holografico/20 px-4 py-2.5 flex items-center justify-between sticky top-0 z-30 backdrop-blur-sm">
        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" class="p-1 rounded text-branco-frio/70 hover:text-ciano-holografico">
                <x-icon name="arrow-left" class="size-5" />
            </a>
            <div>
                <h1 class="font-headline text-label-lg text-ciano-holografico tracking-wider uppercase leading-tight">MULTI-DUELISTAS</h1>
                <span class="text-[11px] text-branco-frio/60 font-body">{{ $playerCount }} jogadores</span>
            </div>
        </div>

        <div class="flex items-center gap-1 bg-preto-profundo/90 p-1 rounded-lg border border-branco-frio/15">
            <button onclick="setPlayerLayout(1)" id="btn-layout-1" class="px-2 py-0.5 rounded text-[11px] font-headline {{ $layoutMode === '1p' ? 'bg-azul-tecnologico text-branco-frio font-bold shadow-[0_0_8px_#176BCE]' : 'text-branco-frio/60 hover:text-branco-frio' }}">1P</button>
            <button onclick="setPlayerLayout(4)" id="btn-layout-4" class="px-2 py-0.5 rounded text-[11px] font-headline {{ $layoutMode === '4p' ? 'bg-azul-tecnologico text-branco-frio font-bold shadow-[0_0_8px_#176BCE]' : 'text-branco-frio/60 hover:text-branco-frio' }}">4P</button>
            <button onclick="setPlayerLayout(8)" id="btn-layout-8" class="px-2 py-0.5 rounded text-[11px] font-headline {{ $layoutMode === '8p' ? 'bg-azul-tecnologico text-branco-frio font-bold shadow-[0_0_8px_#176BCE]' : 'text-branco-frio/60 hover:text-branco-frio' }}">8P</button>
        </div>
    </header>

    <main class="flex-1 p-3 pb-20 flex flex-col justify-between overflow-y-auto">
        <!-- 4P LAYOUT -->
        <div id="layout-4p" class="{{ $layoutMode === '4p' ? 'grid grid-cols-2 gap-2.5 h-full' : 'hidden grid-cols-2 gap-2.5 h-full' }}">
            @foreach ($players as $player)
                <article class="bg-grafite/90 border {{ $player->id === $expandedPlayerId ? 'border-ciano-holografico/40 col-span-full min-h-[65dvh] flex flex-col justify-center shadow-glow' : 'border-branco-frio/20' }} rounded-xl p-3 flex flex-col justify-between relative overflow-hidden shadow-md">
                    <div class="flex items-center justify-between pb-1 border-b border-branco-frio/10">
                        <button wire:click="editPlayerIdentity({{ $player->id }})" class="text-xs font-headline font-bold text-branco-frio uppercase hover:text-ciano-holografico truncate">{{ $player->name }}</button>
                        <span class="text-[9px] text-branco-frio/40 font-headline">P{{ $player->seat + 1 }}</span>
                    </div>
                    <div class="py-2 text-center">
                        <span class="font-display text-3xl font-extrabold text-branco-frio tabular-nums block">{{ $player->life_points }}</span>
                        <span class="text-[9px] font-headline text-branco-frio/50 uppercase tracking-wider">Pontos de Vida</span>
                    </div>
                    <div class="grid grid-cols-2 gap-1.5 pt-1 border-t border-branco-frio/10">
                        <button wire:click="subtract({{ $player->id }}, 1000)" class="py-1.5 bg-vermelho-dano/20 text-vermelho-dano border border-vermelho-dano/40 rounded text-[11px] font-headline font-bold hover:bg-vermelho-dano/30">−1000</button>
                        <button wire:click="add({{ $player->id }}, 1000)" class="py-1.5 bg-verde-recuperacao/20 text-verde-recuperacao border border-verde-recuperacao/40 rounded text-[11px] font-headline font-bold hover:bg-verde-recuperacao/30">+1000</button>
                    </div>
                    <div class="grid grid-cols-4 gap-1 pt-1">
                        <button wire:click="halve({{ $player->id }})" class="py-1 rounded bg-preto-profundo/70 border border-branco-frio/15 text-branco-frio/80 text-[10px] font-headline">÷2</button>
                        <button wire:click="doubleLifePoints({{ $player->id }})" class="py-1 rounded bg-preto-profundo/70 border border-branco-frio/15 text-branco-frio/80 text-[10px] font-headline">×2</button>
                        <button wire:click="openLifePointCalculator({{ $player->id }})" class="py-1 rounded bg-preto-profundo/70 border border-branco-frio/15 text-branco-frio/80 text-[10px] font-headline">Calc</button>
                        <button wire:click="restore({{ $player->id }})" class="py-1 rounded bg-preto-profundo/70 border border-branco-frio/15 text-branco-frio/80 text-[10px] font-headline">Rest.</button>
                    </div>
                    <div class="mt-1 flex items-center justify-end gap-1">
                        <button wire:click="movePlayer({{ $player->id }}, 'up')" class="flex size-8 items-center justify-center rounded bg-surface-container-high text-on-surface-variant transition hover:text-primary disabled:opacity-30" aria-label="Mover {{ $player->name }} para cima">
                            <x-icon name="arrow-up" class="size-4" />
                        </button>
                        <button wire:click="movePlayer({{ $player->id }}, 'down')" class="flex size-8 items-center justify-center rounded bg-surface-container-high text-on-surface-variant transition hover:text-primary disabled:opacity-30" aria-label="Mover {{ $player->name }} para baixo">
                            <x-icon name="arrow-down" class="size-4" />
                        </button>
                        <button wire:click="togglePlayerExpansion({{ $player->id }})" class="flex min-h-8 items-center gap-1 rounded bg-surface-container-high px-2 font-mono text-[10px] text-on-surface-variant transition hover:text-primary" aria-label="{{ $expandedPlayerId === $player->id ? 'Recolher' : 'Expandir' }} painel de {{ $player->name }}">
                            <x-icon name="expand" class="size-4" />
                            {{ $expandedPlayerId === $player->id ? 'Recolher' : 'Expandir' }}
                        </button>
                    </div>
                </article>
            @endforeach
        </div>

        <!-- 8P LAYOUT -->
        <div id="layout-8p" class="{{ $layoutMode === '8p' ? 'grid grid-cols-2 gap-2 h-full overflow-y-auto' : 'hidden grid-cols-2 gap-2 h-full overflow-y-auto' }}">
            @foreach ($players as $player)
                <div class="bg-grafite/90 border {{ $player->id === $expandedPlayerId ? 'border-ciano-holografico/40' : 'border-branco-frio/15' }} rounded-lg p-2 flex flex-col justify-between">
                    <div class="flex justify-between items-center text-[10px] font-headline text-branco-frio">
                        <span class="truncate">{{ $player->name }}</span>
                        <span class="tabular-nums">{{ $player->life_points }}</span>
                    </div>
                    <div class="flex gap-1 mt-1">
                        <button wire:click="subtract({{ $player->id }}, 500)" class="flex-1 py-0.5 bg-vermelho-dano/20 text-vermelho-dano text-[10px] font-headline rounded">−500</button>
                        <button wire:click="add({{ $player->id }}, 500)" class="flex-1 py-0.5 bg-verde-recuperacao/20 text-verde-recuperacao text-[10px] font-headline rounded">+500</button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 1P LAYOUT -->
        <div id="layout-1p" class="{{ $layoutMode === '1p' ? 'flex flex-col justify-between h-full bg-grafite/90 border border-ciano-holografico/40 rounded-xl p-5 shadow-2xl' : 'hidden flex-col justify-between h-full bg-grafite/90 border border-ciano-holografico/40 rounded-xl p-5 shadow-2xl' }}">
            @if ($players->first())
                @php $solo = $players->first(); @endphp
                <div class="flex items-center justify-between">
                    <h3 class="font-headline text-sm font-bold text-ciano-holografico uppercase">MODO SOLITÁRIO</h3>
                    <span class="text-xs bg-azul-tecnologico/30 text-ciano-holografico px-2 py-0.5 rounded font-headline uppercase">SOLO</span>
                </div>
                <div class="my-auto text-center">
                    <span class="text-xs font-headline text-branco-frio/60 uppercase tracking-widest block mb-1">Pontos de Vida</span>
                    <span class="font-display text-6xl font-extrabold text-branco-frio tabular-nums drop-shadow-[0_0_20px_rgba(53,216,255,0.5)]">{{ $solo->life_points }}</span>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-3 border-t border-branco-frio/10">
                    <button wire:click="subtract({{ $solo->id }}, 1000)" class="py-3 bg-vermelho-dano/20 text-vermelho-dano border border-vermelho-dano rounded-lg font-headline text-sm font-bold">−1000 DANO</button>
                    <button wire:click="add({{ $solo->id }}, 1000)" class="py-3 bg-verde-recuperacao/20 text-verde-recuperacao border border-verde-recuperacao rounded-lg font-headline text-sm font-bold">+1000 RECUPERAR</button>
                </div>
            @endif
        </div>
    </main>

    <!-- MIDDLE ACTION BAR -->
    <section class="bg-preto-profundo/90 border-t border-ciano-holografico/30 px-3 py-2 flex items-center justify-between shadow-[0_0_15px_rgba(23,107,206,0.15)]">
        <span class="font-headline text-sm text-ciano-holografico tabular-nums">{{ $duel->formattedTimerRemaining() }}</span>
        <span class="text-[9px] font-body text-branco-frio/50">Ação recente: {{ $lastEvent ? ($lastEvent->after - $lastEvent->before) : 'Nenhuma alteração' }}</span>
        <div class="flex items-center gap-1">
            @foreach (['DP','SP','M1','BP','M2','EP'] as $phase)
                <button wire:click="setPhase('{{ $phase }}')" class="px-2 py-0.5 rounded text-[10px] font-headline uppercase {{ $duel->phase->value === $phase ? 'bg-vermelho-dano text-preto-profundo font-bold shadow-[0_0_10px_#FF4D5A]' : 'bg-preto-profundo text-branco-frio/50 hover:text-branco-frio border border-transparent' }}">{{ $phase }}</button>
            @endforeach
        </div>
        <div class="flex items-center gap-1">
            <button wire:click="undo" title="Desfazer" class="p-1 rounded text-branco-frio/70 hover:text-ciano-holografico hover:bg-azul-tecnologico/20">
                <x-icon name="undo" class="size-5" />
            </button>
            <button wire:click="redo" title="Refazer" class="p-1 rounded text-branco-frio/70 hover:text-ciano-holografico hover:bg-azul-tecnologico/20">
                <x-icon name="redo" class="size-5" />
            </button>
            <button wire:click="drawFirstPlayer" class="px-2 py-0.5 bg-dourado-destaque/15 border border-dourado-destaque/40 rounded text-dourado-destaque text-[10px] font-headline flex items-center gap-1">
                <x-icon name="dice" class="size-4" /> Sorteio
            </button>
            <button wire:click="advanceTurn" class="px-2 py-1 rounded bg-azul-tecnologico/30 border border-ciano-holografico/40 text-ciano-holografico text-[10px] font-headline uppercase">Turno</button>
        </div>
    </section>

    <!-- TOOLS ROW -->
    <div class="flex items-center justify-between gap-2 px-3 py-2">
        <button onclick="openCountersModal()" class="flex-1 py-2 rounded bg-preto-profundo/80 border border-dourado-destaque/40 text-dourado-destaque text-xs font-headline uppercase flex items-center justify-center gap-1">
            <x-icon name="toll" class="size-4" /> Contadores
        </button>
        <button onclick="openDiceModal()" class="flex-1 py-2 rounded bg-preto-profundo/80 border border-ciano-holografico/30 text-ciano-holografico text-xs font-headline uppercase flex items-center justify-center gap-1">
            <x-icon name="dice" class="size-4" /> Dados
        </button>
        <button onclick="openCoinFlipModal()" class="flex-1 py-2 rounded bg-preto-profundo/80 border border-ciano-holografico/30 text-ciano-holografico text-xs font-headline uppercase flex items-center justify-center gap-1">
            <x-icon name="coin" class="size-4" /> Moeda
        </button>
        <button onclick="openRemindersModal()" class="flex-1 py-2 rounded bg-preto-profundo/80 border border-dourado-destaque/40 text-dourado-destaque text-xs font-headline uppercase flex items-center justify-center gap-1">
            <x-icon name="bell" class="size-4" /> Lembretes
        </button>
        <button wire:click="toggleHapticFeedback" class="py-2 px-3 rounded bg-preto-profundo/80 border border-branco-frio/20 text-branco-frio/70 text-xs font-headline uppercase">
            Vibração Off
        </button>
    </div>

    <!-- FOOTER NAV -->
    <footer class="fixed bottom-0 left-0 right-0 z-40 bg-grafite/95 border-t border-ciano-holografico/20 px-4 py-2 backdrop-blur flex items-center justify-around">
        <a href="{{ route('home') }}" class="flex flex-col items-center text-branco-frio/60 hover:text-ciano-holografico">
            <x-icon name="home" class="size-5" />
            <span class="text-[10px] font-headline uppercase">Início</span>
        </a>
        <a href="{{ route('duel.setup') }}" class="flex flex-col items-center text-ciano-holografico">
            <x-icon name="swords" class="size-5" />
            <span class="text-[10px] font-headline uppercase">Arena</span>
        </a>
        <a href="#" class="flex flex-col items-center text-branco-frio/60 hover:text-ciano-holografico">
            <x-icon name="toll" class="size-5" />
            <span class="text-[10px] font-headline uppercase">Contadores</span>
        </a>
        <a href="{{ route('appearance.edit') }}" class="flex flex-col items-center text-branco-frio/60 hover:text-ciano-holografico">
            <x-icon name="palette" class="size-5" />
            <span class="text-[10px] font-headline uppercase">Estilos</span>
        </a>
    </footer>

    @include('livewire.partials.counters-modal')
    @include('livewire.partials.tools-modals')
    <script>
        function setPlayerLayout(count) {
            const l1 = document.getElementById('layout-1p');
            const l4 = document.getElementById('layout-4p');
            const l8 = document.getElementById('layout-8p');
            const b1 = document.getElementById('btn-layout-1');
            const b4 = document.getElementById('btn-layout-4');
            const b8 = document.getElementById('btn-layout-8');
            const active = 'px-2 py-0.5 rounded text-[11px] font-headline bg-azul-tecnologico text-branco-frio font-bold shadow-[0_0_8px_#176BCE]';
            const inactive = 'px-2 py-0.5 rounded text-[11px] font-headline text-branco-frio/60 hover:text-branco-frio';
            [l1, l4, l8].forEach((el) => { if (el) { el.classList.add('hidden'); el.classList.remove('flex', 'grid'); } });
            [b1, b4, b8].forEach((btn) => { if (btn) btn.className = inactive; });
            if (count === 1) { l1.classList.remove('hidden'); l1.classList.add('flex'); b1.className = active; }
            else if (count === 4) { l4.classList.remove('hidden'); l4.classList.add('grid'); b4.className = active; }
            else if (count === 8) { l8.classList.remove('hidden'); l8.classList.add('grid'); b8.className = active; }
        }
    </script>
</div>
