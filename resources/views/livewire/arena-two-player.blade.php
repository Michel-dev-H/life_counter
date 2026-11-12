@php
    $players = $duel->duelPlayers;
    $topPlayer = $players->get(1) ?? $players->first();
    $bottomPlayer = $players->first();
@endphp

<div class="min-h-screen flex flex-col font-body bg-preto-profundo text-branco-frio circuit-bg selection:bg-ciano-holografico selection:text-preto-profundo">
    <!-- TOP HEADER -->
    <header class="bg-grafite/90 border-b border-ciano-holografico/20 px-4 py-2 flex items-center justify-between z-30 backdrop-blur-sm">
        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" class="p-1 rounded text-branco-frio/70 hover:text-ciano-holografico">
                <x-icon name="arrow-left" class="size-5" />
                <span class="sr-only">Voltar para o início</span>
            </a>
            <a href="{{ route('appearance.edit') }}" class="p-1 rounded text-branco-frio/70 hover:text-ciano-holografico" aria-label="Aparência">
                <x-icon name="palette" class="size-5" />
            </a>
            <div>
                <h1 class="font-headline text-label-lg text-ciano-holografico tracking-wider uppercase leading-tight">ARENA DE DUELO</h1>
                <span class="text-[11px] text-branco-frio/60 font-body">Formato 2 Jogadores</span>
            </div>
        </div>
        <div class="flex items-center gap-1 bg-preto-profundo/80 px-2.5 py-1 rounded-full border border-vermelho-dano/40">
            <button wire:click="toggleOpponentRotation" title="Inverter tela do oponente para duelo de mesa (180°)" class="w-8 h-8 rounded bg-grafite hover:bg-azul-tecnologico/30 border border-branco-frio/20 text-ciano-holografico flex items-center justify-center transition active:scale-95">
                <x-icon name="rotate" class="size-5" />
            </button>
            <button onclick="openDiceModal()" title="Abrir dados e moedas" class="w-8 h-8 rounded bg-grafite hover:bg-azul-tecnologico/30 border border-branco-frio/20 text-branco-frio flex items-center justify-center transition active:scale-95">
                <x-icon name="dice" class="size-5" />
            </button>
        </div>
    </header>

    <main class="flex-1 flex flex-col justify-between p-3 pb-20 gap-3 relative overflow-hidden">
        <!-- PLAYER 2 (OPPONENT) POD -->
        <section id="player-2-pod" class="relative bg-grafite/90 border border-ciano-holografico/25 rounded-xl p-4 transition-transform duration-300 shadow-[0_4px_25px_rgba(0,0,0,0.6)] flex flex-col justify-between overflow-hidden {{ $rotateOpponent ? 'rotate-180' : '' }}">
            <div class="flex items-center justify-between pb-2 border-b border-branco-frio/10">
                <button wire:click="editPlayerIdentity({{ $topPlayer->id }})" class="font-headline text-sm font-bold text-ciano-holografico uppercase hover:underline">{{ $topPlayer->name }}</button>
                <span class="text-[10px] text-branco-frio/40 font-headline">P2</span>
            </div>
            <div class="py-2 text-center">
                <span class="font-display text-5xl font-extrabold text-branco-frio tabular-nums block drop-shadow-[0_0_12px_rgba(255,255,255,0.15)]">{{ $topPlayer->life_points }}</span>
                <span class="text-[10px] font-headline text-ciano-holografico uppercase tracking-wider">Pontos de Vida</span>
            </div>
            <div class="grid grid-cols-4 gap-1.5 pt-2 border-t border-branco-frio/10">
                <button wire:click="subtract({{ $topPlayer->id }}, 1000)" class="py-1.5 bg-vermelho-dano/15 hover:bg-vermelho-dano/25 text-vermelho-dano border border-vermelho-dano/40 rounded text-[11px] font-headline font-bold">−1000</button>
                <button wire:click="subtract({{ $topPlayer->id }}, 500)" class="py-1.5 bg-vermelho-dano/15 hover:bg-vermelho-dano/25 text-vermelho-dano border border-vermelho-dano/40 rounded text-[11px] font-headline font-bold">−500</button>
                <button wire:click="add({{ $topPlayer->id }}, 500)" class="py-1.5 bg-verde-recuperacao/15 hover:bg-verde-recuperacao/25 text-verde-recuperacao border border-verde-recuperacao/40 rounded text-[11px] font-headline font-bold">+500</button>
                <button wire:click="add({{ $topPlayer->id }}, 1000)" class="py-1.5 bg-verde-recuperacao/15 hover:bg-verde-recuperacao/25 text-verde-recuperacao border border-verde-recuperacao/40 rounded text-[11px] font-headline font-bold">+1000</button>
            </div>
            <div class="grid grid-cols-4 gap-1.5 pt-1.5">
                <button wire:click="halve({{ $topPlayer->id }})" class="py-1 rounded bg-preto-profundo/70 border border-branco-frio/15 hover:border-ciano-holografico text-branco-frio/80 text-[10px] font-headline">÷2</button>
                <button wire:click="doubleLifePoints({{ $topPlayer->id }})" class="py-1 rounded bg-preto-profundo/70 border border-branco-frio/15 hover:border-ciano-holografico text-branco-frio/80 text-[10px] font-headline">×2</button>
                <button wire:click="openLifePointCalculator({{ $topPlayer->id }})" class="py-1 rounded bg-preto-profundo/70 border border-branco-frio/15 hover:border-ciano-holografico text-branco-frio/80 text-[10px] font-headline">Calc</button>
                <button wire:click="restore({{ $topPlayer->id }})" class="py-1 rounded bg-preto-profundo/70 border border-branco-frio/15 hover:border-ciano-holografico text-branco-frio/80 text-[10px] font-headline">Rest.</button>
            </div>
        </section>

        <!-- MIDDLE ACTION BAR -->
        <section class="bg-preto-profundo/90 border border-ciano-holografico/30 rounded-lg px-3 py-2 flex items-center justify-between shadow-[0_0_15px_rgba(23,107,206,0.15)]">
            <span class="font-headline text-sm text-ciano-holografico tabular-nums">{{ $duel->formattedTimerRemaining() }}</span>
            <span class="text-[10px] font-headline text-branco-frio/50 uppercase">TURNO {{ str_pad((string) $duel->turn, 2, '0', STR_PAD_LEFT) }}</span>
            <span class="text-[10px] font-headline text-ciano-holografico">{{ $duel->turnOwnerLabel() }}</span>
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

        @if ($firstPlayerDrawn)
            <p role="status" class="rounded border border-dourado-destaque/30 bg-dourado-destaque/10 px-3 py-2 font-mono text-label-sm text-dourado-destaque">
                <span class="font-bold">{{ $firstPlayerDrawn }}</span> começa. O sorteio é apenas um auxílio; sigam o formato aplicável.
            </p>
        @endif

        <!-- PLAYER 1 (HOME) POD -->
        <section id="player-1-pod" class="relative bg-grafite/90 border border-ciano-holografico/40 rounded-xl p-4 shadow-[0_4px_30px_rgba(23,107,206,0.3)] flex flex-col justify-between overflow-hidden">
            <div class="flex items-center justify-between pb-2 border-b border-branco-frio/10">
                <button wire:click="editPlayerIdentity({{ $bottomPlayer->id }})" class="font-headline text-sm font-bold text-ciano-holografico uppercase hover:underline">{{ $bottomPlayer->name }}</button>
                <span class="text-[10px] text-branco-frio/40 font-headline">P1</span>
            </div>
            <div class="py-2 text-center">
                <span class="font-display text-5xl font-extrabold text-branco-frio tabular-nums block drop-shadow-[0_0_12px_rgba(53,216,255,0.4)]">{{ $bottomPlayer->life_points }}</span>
                <span class="text-[10px] font-headline text-ciano-holografico uppercase tracking-wider">Pontos de Vida</span>
            </div>
            <div class="grid grid-cols-4 gap-1.5 pt-2 border-t border-branco-frio/10">
                <button wire:click="subtract({{ $bottomPlayer->id }}, 1000)" class="py-1.5 bg-vermelho-dano/15 hover:bg-vermelho-dano/25 text-vermelho-dano border border-vermelho-dano/40 rounded text-[11px] font-headline font-bold">−1000</button>
                <button wire:click="subtract({{ $bottomPlayer->id }}, 500)" class="py-1.5 bg-vermelho-dano/15 hover:bg-vermelho-dano/25 text-vermelho-dano border border-vermelho-dano/40 rounded text-[11px] font-headline font-bold">−500</button>
                <button wire:click="add({{ $bottomPlayer->id }}, 500)" class="py-1.5 bg-verde-recuperacao/15 hover:bg-verde-recuperacao/25 text-verde-recuperacao border border-verde-recuperacao/40 rounded text-[11px] font-headline font-bold">+500</button>
                <button wire:click="add({{ $bottomPlayer->id }}, 1000)" class="py-1.5 bg-verde-recuperacao/15 hover:bg-verde-recuperacao/25 text-verde-recuperacao border border-verde-recuperacao/40 rounded text-[11px] font-headline font-bold">+1000</button>
            </div>
            <div class="grid grid-cols-4 gap-1.5 pt-1.5">
                <button wire:click="halve({{ $bottomPlayer->id }})" class="py-1 rounded bg-preto-profundo/70 border border-branco-frio/15 hover:border-ciano-holografico text-branco-frio/80 text-[10px] font-headline">÷2</button>
                <button wire:click="doubleLifePoints({{ $bottomPlayer->id }})" class="py-1 rounded bg-preto-profundo/70 border border-branco-frio/15 hover:border-ciano-holografico text-branco-frio/80 text-[10px] font-headline">×2</button>
                <button wire:click="openLifePointCalculator({{ $bottomPlayer->id }})" class="py-1 rounded bg-preto-profundo/70 border border-branco-frio/15 hover:border-ciano-holografico text-branco-frio/80 text-[10px] font-headline">Calc</button>
                <button wire:click="restore({{ $bottomPlayer->id }})" class="py-1 rounded bg-preto-profundo/70 border border-branco-frio/15 hover:border-ciano-holografico text-branco-frio/80 text-[10px] font-headline">Rest.</button>
            </div>
        </section>

        <!-- TOOLS ROW -->
        <div class="flex items-center justify-between gap-2">
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
                Vibração {{ $hapticFeedbackEnabled ? 'On' : 'Off' }}
            </button>
        </div>
    </main>

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

    <!-- COUNTERS MODAL -->
    @include('livewire.partials.counters-modal')

    <!-- EXISTING FETCH-BASED TOOL MODALS (dice/coin/reminders) -->
    @include('livewire.partials.tools-modals')
</div>
