<div class="relative min-h-dvh">
    <div class="tactical-grid pointer-events-none fixed inset-0 opacity-20"></div>

    <header class="pt-safe sticky top-0 z-30 border-b border-white/5 bg-surface-container-lowest/85 backdrop-blur-xl">
        <div class="mx-auto flex h-16 max-w-3xl items-center justify-between gap-2 px-3">
            <a
                href="{{ route('home') }}"
                class="flex size-11 shrink-0 items-center justify-center rounded text-on-surface-variant transition-colors hover:text-primary"
                aria-label="Voltar para o início"
            >
                <x-icon name="arrow-left" />
            </a>

            <div class="flex min-w-0 items-center gap-1.5">
                <div class="flex items-center gap-1 rounded bg-surface-container-high/60 px-2 py-1">
                    <x-icon name="shield" class="size-4 text-primary-fixed-dim" />
                    <span class="whitespace-nowrap font-mono text-label-md tracking-wider text-primary-fixed">{{ $duel->positionLabel() }}</span>
                </div>

                <div
                    wire:ignore
                    x-data="{
                        remaining: {{ $timerRemaining }},
                        running: @js($timerRunning),
                        get display() {
                            const minutes = String(Math.floor(this.remaining / 60)).padStart(2, '0');
                            const seconds = String(this.remaining % 60).padStart(2, '0');

                            return minutes + ':' + seconds;
                        },
                        toggle() {
                            if (! this.running && this.remaining === 0) {
                                return;
                            }

                            this.running = ! this.running;
                            $wire.toggleTimer();
                        },
                        reset() {
                            this.remaining = {{ \App\Models\Duel::MATCH_TIME_SECONDS }};
                            this.running = false;
                            $wire.resetTimer();
                        },
                        tick() {
                            if (! this.running) {
                                return;
                            }

                            this.remaining = Math.max(0, this.remaining - 1);

                            if (this.remaining === 0) {
                                this.running = false;
                                $wire.pauseTimer();
                            }
                        },
                    }"
                    x-init="setInterval(() => tick(), 1000)"
                    class="flex items-center gap-1 rounded bg-surface-container-low px-1.5 py-1"
                >
                    <button
                        type="button"
                        @click="toggle()"
                        :aria-label="running ? 'Pausar cronômetro' : 'Iniciar cronômetro'"
                        class="flex items-center gap-1"
                    >
                        <span class="size-1.5 rounded-full" :class="running ? 'animate-pulse bg-primary-container' : 'bg-outline'"></span>
                        <x-icon name="timer" class="size-4 text-secondary" />
                        <span class="font-mono text-label-md text-secondary tabular-nums" x-text="display">{{ $duel->formattedTimerRemaining() }}</span>
                    </button>
                    <button
                        type="button"
                        @click="reset()"
                        class="flex size-6 items-center justify-center rounded text-on-surface-variant transition-colors hover:text-primary"
                        aria-label="Reiniciar cronômetro"
                    >
                        <x-icon name="restart" class="size-3.5" />
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-1">
                <div class="flex items-center gap-1 rounded bg-surface-container-low px-2 py-1">
                    <x-icon name="users" class="size-4 text-secondary" />
                    <span class="font-mono text-label-md text-secondary">{{ $players->count() }}</span>
                </div>
                <a
                    href="{{ route('appearance.edit') }}"
                    class="flex size-9 items-center justify-center rounded text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-primary"
                    aria-label="Abrir aparência"
                >
                    <x-icon name="settings" class="size-4" />
                </a>
            </div>
        </div>
    </header>

    <main class="relative mx-auto flex w-full max-w-3xl flex-col gap-3 px-4 pb-safe pt-4">
        @if ($players->count() !== 2)
            @include('livewire.partials.phase-ribbon')
        @endif

        @error('amount')
            <p role="alert" class="rounded border border-damage/30 bg-damage/10 px-3 py-2 font-mono text-label-sm text-damage">
                {{ $message }}
            </p>
        @enderror
        <div class="flex items-center justify-between gap-2">
            <livewire:duel-dice :duel="$duel" :key="'duel-dice-'.$duel->id" />
            <button
                type="button"
                wire:click="toggleHapticFeedback"
                aria-pressed="{{ $hapticFeedbackEnabled ? 'true' : 'false' }}"
                class="min-h-11 rounded border border-white/10 bg-surface-container-low px-3 font-mono text-label-sm text-on-surface-variant transition hover:bg-surface-container-high"
            >
                Vibração {{ $hapticFeedbackEnabled ? 'ativada' : 'desativada' }}
            </button>
        </div>
        @error('hapticFeedback')
            <p role="alert" class="rounded border border-damage/30 bg-damage/10 px-3 py-2 font-mono text-label-sm text-damage">
                {{ $message }}
            </p>
        @enderror
        @error('layout')
            <p role="alert" class="rounded border border-damage/30 bg-damage/10 px-3 py-2 font-mono text-label-sm text-damage">
                {{ $message }}
            </p>
        @enderror

        <div @class([
            'grid gap-3',
            'sm:grid-cols-2' => $players->count() > 2,
        ])>
            @foreach ($players as $player)
                <section
                    wire:key="arena-player-{{ $player->id }}"
                    x-data="{
                        feedback: '',
                        feedbackTimer: null,
                        showFeedback(event) {
                            if (Number(event.detail.playerId) !== {{ $player->id }}) {
                                return;
                            }

                            this.feedback = event.detail.kind;
                            clearTimeout(this.feedbackTimer);
                            this.feedbackTimer = setTimeout(() => this.feedback = '', 550);
                        },
                    }"
                    x-on:life-points-changed.window="showFeedback($event)"
                    @class([
                        'hud-panel chamfer p-3 transition-all duration-300',
                        'rotate-180' => $rotateOpponent && $players->count() === 2 && $player->seat === 1,
                        'col-span-full min-h-[65dvh] flex flex-col justify-center shadow-glow' => $expandedPlayerId === $player->id,
                    ])
                >
                    <div
                        class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-transparent to-transparent"
                        style="background-image: linear-gradient(to right, transparent, var(--player-accent-{{ $player->accentToken() }}), transparent)"
                    ></div>

                    <div class="flex items-start justify-between gap-2">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded bg-surface-container-high">
                                <x-icon :name="$player->iconName()" class="size-4" style="color: var(--player-accent-{{ $player->accentToken() }})" />
                            </span>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <h2 class="truncate font-sans text-headline-sm tracking-tight text-on-surface">{{ $player->name }}</h2>
                                    <span @class([
                                        'rounded bg-surface-container-high px-1.5 py-0.5 font-mono text-label-sm uppercase tracking-wider',
                                        'text-primary-container' => $player->seat === 0,
                                        'text-secondary' => $player->seat === 1,
                                        'text-on-surface-variant' => $player->seat > 1,
                                    ])>
                                        {{ $player->seatLabel() }}
                                    </span>
                                </div>
                                <p class="font-mono text-label-sm text-on-surface-variant">Iniciou com {{ $player->starting_life_points }} PV</p>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-1">
                            <button
                                type="button"
                                wire:click="editPlayerIdentity({{ $player->id }})"
                                class="flex size-11 items-center justify-center rounded text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-primary"
                                aria-label="Editar identidade de {{ $player->name }}"
                            >
                                <x-icon name="pencil" class="size-4" />
                            </button>

                            @if ($player->latestLifePointEvent && ! $player->latestLifePointEvent->undone_at)
                                <span @class([
                                    'rounded px-1.5 py-0.5 font-mono text-label-md font-bold',
                                    'bg-damage/15 text-damage' => $player->latestLifePointEvent->signedAmount() < 0,
                                    'bg-heal/15 text-heal' => $player->latestLifePointEvent->signedAmount() > 0,
                                ])>
                                    {{ $player->latestLifePointEvent->signedAmount() }}
                                </span>
                            @endif

                            @if ($isTwoPlayerDuel && $player->seat === 1)
                                <button
                                    type="button"
                                    wire:click="toggleOpponentRotation"
                                    class="flex size-11 items-center justify-center rounded bg-surface-container-high text-on-surface-variant transition hover:text-primary"
                                    aria-label="Girar painel do oponente"
                                >
                                    <x-icon name="rotate" class="size-4" />
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="mt-2 rounded-lg bg-surface-container-lowest/80 p-3 text-center shadow-inner">
                        <div class="flex items-baseline justify-center gap-2">
                            <span
                                x-bind:class="feedback === 'damage'
                                    ? 'life-point-value-feedback life-point-value-feedback--damage'
                                    : feedback === 'recovery'
                                        ? 'life-point-value-feedback life-point-value-feedback--recovery'
                                        : ''"
                                @class([
                                    'text-glow font-mono font-bold tracking-tight text-primary tabular-nums',
                                    'text-headline-xl-mobile' => $expandedPlayerId !== $player->id,
                                    'text-headline-xl' => $expandedPlayerId === $player->id,
                                ])
                                aria-live="polite"
                                aria-atomic="true"
                            >
                                {{ $player->life_points }}
                            </span>
                            <span class="font-mono text-label-md font-semibold tracking-widest text-primary-fixed-dim">LP</span>
                        </div>

                        <div class="mx-auto mt-2 h-1.5 w-full max-w-xs overflow-hidden rounded-full bg-surface-container-high">
                            <div
                                class="h-full rounded-full transition-all duration-300"
                                style="width: {{ min(100, $player->remainingPercentage()) }}%; background-color: var(--player-accent-{{ $player->accentToken() }}); box-shadow: 0 0 8px var(--player-accent-{{ $player->accentToken() }})"
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
                            aria-label="Reduzir pontos de vida de {{ $player->name }} à metade"
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
                    </div>

                    <div class="mt-1.5 grid grid-cols-3 gap-1.5">
                        <button
                            type="button"
                            wire:click="doubleLifePoints({{ $player->id }})"
                            class="flex h-12 items-center justify-center rounded bg-surface-container font-mono text-headline-sm font-bold text-secondary transition hover:bg-secondary/15 active:scale-95"
                            aria-label="Dobrar pontos de vida de {{ $player->name }}"
                        >
                            ×2
                        </button>
                        <button
                            type="button"
                            wire:click="openLifePointCalculator({{ $player->id }})"
                            class="flex h-12 items-center justify-center rounded bg-surface-container-high font-mono text-label-md font-bold text-on-surface-variant transition hover:bg-surface-container-highest active:scale-95"
                            aria-label="Abrir calculadora de pontos de vida para {{ $player->name }}"
                        >
                            Valor
                        </button>
                        <button
                            type="button"
                            wire:click="restore({{ $player->id }})"
                            class="flex h-12 items-center justify-center gap-1 rounded bg-surface-container-high font-mono text-label-md font-bold text-on-surface-variant transition hover:bg-surface-container-highest active:scale-95"
                            aria-label="Restaurar os pontos de vida iniciais de {{ $player->name }}"
                        >
                            <x-icon name="restart" class="size-4" />
                            {{ $player->starting_life_points }}
                        </button>
                    </div>

                    <div class="mt-2 flex items-center justify-end gap-1">
                        @if ($players->count() > 2)
                            <button
                                type="button"
                                wire:click="movePlayer({{ $player->id }}, 'up')"
                                @disabled($loop->first)
                                class="flex size-11 items-center justify-center rounded bg-surface-container-high text-on-surface-variant transition hover:text-primary disabled:opacity-30"
                                aria-label="Mover {{ $player->name }} para cima"
                            >
                                <x-icon name="arrow-up" class="size-4" />
                            </button>
                            <button
                                type="button"
                                wire:click="movePlayer({{ $player->id }}, 'down')"
                                @disabled($loop->last)
                                class="flex size-11 items-center justify-center rounded bg-surface-container-high text-on-surface-variant transition hover:text-primary disabled:opacity-30"
                                aria-label="Mover {{ $player->name }} para baixo"
                            >
                                <x-icon name="arrow-down" class="size-4" />
                            </button>
                        @endif
                        <button
                            type="button"
                            wire:click="togglePlayerExpansion({{ $player->id }})"
                            aria-expanded="{{ $expandedPlayerId === $player->id ? 'true' : 'false' }}"
                            aria-label="{{ $expandedPlayerId === $player->id ? 'Recolher' : 'Expandir' }} painel de {{ $player->name }}"
                            class="flex min-h-11 items-center gap-2 rounded bg-surface-container-high px-3 font-mono text-label-sm text-on-surface-variant transition hover:text-primary"
                        >
                            <x-icon :name="$expandedPlayerId === $player->id ? 'collapse' : 'expand'" class="size-4" />
                            {{ $expandedPlayerId === $player->id ? 'Recolher' : 'Expandir' }}
                        </button>
                    </div>
                </section>

                @if ($isTwoPlayerDuel && $player->seat === 1)
                    @include('livewire.partials.phase-ribbon')
                @endif
            @endforeach
        </div>
    </main>

    @if ($lifePointCalculatorPlayerId !== null && $lifePointCalculatorPlayer)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-surface/80 p-4 backdrop-blur-sm">
            <section
                role="dialog"
                aria-modal="true"
                aria-labelledby="life-point-calculator-title"
                class="max-h-[90dvh] w-full max-w-sm overflow-y-auto rounded-xl border border-white/10 bg-surface-container-lowest p-5 shadow-2xl"
                x-data="{
                    operation: 'subtract',
                    input: '',
                    inputError: '',
                    applying: false,
                    append(digit) {
                        if (this.input.length >= 6) {
                            this.inputError = 'O valor não pode exceder 999.999.';
                            return;
                        }

                        this.input = this.input === '0' ? String(digit) : this.input + String(digit);
                        this.inputError = '';
                    },
                    async apply(playerId) {
                        this.applying = true;

                        try {
                            await this.$wire.applyLifePointCalculation(playerId, this.input, this.operation);
                        } finally {
                            this.applying = false;
                        }
                    }
                }"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-mono text-label-sm uppercase tracking-widest text-primary-fixed-dim">{{ $lifePointCalculatorPlayer->name }}</p>
                        <h2 id="life-point-calculator-title" class="mt-1 font-sans text-headline-sm font-semibold text-on-surface">Calculadora de PV</h2>
                    </div>
                    <button
                        type="button"
                        wire:click="closeLifePointCalculator"
                        class="flex size-11 shrink-0 items-center justify-center rounded text-on-surface-variant transition hover:bg-surface-container-high hover:text-on-surface"
                        aria-label="Fechar calculadora de pontos de vida"
                    >
                        <x-icon name="x" />
                    </button>
                </div>

                <div role="group" aria-label="Operação da calculadora" class="mt-4 grid grid-cols-3 gap-2">
                    @foreach (['subtract' => 'Subtrair', 'add' => 'Adicionar', 'set' => 'Definir'] as $operation => $label)
                        <button
                            type="button"
                            x-on:click="operation = '{{ $operation }}'"
                            x-bind:aria-pressed="operation === '{{ $operation }}' ? 'true' : 'false'"
                            x-bind:class="operation === '{{ $operation }}'
                                ? 'border-primary-container bg-primary-container text-on-primary'
                                : 'border-white/10 bg-surface-container text-on-surface-variant hover:bg-surface-container-high'"
                            class="h-12 rounded border font-mono text-label-sm font-bold transition"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <div
                    class="mt-3 rounded-lg bg-surface-container-low px-4 py-3 text-right font-mono text-headline-md font-bold tabular-nums text-on-surface"
                    x-bind:aria-label="'Valor informado: ' + (input === '' ? 'nenhum' : input) + ' pontos de vida'"
                    aria-live="polite"
                >
                    <span x-text="input === '' ? '0' : input"></span>
                    <span class="text-label-sm text-on-surface-variant">PV</span>
                </div>

                <p
                    class="mt-2 rounded bg-surface-container px-3 py-2 text-center font-mono text-label-md text-on-surface-variant"
                    x-show="operation === 'set' && input !== '' && /^\d+$/.test(input) && Number(input) <= 999999"
                    aria-live="polite"
                >
                    Resultado: {{ number_format($lifePointCalculatorPlayer->life_points, 0, ',', '.') }}
                    → <span x-text="new Intl.NumberFormat('pt-BR').format(Number(input))"></span> PV
                </p>

                <div class="mt-3 grid grid-cols-3 gap-2">
                    @foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9] as $digit)
                        <button
                            type="button"
                            x-on:click="append({{ $digit }})"
                            class="h-12 rounded bg-surface-container font-mono text-headline-sm font-bold text-on-surface transition hover:bg-surface-container-high active:scale-95"
                            aria-label="Digitar {{ $digit }}"
                        >
                            {{ $digit }}
                        </button>
                    @endforeach
                    <button
                        type="button"
                        x-on:click="input = ''; inputError = ''"
                        class="h-12 rounded bg-surface-container-high font-mono text-label-sm font-bold text-on-surface-variant transition hover:bg-surface-container-highest active:scale-95"
                        aria-label="Limpar valor"
                    >
                        Limpar
                    </button>
                    <button
                        type="button"
                        x-on:click="append(0)"
                        class="h-12 rounded bg-surface-container font-mono text-headline-sm font-bold text-on-surface transition hover:bg-surface-container-high active:scale-95"
                        aria-label="Digitar 0"
                    >
                        0
                    </button>
                    <button
                        type="button"
                        x-on:click="input = input.slice(0, -1); inputError = ''"
                        class="h-12 rounded bg-surface-container-high font-mono text-label-sm font-bold text-on-surface-variant transition hover:bg-surface-container-highest active:scale-95"
                        aria-label="Apagar último dígito"
                    >
                        Apagar
                    </button>
                </div>

                <p x-show="inputError !== ''" role="alert" class="mt-3 rounded border border-damage/30 bg-damage/10 px-3 py-2 font-mono text-label-sm text-damage" x-text="inputError"></p>
                @error('input')
                    <p role="alert" class="mt-3 rounded border border-damage/30 bg-damage/10 px-3 py-2 font-mono text-label-sm text-damage">
                        {{ $message }}
                    </p>
                @enderror
                @error('operation')
                    <p role="alert" class="mt-3 rounded border border-damage/30 bg-damage/10 px-3 py-2 font-mono text-label-sm text-damage">
                        {{ $message }}
                    </p>
                @enderror

                <div class="mt-4 flex gap-2">
                    <button
                        type="button"
                        wire:click="closeLifePointCalculator"
                        class="h-12 flex-1 rounded border border-white/10 px-3 font-mono text-label-sm font-bold text-on-surface-variant transition hover:bg-surface-container-high"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        x-on:click="apply({{ $lifePointCalculatorPlayer->id }})"
                        x-bind:disabled="applying || inputError !== ''"
                        class="h-12 flex-1 rounded bg-primary-container px-3 font-mono text-label-sm font-bold text-on-primary transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <span x-text="operation === 'set' ? 'Aplicar definição' : 'Aplicar ' + (input || '')"></span>
                    </button>
                </div>
            </section>
        </div>
    @endif

    @if ($editingPlayerId !== null)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-surface/80 p-4 backdrop-blur-sm">
            <section
                role="dialog"
                aria-modal="true"
                aria-labelledby="player-identity-title"
                class="max-h-[90dvh] w-full max-w-md overflow-y-auto rounded-xl border border-white/10 bg-surface-container-lowest p-5 shadow-2xl"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-mono text-label-sm uppercase tracking-widest text-primary-fixed-dim">Opções individuais</p>
                        <h2 id="player-identity-title" class="mt-1 font-sans text-headline-sm font-semibold text-on-surface">Identidade do participante</h2>
                    </div>
                    <button
                        type="button"
                        wire:click="cancelPlayerIdentityEdit"
                        class="flex size-10 shrink-0 items-center justify-center rounded text-on-surface-variant transition hover:bg-surface-container-high hover:text-on-surface"
                        aria-label="Fechar edição de identidade"
                    >
                        <x-icon name="x" />
                    </button>
                </div>

                <label class="mt-5 block font-mono text-label-sm text-on-surface-variant">
                    Nome ou apelido
                    <input
                        type="text"
                        maxlength="40"
                        wire:model="editingPlayerName"
                        class="mt-1 h-12 w-full rounded border border-white/10 bg-surface-container-low px-3 font-sans text-body-md text-on-surface focus:border-primary-container focus:outline-none"
                        aria-label="Nome ou apelido do participante"
                    >
                </label>
                @error('editingPlayerName')
                    <p class="mt-1 font-mono text-label-sm text-damage">{{ $message }}</p>
                @enderror

                <div class="mt-5">
                    <p class="font-mono text-label-sm text-on-surface-variant">Cor de identificação</p>
                    <div role="group" aria-label="Cor de identificação do participante" class="mt-2 flex flex-wrap gap-2">
                        @foreach (\App\Models\Duel::PLAYER_ACCENTS as $accent => $label)
                            <button
                                type="button"
                                wire:click="$set('editingPlayerColor', '{{ $accent }}')"
                                aria-label="Cor {{ $label }}"
                                aria-pressed="{{ $editingPlayerColor === $accent ? 'true' : 'false' }}"
                                @class([
                                    'size-9 rounded-full border-2 transition',
                                    'border-on-surface ring-2 ring-primary/40' => $editingPlayerColor === $accent,
                                    'border-transparent' => $editingPlayerColor !== $accent,
                                ])
                                style="background-color: var(--player-accent-{{ $accent }})"
                            ></button>
                        @endforeach
                    </div>
                    @error('editingPlayerColor')
                        <p class="mt-2 font-mono text-label-sm text-damage">{{ $message }}</p>
                    @enderror
                </div>

                <label class="mt-5 block font-mono text-label-sm text-on-surface-variant">
                    Ícone ou avatar
                    <select
                        wire:model="editingPlayerIcon"
                        aria-label="Ícone do participante"
                        class="mt-1 h-12 w-full rounded border border-white/10 bg-surface-container-low px-3 font-sans text-body-md text-on-surface focus:border-primary-container focus:outline-none"
                    >
                        <option value="">Padrão da arena</option>
                        @foreach (\App\Models\Duel::PLAYER_ICONS as $icon => $label)
                            <option value="{{ $icon }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                @error('editingPlayerIcon')
                    <p class="mt-1 font-mono text-label-sm text-damage">{{ $message }}</p>
                @enderror

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        wire:click="cancelPlayerIdentityEdit"
                        class="h-11 rounded border border-white/10 px-4 font-mono text-label-sm text-on-surface-variant transition hover:bg-surface-container-high"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        wire:click="savePlayerIdentity"
                        class="h-11 rounded bg-primary-container px-4 font-mono text-label-sm font-bold text-on-primary transition hover:brightness-110"
                    >
                        Salvar
                    </button>
                </div>
            </section>
        </div>
    @endif
</div>
