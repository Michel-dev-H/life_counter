<native:column class="w-full h-full">
    @if ($duels->isEmpty())
        <native:column class="w-full h-full items-center justify-center gap-2 px-8">
            <native:text class="text-lg font-bold text-theme-on-surface text-center">Nenhum duelo ainda</native:text>
            <native:text class="text-theme-on-surface-variant text-center">
                Inicie uma nova partida para ter um histórico aqui.
            </native:text>
        </native:column>
    @else
        <native:list class="w-full flex-1">
            @foreach ($duels as $duel)
                <native:list-item
                    native:key="duel-{{ $duel->id }}"
                    :headline="'Duelo #'.$duel->id"
                    :supporting="$duel->duelPlayers->pluck('name')->implode(' vs ')"
                    trailingIcon="chevron.right"
                    @navigate="'/duel/'.$duel->id"
                />
            @endforeach
        </native:list>
    @endif
</native:column>
