<div class="relative min-h-dvh">
    <div class="tactical-grid pointer-events-none fixed inset-0 opacity-20"></div>

    <header class="pt-safe sticky top-0 z-30 border-b border-white/5 bg-surface-container-lowest/85 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-3xl items-center justify-between gap-2 px-4">
            <div class="flex min-w-0 items-center gap-2">
                <a
                    href="{{ route('duel.setup') }}"
                    class="flex size-11 shrink-0 items-center justify-center rounded text-on-surface-variant transition-colors hover:text-primary"
                    aria-label="Voltar para a configuração"
                >
                    <x-icon name="arrow-left" />
                </a>
                <div class="flex items-center gap-1.5 rounded bg-surface-container-high/60 px-2 py-1">
                    <x-icon name="shield" class="size-4 text-primary-fixed-dim" />
                    <span class="font-mono text-label-md uppercase tracking-wider text-primary-fixed">Duelo #{{ $duel->id }}</span>
                </div>
            </div>

            <div class="flex items-center gap-1.5 rounded bg-surface-container-low px-2 py-1">
                <x-icon name="users" class="size-4 text-secondary" />
                <span class="font-mono text-label-md text-secondary">{{ $players->count() }}</span>
            </div>
        </div>
    </header>

    <main class="relative mx-auto flex w-full max-w-3xl flex-col gap-3 px-4 pb-safe pt-4">
        <section class="hud-panel chamfer flex items-center justify-between gap-3 px-3 py-2">
            <div class="min-w-0">
                <p class="font-mono text-label-sm uppercase tracking-widest text-primary-fixed-dim">Ação recente</p>
                @if ($lastEvent)
                    <p class="truncate font-mono text-label-md text-on-surface">
                        {{ $lastEvent->duelPlayer->name }}
                        <span @class([
                            'font-bold',
                            'text-damage' => $lastEvent->signedAmount() < 0,
                            'text-heal' => $lastEvent->signedAmount() > 0,
                        ])>
                            {{ $lastEvent->signedAmount() > 0 ? '+' : '' }}{{ $lastEvent->signedAmount() }}
                        </span>
                    </p>
                @else
                    <p class="truncate font-mono text-label-md text-on-surface-variant">Nenhuma alteração</p>
                @endif
            </div>

            <div class="flex shrink-0 items-center gap-1">
                <button
                    type="button"
                    wire:click="undo"
                    @disabled(! $canUndo)
                    class="flex size-11 items-center justify-center rounded bg-surface-container text-on-surface-variant transition hover:text-primary active:scale-90 disabled:opacity-30 disabled:active:scale-100"
                    aria-label="Desfazer última alteração"
                >
                    <x-icon name="undo" />
                </button>
                <button
                    type="button"
                    wire:click="redo"
                    @disabled(! $canRedo)
                    class="flex size-11 items-center justify-center rounded bg-surface-container text-on-surface-variant transition hover:text-primary active:scale-90 disabled:opacity-30 disabled:active:scale-100"
                    aria-label="Refazer alteração desfeita"
                >
                    <x-icon name="redo" />
                </button>
            </div>
        </section>

        <div @class([
            'grid gap-3',
            'sm:grid-cols-2' => $players->count() > 2,
        ])>
            @foreach ($players as $player)
                <section
                    wire:key="arena-player-{{ $player->id }}"
                    @class([
                        'hud-panel chamfer p-3 transition-transform duration-500',
                        'rotate-180' => $rotateOpponent && $players->count() === 2 && $player->seat === 1,
                    ])
                >
                    <div
                        class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent to-transparent"
                        style="background-image: linear-gradient(to right, transparent, {{ $player->color }}, transparent)"
                    ></div>

                    <div class="flex items-start justify-between gap-2">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded bg-surface-container-high">
                                <x-icon name="swords" class="size-4" style="color: {{ $player->color }}" />
                            </span>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <h2 class="truncate font-sans text-headline-sm tracking-tight text-on-surface">{{ $player->name }}</h2>
                                    <span
                                        class="rounded px-1.5 py-0.5 font-mono text-label-sm uppercase tracking-wider"
                                        style="background-color: {{ $player->color }}29; color: {{ $player->color }}"
                                    >
                                        {{ $player->seatLabel() }}
                                    </span>
                                </div>
                                <p class="font-mono text-label-sm text-on-surface-variant">Iniciou com {{ $player->starting_life_points }} PV</p>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-1">
                            @if ($player->latestLifePointEvent && ! $player->latestLifePointEvent->undone_at)
                                <span @class([
                                    'rounded px-1.5 py-0.5 font-mono text-label-md font-bold',
                                    'bg-damage/15 text-damage' => $player->latestLifePointEvent->signedAmount() < 0,
                                    'bg-heal/15 text-heal' => $player->latestLifePointEvent->signedAmount() > 0,
                                ])>
                                    {{ $player->latestLifePointEvent->signedAmount() }}
                                </span>
                            @endif

                            @if ($players->count() === 2 && $player->seat === 1)
                                <button
                                    type="button"
                                    wire:click="toggleOpponentRotation"
                                    class="flex size-9 items-center justify-center rounded bg-surface-container-high text-on-surface-variant transition hover:text-primary"
                                    aria-label="Girar painel do oponente"
                                >
                                    <x-icon name="rotate" class="size-4" />
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="mt-2 rounded-lg bg-surface-container-lowest/80 p-3 text-center shadow-inner">
                        <div class="flex items-baseline justify-center gap-2">
                            <span class="text-glow font-mono text-headline-xl-mobile font-bold tracking-tight text-primary tabular-nums">
                                {{ $player->life_points }}
                            </span>
                            <span class="font-mono text-label-md font-semibold tracking-widest text-primary-fixed-dim">LP</span>
                        </div>

                        <div class="mx-auto mt-2 h-1.5 w-full max-w-xs overflow-hidden rounded-full bg-surface-container-high">
                            <div
                                class="h-full rounded-full transition-all duration-300"
                                style="width: {{ min(100, $player->remainingPercentage()) }}%; background-color: {{ $player->color }}; box-shadow: 0 0 8px {{ $player->color }}"
                            ></div>
                        </div>

                        <div class="mx-auto mt-1.5 flex max-w-xs items-center justify-between px-1 font-mono text-label-sm text-on-surface-variant">
                            <span>{{ $player->life_points }} / {{ $player->starting_life_points }}</span>
                            <span class="font-bold text-primary-fixed-dim">{{ $player->remainingPercentage() }}% RESTANTE</span>
                        </div>
                    </div>

                    <div class="mt-2 grid grid-cols-4 gap-1.5">
                        <button
                            type="button"
                            wire:click="subtract({{ $player->id }}, 1000)"
                            class="flex h-12 items-center justify-center rounded bg-surface-container font-mono text-headline-sm font-bold text-damage transition hover:bg-damage/15 active:scale-95"
                        >
                            -1000
                        </button>
                        <button
                            type="button"
                            wire:click="subtract({{ $player->id }}, 500)"
                            class="flex h-12 items-center justify-center rounded bg-surface-container font-mono text-headline-sm font-bold text-damage transition hover:bg-damage/15 active:scale-95"
                        >
                            -500
                        </button>
                        <button
                            type="button"
                            wire:click="subtract({{ $player->id }}, 100)"
                            class="flex h-12 items-center justify-center rounded bg-surface-container font-mono text-headline-sm font-bold text-damage transition hover:bg-damage/15 active:scale-95"
                        >
                            -100
                        </button>
                        <button
                            type="button"
                            wire:click="halve({{ $player->id }})"
                            class="flex h-12 items-center justify-center rounded bg-surface-container font-mono text-headline-sm font-bold text-secondary transition hover:bg-secondary/15 active:scale-95"
                        >
                            ÷2
                        </button>
                        <button
                            type="button"
                            wire:click="add({{ $player->id }}, 100)"
                            class="flex h-12 items-center justify-center rounded bg-surface-container font-mono text-headline-sm font-bold text-primary-fixed-dim transition hover:bg-primary-container/15 active:scale-95"
                        >
                            +100
                        </button>
                        <button
                            type="button"
                            wire:click="add({{ $player->id }}, 500)"
                            class="flex h-12 items-center justify-center rounded bg-surface-container font-mono text-headline-sm font-bold text-primary-fixed-dim transition hover:bg-primary-container/15 active:scale-95"
                        >
                            +500
                        </button>
                        <button
                            type="button"
                            wire:click="add({{ $player->id }}, 1000)"
                            class="flex h-12 items-center justify-center rounded bg-surface-container font-mono text-headline-sm font-bold text-primary-fixed-dim transition hover:bg-primary-container/15 active:scale-95"
                        >
                            +1000
                        </button>
                        <button
                            type="button"
                            wire:click="restore({{ $player->id }})"
                            class="flex h-12 items-center justify-center gap-1 rounded bg-surface-container-high font-mono text-label-md font-bold text-on-surface-variant transition hover:bg-surface-container-highest active:scale-95"
                            aria-label="Restaurar os pontos de vida iniciais"
                        >
                            <x-icon name="restart" class="size-4" />
                            {{ $player->starting_life_points }}
                        </button>
                    </div>
                </section>
            @endforeach
        </div>
    </main>
</div>
