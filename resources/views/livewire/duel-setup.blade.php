<div class="relative min-h-dvh">
    <div class="tactical-grid pointer-events-none fixed inset-0 opacity-15"></div>

    <header class="pt-safe sticky top-0 z-30 border-b border-white/5 bg-preto-profundo/85 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-2xl items-center gap-2 px-4">
            <span class="flex size-11 items-center justify-center rounded bg-azul-tecnologico/20 text-ciano-holografico-fixed-dim">
                <x-icon name="shield" />
            </span>
            <div class="min-w-0">
                <p class="font-headline text-label-sm uppercase tracking-widest text-ciano-holografico-fixed-dim">Configuração Tática</p>
                <h1 class="truncate font-body text-headline-sm font-semibold text-branco-frio">Configurações e Lobby Tático</h1>
            </div>
        </div>
    </header>

    <main class="relative mx-auto w-full max-w-2xl px-4 pb-10 pt-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <span class="font-headline text-label-sm uppercase tracking-widest text-ciano-holografico">Tactical Lobby • Multiplayer Setup</span>
            <span class="hidden items-center gap-1.5 rounded bg-preto-profundo/80 border border-branco-frio/15 px-2 py-1 font-headline text-[10px] text-branco-frio/60 uppercase sm:flex whitespace-nowrap">
                <x-icon name="offline" class="size-3.5 text-ciano-holografico" />
                Offline-First
            </span>
        </div>

        <p class="mt-2 flex items-center gap-1.5 font-headline text-label-sm text-branco-frio-variant">
            <x-icon name="users" class="size-4 text-ciano-holografico-fixed-dim" />
            1 a 8 duelistas na mesma mesa
        </p>

        <section class="mt-6">
            <div class="flex items-end justify-between gap-2">
                <h2 class="font-headline text-label-md uppercase tracking-wider text-branco-frio">Capacidade da Arena</h2>
                <span class="font-headline text-label-sm text-ciano-holografico-fixed-dim">
                    {{ count($playerNames) }} {{ count($playerNames) === 1 ? 'JOGADOR SELECIONADO' : 'JOGADORES SELECIONADOS' }}
                </span>
            </div>

            <div class="mt-2 grid grid-cols-4 gap-1.5 sm:grid-cols-8">
                @foreach (\App\Livewire\DuelSetup::CAPACITY_LABELS as $count => $label)
                    <button
                        type="button"
                        wire:click="setPlayerCount({{ $count }})"
                        @class([
                            'rounded-lg flex flex-col items-center justify-center gap-0.5 border px-1 py-2 transition',
                            'border-ciano-holografico bg-azul-tecnologico/20 text-ciano-holografico-container' => count($playerNames) === $count,
                            'border-branco-frio/15 bg-grafite/80 text-branco-frio-variant hover:border-ciano-holografico/40 hover:text-ciano-holografico' => count($playerNames) !== $count,
                        ])
                        aria-label="{{ $count }} jogadores ({{ $label }})"
                    >
                        <span class="font-headline text-label-lg font-bold">{{ $count }}</span>
                        <span class="font-headline text-label-sm uppercase">{{ $label }}</span>
                    </button>
                @endforeach
            </div>
        </section>

        <section class="mt-6">
            <h2 class="font-headline text-label-md uppercase tracking-wider text-branco-frio">Pontos de Vida Iniciais (PV / LP)</h2>

            <div class="mt-2 grid grid-cols-2 gap-2">
                @foreach ([
                    ['value' => 8000, 'label' => 'Padrão TCG', 'hint' => 'Match Oficial & Avançado'],
                    ['value' => 4000, 'label' => 'Speed Duel', 'hint' => 'Anime / Duel Links'],
                    ['value' => 16000, 'label' => 'Tag Team', 'hint' => '2v2 Compartilhado'],
                ] as $preset)
                    <button
                        type="button"
                        wire:click="$set('startingLifePoints', {{ $preset['value'] }})"
                        @class([
                            'rounded-lg border p-3 text-left transition',
                            'border-ciano-holografico bg-azul-tecnologico/20' => $startingLifePoints === $preset['value'],
                            'border-branco-frio/15 bg-grafite/80 hover:border-ciano-holografico/40' => $startingLifePoints !== $preset['value'],
                        ])
                    >
                        <span class="flex items-center justify-between gap-1 font-headline text-label-sm uppercase tracking-wider text-branco-frio-variant">
                            {{ $preset['label'] }}
                            @if ($startingLifePoints === $preset['value'])
                                <x-icon name="check" class="size-4 text-ciano-holografico-container" />
                            @endif
                        </span>
                        <span class="mt-1 block font-headline text-headline-sm font-bold text-ciano-holografico">{{ number_format($preset['value'], 0, ',', '.') }} LP</span>
                        <span class="font-body text-body-sm text-branco-frio-variant">{{ $preset['hint'] }}</span>
                    </button>
                @endforeach

                <label
                    @class([
                        'rounded-lg border p-3 transition',
                        'border-ciano-holografico bg-azul-tecnologico/20' => ! in_array($startingLifePoints, [8000, 4000, 16000], true),
                        'border-branco-frio/15 bg-grafite/80' => in_array($startingLifePoints, [8000, 4000, 16000], true),
                    ])
                >
                    <span class="flex items-center justify-between gap-1 font-headline text-label-sm uppercase tracking-wider text-branco-frio-variant">
                        Personalizado
                        @if (! in_array($startingLifePoints, [8000, 4000, 16000], true))
                            <x-icon name="check" class="size-4 text-ciano-holografico-container" />
                        @endif
                    </span>
                    <span class="mt-1 flex items-baseline gap-1">
                        <input
                            type="number"
                            min="1"
                            max="999999"
                            inputmode="numeric"
                            wire:model="startingLifePoints"
                            class="w-24 border-0 bg-transparent p-0 font-headline text-headline-sm font-bold text-ciano-holografico focus:outline-none"
                            aria-label="Pontos de vida iniciais personalizados"
                        >
                        <span class="font-headline text-label-sm text-branco-frio-variant">LP</span>
                    </span>
                    <span class="font-body text-body-sm text-branco-frio-variant">Boss Battle / Custom</span>
                </label>
            </div>

            @error('startingLifePoints')
                <p class="mt-2 font-headline text-label-sm text-vermelho-dano">{{ $message }}</p>
            @enderror
        </section>

        <section class="mt-6">
            <h2 class="font-headline text-label-md uppercase tracking-wider text-branco-frio">Duelistas</h2>

            <div class="mt-2 flex flex-col gap-2">
                @foreach ($playerNames as $index => $name)
                    <div class="rounded border border-branco-frio/15 bg-grafite/80 p-3" wire:key="player-slot-{{ $index }}">
                        <div class="flex items-center gap-2">
                            <span
                                class="flex size-10 shrink-0 items-center justify-center rounded bg-grafite font-headline text-label-md font-bold text-ciano-holografico-container"
                            >
                                P{{ $index + 1 }}
                            </span>

                            <input
                                type="text"
                                maxlength="40"
                                wire:model="playerNames.{{ $index }}"
                                placeholder="Jogador {{ $index + 1 }}"
                                class="h-12 min-w-0 flex-1 rounded border border-branco-frio/15 bg-preto-profundo px-3 font-body text-body-md text-branco-frio placeholder:text-branco-frio-variant/60 focus:border-ciano-holografico focus:outline-none"
                                aria-label="Nome do jogador {{ $index + 1 }}"
                            >

                            <button
                                type="button"
                                wire:click="removePlayer({{ $index }})"
                                @disabled(count($playerNames) === 1)
                                class="flex size-12 shrink-0 items-center justify-center rounded text-branco-frio-variant transition-colors hover:text-vermelho-dano disabled:opacity-30"
                                aria-label="Remover jogador {{ $index + 1 }}"
                            >
                                <x-icon name="x" />
                            </button>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                            <div role="group" aria-label="Cor de identificação do jogador {{ $index + 1 }}" class="flex flex-wrap gap-2">
                                @foreach (\App\Models\Duel::PLAYER_ACCENTS as $accent => $label)
                                    <button
                                        type="button"
                                        wire:click="$set('playerColors.{{ $index }}', '{{ $accent }}')"
                                        aria-label="Cor {{ $label }}"
                                        aria-pressed="{{ ($playerColors[$index] ?? '') === $accent ? 'true' : 'false' }}"
                                        @class([
                                            'size-8 rounded-full border-2 transition',
                                            'border-on-surface ring-2 ring-primary/40' => ($playerColors[$index] ?? '') === $accent,
                                            'border-transparent' => ($playerColors[$index] ?? '') !== $accent,
                                        ])
                                        style="background-color: var(--player-accent-{{ $accent }})"
                                    ></button>
                                @endforeach
                            </div>

                            <label class="flex min-w-40 flex-1 items-center gap-2 font-headline text-label-sm text-branco-frio-variant sm:flex-none">
                                Ícone
                                <select
                                    wire:model="playerIcons.{{ $index }}"
                                    aria-label="Ícone do jogador {{ $index + 1 }}"
                                    class="h-10 min-w-0 flex-1 rounded border border-branco-frio/15 bg-preto-profundo px-2 text-body-sm text-branco-frio focus:border-ciano-holografico focus:outline-none sm:w-40"
                                >
                                    <option value="">Padrão da arena</option>
                                    @foreach (\App\Models\Duel::PLAYER_ICONS as $icon => $label)
                                        <option value="{{ $icon }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        @error('playerColors.'.$index)
                            <p class="mt-2 font-headline text-label-sm text-vermelho-dano">{{ $message }}</p>
                        @enderror
                        @error('playerIcons.'.$index)
                            <p class="mt-2 font-headline text-label-sm text-vermelho-dano">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                @error('playerNames')
                    <p class="font-headline text-label-sm text-vermelho-dano">{{ $message }}</p>
                @enderror
                @error('playerNames.*')
                    <p class="font-headline text-label-sm text-vermelho-dano">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="button"
                wire:click="addPlayer"
                @disabled(count($playerNames) >= \App\Models\Duel::MAX_PLAYERS)
                class="mt-2 flex h-12 w-full items-center justify-center gap-1.5 rounded border border-dashed border-white/15 font-headline text-label-md text-ciano-holografico transition-colors hover:border-ciano-holografico/60 disabled:opacity-30"
            >
                <x-icon name="plus" class="size-4" />
                Adicionar Duelista (Máx 8)
            </button>
        </section>

        <button
            type="button"
            wire:click="start"
            class="shadow-glow mt-8 flex h-14 w-full items-center justify-center gap-2 rounded bg-gradient-to-br from-primary-container to-[#4facfe] font-headline text-headline-sm font-bold uppercase tracking-wide text-branco-frio transition active:scale-[0.99]"
        >
            <x-icon name="play" class="size-5" />
            Iniciar Arena de Duelo
        </button>
    </main>

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
</div>
