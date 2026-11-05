<div>
    <button
        type="button"
        wire:click="openRoller"
        class="flex min-h-11 items-center gap-2 rounded border border-primary-container/30 bg-primary-container/10 px-3 font-mono text-label-sm font-bold text-primary-fixed-dim transition hover:bg-primary-container/20"
        aria-haspopup="dialog"
        aria-label="Abrir lançador de dados"
    >
        <x-icon name="dice" class="size-4" />
        Lançar dados
    </button>

    @if ($isOpen)
        <div
            wire:keydown.escape="closeRoller"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4"
        >
            <button
                type="button"
                wire:click="closeRoller"
                class="absolute inset-0 bg-surface/85 backdrop-blur-sm"
                aria-label="Fechar lançador de dados"
            ></button>

            <section
                role="dialog"
                aria-modal="true"
                aria-labelledby="dice-roller-title"
                class="relative z-10 max-h-[92dvh] w-full max-w-lg overflow-y-auto rounded-xl border border-white/10 bg-surface-container-lowest p-4 shadow-2xl sm:p-5"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="font-mono text-label-sm uppercase tracking-widest text-primary-fixed-dim">Ferramenta do duelo</p>
                        <h2 id="dice-roller-title" class="mt-1 font-sans text-headline-sm font-semibold text-on-surface">Lançar dados</h2>
                    </div>
                    <button
                        type="button"
                        wire:click="closeRoller"
                        class="flex size-11 shrink-0 items-center justify-center rounded text-on-surface-variant transition hover:bg-surface-container-high hover:text-on-surface"
                        aria-label="Fechar lançador de dados"
                    >
                        <x-icon name="x" />
                    </button>
                </div>

                <div class="mt-4 flex items-center justify-between gap-3 rounded-lg bg-surface-container-low p-3">
                    <span class="font-mono text-label-md text-on-surface-variant">Quantidade de dados</span>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="decreaseDiceCount"
                            @disabled($diceCount <= 1)
                            wire:loading.attr="disabled"
                            class="flex size-11 items-center justify-center rounded bg-surface-container text-on-surface transition hover:bg-surface-container-high disabled:opacity-40"
                            aria-label="Reduzir quantidade de dados"
                        >
                            <x-icon name="minus" />
                        </button>
                        <input
                            type="number"
                            inputmode="numeric"
                            min="1"
                            max="{{ \App\Livewire\DuelDice::MAX_DICE }}"
                            wire:model.live="diceCount"
                            class="h-11 w-14 rounded border border-white/10 bg-surface-container text-center font-mono text-headline-sm font-bold tabular-nums text-on-surface"
                            aria-label="Quantidade de dados, de 1 a 10"
                        >
                        <button
                            type="button"
                            wire:click="increaseDiceCount"
                            @disabled($diceCount >= \App\Livewire\DuelDice::MAX_DICE)
                            wire:loading.attr="disabled"
                            class="flex size-11 items-center justify-center rounded bg-surface-container text-on-surface transition hover:bg-surface-container-high disabled:opacity-40"
                            aria-label="Aumentar quantidade de dados"
                        >
                            <x-icon name="plus" />
                        </button>
                    </div>
                </div>

                @error('diceCount')
                    <p role="alert" class="mt-2 rounded border border-damage/30 bg-damage/10 px-3 py-2 font-mono text-label-sm text-damage">
                        {{ $message }}
                    </p>
                @enderror
                @error('dice')
                    <p role="alert" class="mt-2 rounded border border-damage/30 bg-damage/10 px-3 py-2 font-mono text-label-sm text-damage">
                        {{ $message }}
                    </p>
                @enderror

                <div class="mt-3 flex items-center gap-3">
                    <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded px-2 font-mono text-label-sm text-on-surface-variant">
                        <input
                            type="checkbox"
                            wire:model.live="showTotal"
                            class="size-5 accent-primary-container"
                        >
                        Mostrar soma
                    </label>
                </div>

                <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                    <button
                        type="button"
                        wire:click="rollDice"
                        wire:loading.attr="disabled"
                        class="flex h-12 w-full items-center justify-center gap-2 rounded bg-primary-container px-3 font-mono text-label-md font-bold text-on-primary transition hover:brightness-110 disabled:opacity-60 sm:flex-1"
                    >
                        <x-icon name="dice" class="size-4" />
                        <span wire:loading.remove wire:target="rollDice,repeatDiceRoll">Lançar {{ $diceCount }} {{ $diceCount === 1 ? 'dado' : 'dados' }}</span>
                        <span wire:loading wire:target="rollDice,repeatDiceRoll">Lançando…</span>
                    </button>
                    <button
                        type="button"
                        wire:click="repeatDiceRoll"
                        wire:loading.attr="disabled"
                        @disabled($repeatDiceCount === null)
                        class="flex h-12 w-full items-center justify-center gap-2 rounded border border-white/10 px-3 font-mono text-label-sm font-bold text-on-surface-variant transition hover:bg-surface-container-high disabled:opacity-40 sm:w-auto"
                        aria-label="Repetir último lançamento"
                    >
                        <x-icon name="restart" class="size-4" />
                        <span>{{ $repeatDiceCount === null ? 'Repetir' : 'Repetir '.$repeatDiceCount.' '.($repeatDiceCount === 1 ? 'dado' : 'dados') }}</span>
                    </button>
                </div>

                @if ($latestDiceRoll !== null)
                    <section
                        wire:key="dice-roll-result-{{ $latestDiceRoll->id }}"
                        class="mt-4 rounded-lg border border-primary-container/20 bg-surface-container-low p-3"
                        aria-live="polite"
                        aria-label="Resultado do último lançamento"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="font-mono text-label-sm uppercase tracking-wider text-on-surface-variant">Último resultado</h3>
                            <span class="font-mono text-label-xs text-on-surface-variant">
                                {{ $latestDiceRoll->dice_count }} {{ $latestDiceRoll->dice_count === 1 ? 'dado' : 'dados' }}
                            </span>
                        </div>

                        <ol class="mt-3 grid grid-cols-5 gap-2" aria-label="Faces dos dados">
                            @foreach ($latestDiceRoll->results as $index => $result)
                                <li
                                    wire:key="dice-roll-{{ $latestDiceRoll->id }}-{{ $index }}"
                                    class="dice-roll-reveal flex aspect-square items-center justify-center rounded-lg border border-white/10 bg-surface-container-high font-mono text-headline-md font-bold tabular-nums text-on-surface"
                                    aria-label="Dado {{ $index + 1 }}: {{ $result }}"
                                >
                                    {{ $result }}
                                </li>
                            @endforeach
                        </ol>

                        @if ($showTotal)
                            <p class="mt-3 text-right font-mono text-label-md text-on-surface-variant">
                                Soma: <strong class="text-headline-sm text-primary-fixed-dim">{{ $latestDiceRoll->total }}</strong>
                            </p>
                        @endif
                    </section>
                @endif

                <section class="mt-4" aria-labelledby="dice-history-title">
                    <div class="flex items-center justify-between gap-2">
                        <h3 id="dice-history-title" class="font-mono text-label-sm font-bold uppercase tracking-wider text-on-surface">Histórico recente</h3>
                        <span class="font-mono text-label-xs text-on-surface-variant">Até 10 lançamentos</span>
                    </div>

                    @if ($rolls->count() <= 1)
                        <p class="mt-2 rounded bg-surface-container-low px-3 py-2 font-mono text-label-sm text-on-surface-variant">
                            Ainda não há lançamentos anteriores nesta partida.
                        </p>
                    @else
                        <ol class="mt-2 flex max-h-48 flex-col gap-2 overflow-y-auto" aria-label="Lançamentos anteriores desta partida">
                            @foreach ($rolls->skip(1) as $roll)
                                <li
                                    wire:key="dice-history-{{ $roll->id }}"
                                    class="flex items-start justify-between gap-3 rounded bg-surface-container-low px-3 py-2 font-mono text-label-sm"
                                >
                                    <div class="min-w-0">
                                        <p class="text-on-surface-variant">
                                            {{ $roll->created_at->format('H:i') }} · {{ $roll->dice_count }} {{ $roll->dice_count === 1 ? 'dado' : 'dados' }}
                                        </p>
                                        <p class="mt-1 truncate text-on-surface" aria-label="Resultados: {{ implode(', ', $roll->results) }}">
                                            {{ implode(' · ', $roll->results) }}
                                        </p>
                                    </div>
                                    @if ($showTotal)
                                        <span class="shrink-0 text-primary-fixed-dim">Σ {{ $roll->total }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>
            </section>
        </div>
    @endif
</div>
