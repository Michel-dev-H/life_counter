<main class="mx-auto w-full max-w-4xl px-4 py-8 sm:px-6 lg:py-12">
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-primary">Duel Companion</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight text-on-surface">Início</h1>
            <p class="mt-2 text-sm text-on-surface-variant">Partidas e opções principais em um só lugar.</p>
        </div>

        <a
            href="{{ route('duel.setup') }}"
            class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-primary-container px-4 py-2 text-sm font-semibold text-on-primary transition hover:brightness-110"
        >
            <x-icon name="plus" class="size-4" />
            Nova partida
        </a>
    </header>

    <section class="mt-8 grid gap-3 sm:grid-cols-2" aria-label="Acesso rápido">
        <a
            href="{{ route('duel.setup') }}"
            class="rounded-2xl border border-white/10 bg-surface-container-low p-5 transition hover:border-primary-container/40"
        >
            <span class="flex size-10 items-center justify-center rounded-xl bg-primary-container/10 text-primary">
                <x-icon name="swords" class="size-5" />
            </span>
            <h2 class="mt-4 font-semibold text-on-surface">Preparar partida</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Escolha os duelistas e os pontos de vida iniciais.</p>
        </a>

        <a
            href="{{ route('appearance.edit') }}"
            class="rounded-2xl border border-white/10 bg-surface-container-low p-5 transition hover:border-primary-container/40"
        >
            <span class="flex size-10 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
                <x-icon name="settings" class="size-5" />
            </span>
            <h2 class="mt-4 font-semibold text-on-surface">Aparência</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Escolha um tema e ajuste o papel de parede.</p>
        </a>
    </section>

    <section class="mt-10">
        <div>
            <h2 class="text-lg font-semibold text-on-surface">Partidas recentes</h2>
            <p class="mt-1 text-sm text-on-surface-variant">Continue uma partida salva neste dispositivo.</p>
        </div>

        @forelse ($recentDuels as $duel)
            <a
                href="{{ route('duel.arena', $duel) }}"
                class="mt-3 flex items-center justify-between gap-4 rounded-2xl border border-white/10 bg-surface-container-low px-4 py-4 transition hover:border-primary-container/40"
            >
                <span class="min-w-0">
                    <span class="block font-medium text-on-surface">Duelo #{{ $duel->id }}</span>
                    <span class="mt-1 block truncate text-sm text-on-surface-variant">
                        {{ $duel->duelPlayers->pluck('name')->join(' · ') }}
                    </span>
                </span>
                <span class="shrink-0 rounded-full bg-surface-container-high px-3 py-1 text-xs text-on-surface-variant">
                    {{ $duel->duelPlayers->count() }} {{ $duel->duelPlayers->count() === 1 ? 'jogador' : 'jogadores' }}
                </span>
            </a>
        @empty
            <div class="mt-3 rounded-2xl border border-dashed border-white/15 px-5 py-8 text-center">
                <p class="font-medium text-on-surface">Nenhum duelo iniciado ainda.</p>
                <p class="mt-1 text-sm text-on-surface-variant">Sua próxima partida aparecerá aqui.</p>
                <a href="{{ route('duel.setup') }}" class="mt-4 inline-flex min-h-10 items-center rounded-xl px-4 text-sm font-semibold text-primary hover:bg-primary-container/10">
                    Iniciar primeira partida
                </a>
            </div>
        @endforelse
    </section>
</main>
