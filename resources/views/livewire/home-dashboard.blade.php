<x-arena-shell
    title="Duel Companion"
    subtitle="Início"
    active="home"
>
    <div class="text-center space-y-2">
        <h2 class="font-display text-4xl font-extrabold text-branco-frio tracking-tight drop-shadow-[0_0_12px_rgba(53,216,255,0.3)]">DUELO</h2>
        <p class="font-body text-sm text-branco-frio/60">Prepare sua arena e comece a jogar.</p>
    </div>

    <div class="flex flex-col gap-4 w-full max-w-xs">
        <a
            href="{{ route('duel.setup') }}"
            class="flex items-center justify-center gap-2 rounded-xl bg-ciano-holografico text-preto-profundo font-headline text-sm font-bold uppercase tracking-wider py-4 shadow-[0_0_20px_rgba(53,216,255,0.4)] transition hover:brightness-110 active:scale-[0.99]"
        >
            <x-icon name="plus" class="size-5" />
            Iniciar Duelo
        </a>

        <a
            href="{{ route('appearance.edit') }}"
            class="flex items-center justify-center gap-2 rounded-xl bg-grafite border border-ciano-holografico/30 text-ciano-holografico font-headline text-sm font-bold uppercase tracking-wider py-4 transition hover:bg-azul-tecnologico/20 active:scale-[0.99]"
        >
            <x-icon name="palette" class="size-5" />
            Aparência
        </a>
    </div>
</x-arena-shell>
