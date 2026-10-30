<div class="relative min-h-dvh">
    <div class="tactical-grid pointer-events-none fixed inset-0 opacity-15"></div>

    <header class="pt-safe sticky top-0 z-30 border-b border-white/5 bg-surface-container-lowest/85 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-2xl items-center gap-2 px-4">
            <span class="flex size-11 items-center justify-center rounded bg-primary-container/15 text-primary-fixed-dim">
                <x-icon name="shield" />
            </span>
            <div class="min-w-0">
                <p class="font-mono text-label-sm uppercase tracking-widest text-primary-fixed-dim">Configuração Tática</p>
                <h1 class="truncate font-sans text-headline-sm font-semibold text-on-surface">Configurações e Lobby Tático</h1>
            </div>
        </div>
    </header>

    <main class="relative mx-auto w-full max-w-2xl px-4 pb-10 pt-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <span class="font-mono text-label-sm uppercase tracking-widest text-primary">Tactical Lobby • Multiplayer Setup</span>
            <span class="flex items-center gap-1.5 rounded bg-surface-container-high px-2 py-1 font-mono text-label-sm text-secondary">
                <x-icon name="offline" class="size-3.5" />
                Offline-First
            </span>
        </div>

        <p class="mt-2 flex items-center gap-1.5 font-mono text-label-sm text-on-surface-variant">
            <x-icon name="users" class="size-4 text-primary-fixed-dim" />
            1 a 8 duelistas na mesma mesa
        </p>

        <section class="mt-6">
            <div class="flex items-end justify-between gap-2">
                <h2 class="font-mono text-label-md uppercase tracking-wider text-on-surface">Capacidade da Arena</h2>
                <span class="font-mono text-label-sm text-primary-fixed-dim">
                    {{ count($playerNames) }} {{ count($playerNames) === 1 ? 'JOGADOR SELECIONADO' : 'JOGADORES SELECIONADOS' }}
                </span>
            </div>

            <div class="mt-2 grid grid-cols-4 gap-1.5 sm:grid-cols-8">
                @foreach (\App\Livewire\DuelSetup::CAPACITY_LABELS as $count => $label)
                    <button
                        type="button"
                        wire:click="setPlayerCount({{ $count }})"
                        @class([
                            'chamfer flex flex-col items-center justify-center gap-0.5 border px-1 py-2 transition',
                            'border-primary-container bg-primary-container/10 text-primary-container' => count($playerNames) === $count,
                            'border-white/10 bg-surface-container-low text-on-surface-variant hover:border-primary-container/40 hover:text-primary' => count($playerNames) !== $count,
                        ])
                        aria-label="{{ $count }} jogadores ({{ $label }})"
                    >
                        <span class="font-mono text-label-lg font-bold">{{ $count }}</span>
                        <span class="font-mono text-label-sm uppercase">{{ $label }}</span>
                    </button>
                @endforeach
            </div>
        </section>

        <section class="mt-6">
            <h2 class="font-mono text-label-md uppercase tracking-wider text-on-surface">Pontos de Vida Iniciais (PV / LP)</h2>

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
                            'chamfer border p-3 text-left transition',
                            'border-primary-container bg-primary-container/10' => $startingLifePoints === $preset['value'],
                            'border-white/10 bg-surface-container-low hover:border-primary-container/40' => $startingLifePoints !== $preset['value'],
                        ])
                    >
                        <span class="flex items-center justify-between gap-1 font-mono text-label-sm uppercase tracking-wider text-on-surface-variant">
                            {{ $preset['label'] }}
                            @if ($startingLifePoints === $preset['value'])
                                <x-icon name="check" class="size-4 text-primary-container" />
                            @endif
                        </span>
                        <span class="mt-1 block font-mono text-headline-sm font-bold text-primary">{{ number_format($preset['value'], 0, ',', '.') }} LP</span>
                        <span class="font-sans text-body-sm text-on-surface-variant">{{ $preset['hint'] }}</span>
                    </button>
                @endforeach

                <label
                    @class([
                        'chamfer border p-3 transition',
                        'border-primary-container bg-primary-container/10' => ! in_array($startingLifePoints, [8000, 4000, 16000], true),
                        'border-white/10 bg-surface-container-low' => in_array($startingLifePoints, [8000, 4000, 16000], true),
                    ])
                >
                    <span class="flex items-center justify-between gap-1 font-mono text-label-sm uppercase tracking-wider text-on-surface-variant">
                        Personalizado
                        @if (! in_array($startingLifePoints, [8000, 4000, 16000], true))
                            <x-icon name="check" class="size-4 text-primary-container" />
                        @endif
                    </span>
                    <span class="mt-1 flex items-baseline gap-1">
                        <input
                            type="number"
                            min="1"
                            max="999999"
                            inputmode="numeric"
                            wire:model="startingLifePoints"
                            class="w-24 border-0 bg-transparent p-0 font-mono text-headline-sm font-bold text-primary focus:outline-none"
                            aria-label="Pontos de vida iniciais personalizados"
                        >
                        <span class="font-mono text-label-sm text-on-surface-variant">LP</span>
                    </span>
                    <span class="font-sans text-body-sm text-on-surface-variant">Boss Battle / Custom</span>
                </label>
            </div>

            @error('startingLifePoints')
                <p class="mt-2 font-mono text-label-sm text-damage">{{ $message }}</p>
            @enderror
        </section>

        <section class="mt-6">
            <h2 class="font-mono text-label-md uppercase tracking-wider text-on-surface">Duelistas</h2>

            <div class="mt-2 flex flex-col gap-2">
                @foreach ($playerNames as $index => $name)
                    <div class="flex items-center gap-2" wire:key="player-slot-{{ $index }}">
                        <span
                            class="flex size-10 shrink-0 items-center justify-center rounded bg-surface-container-high font-mono text-label-md font-bold"
                            style="color: {{ \App\Models\Duel::SEAT_COLORS[$index] ?? '#00f2fe' }}"
                        >
                            P{{ $index + 1 }}
                        </span>

                        <input
                            type="text"
                            maxlength="40"
                            wire:model="playerNames.{{ $index }}"
                            placeholder="Jogador {{ $index + 1 }}"
                            class="h-12 min-w-0 flex-1 rounded border border-white/10 bg-surface-container-lowest px-3 font-sans text-body-md text-on-surface placeholder:text-on-surface-variant/60 focus:border-primary-container focus:outline-none"
                            aria-label="Nome do jogador {{ $index + 1 }}"
                        >

                        <button
                            type="button"
                            wire:click="removePlayer({{ $index }})"
                            @disabled(count($playerNames) === 1)
                            class="flex size-12 shrink-0 items-center justify-center rounded text-on-surface-variant transition-colors hover:text-damage disabled:opacity-30"
                            aria-label="Remover jogador {{ $index + 1 }}"
                        >
                            <x-icon name="x" />
                        </button>
                    </div>
                @endforeach

                @error('playerNames')
                    <p class="font-mono text-label-sm text-damage">{{ $message }}</p>
                @enderror
                @error('playerNames.*')
                    <p class="font-mono text-label-sm text-damage">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="button"
                wire:click="addPlayer"
                @disabled(count($playerNames) >= \App\Models\Duel::MAX_PLAYERS)
                class="mt-2 flex h-12 w-full items-center justify-center gap-1.5 rounded border border-dashed border-white/15 font-mono text-label-md text-primary transition-colors hover:border-primary-container/60 disabled:opacity-30"
            >
                <x-icon name="plus" class="size-4" />
                Adicionar Duelista (Máx 8)
            </button>
        </section>

        <button
            type="button"
            wire:click="start"
            class="shadow-glow mt-8 flex h-14 w-full items-center justify-center gap-2 rounded bg-gradient-to-br from-primary-container to-[#4facfe] font-mono text-headline-sm font-bold uppercase tracking-wide text-on-primary transition active:scale-[0.99]"
        >
            <x-icon name="play" class="size-5" />
            Iniciar Arena de Duelo
        </button>
    </main>
</div>
