<section class="hud-panel chamfer flex flex-col gap-2 px-2 py-2">
    <div class="flex items-center justify-between gap-2 px-1">
        <div class="flex min-w-0 items-center gap-1.5">
            <span class="size-2 shrink-0 rounded-full bg-secondary shadow-[0_0_6px_#edc157]"></span>
            <span class="whitespace-nowrap font-mono text-label-md font-bold uppercase tracking-wider text-secondary">{{ $duel->turnLabel() }}</span>
            <span class="truncate font-mono text-label-sm text-on-surface-variant">• {{ $turnOwner }}</span>
        </div>

        <div class="flex shrink-0 items-center gap-1">
            <button
                type="button"
                wire:click="advanceTurn"
                class="flex size-9 items-center justify-center rounded bg-secondary/15 text-secondary transition hover:bg-secondary/25 active:scale-90"
                aria-label="Avançar turno"
            >
                <x-icon name="chevron-right" class="size-4" />
            </button>
            <button
                type="button"
                wire:click="undo"
                @disabled(! $canUndo)
                class="flex size-9 items-center justify-center rounded bg-surface-container text-on-surface-variant transition hover:text-primary active:scale-90 disabled:opacity-30 disabled:active:scale-100"
                aria-label="Desfazer última alteração"
            >
                <x-icon name="undo" class="size-4" />
            </button>
            <button
                type="button"
                wire:click="redo"
                @disabled(! $canRedo)
                class="flex size-9 items-center justify-center rounded bg-surface-container text-on-surface-variant transition hover:text-primary active:scale-90 disabled:opacity-30 disabled:active:scale-100"
                aria-label="Refazer alteração desfeita"
            >
                <x-icon name="redo" class="size-4" />
            </button>
        </div>
    </div>

    <div class="grid grid-cols-6 gap-1 rounded-lg bg-surface-container-lowest/90 p-1">
        @foreach ($phases as $phase)
            <button
                type="button"
                wire:click="setPhase('{{ $phase->value }}')"
                @class([
                    'flex flex-col items-center justify-center rounded py-1.5 transition',
                    'bg-secondary font-bold text-on-secondary shadow-[0_0_12px_rgba(237,193,87,0.5)]' => $currentPhase === $phase,
                    'bg-surface-container text-on-surface-variant hover:bg-surface-bright' => $currentPhase !== $phase,
                ])
                aria-pressed="{{ $currentPhase === $phase ? 'true' : 'false' }}"
                aria-label="Fase {{ $phase->label() }}"
            >
                <span class="font-mono text-label-sm font-bold">{{ $phase->value }}</span>
                <span class="text-[9px] uppercase tracking-tighter opacity-70">{{ $phase->label() }}</span>
            </button>
        @endforeach
    </div>

    <div class="flex items-center justify-between gap-2 px-1">
        <p class="min-w-0 truncate font-mono text-label-sm text-on-surface-variant">
            Ação recente:
            @if ($lastEvent)
                <span class="text-on-surface">{{ $lastEvent->duelPlayer->name }}</span>
                <span @class([
                    'font-bold',
                    'text-damage' => $lastEvent->signedAmount() < 0,
                    'text-heal' => $lastEvent->signedAmount() > 0,
                ])>
                    {{ $lastEvent->signedAmount() > 0 ? '+' : '' }}{{ $lastEvent->signedAmount() }}
                </span>
            @else
                <span>Nenhuma alteração</span>
            @endif
        </p>
        <span class="shrink-0 font-mono text-label-sm text-primary-fixed-dim">Duelo #{{ $duel->id }} · {{ $players->count() }}P</span>
    </div>
</section>
