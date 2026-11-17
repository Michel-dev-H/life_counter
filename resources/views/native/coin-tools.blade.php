<native:column class="w-full h-full gap-4 p-4">
    <native:button label="Lançar" variant="primary" size="lg" class="w-full" @press="flip" />

    @if ($lastFlip)
        <native:column class="w-full gap-1 rounded-xl bg-theme-surface-variant p-3">
            <native:text class="text-center text-3xl font-extrabold" font="display">
                {{ $lastFlip->result === 'heads' ? 'Cara' : 'Coroa' }}
            </native:text>
            <native:text class="text-center text-theme-on-surface-variant">
                {{ $lastFlip->created_at->format('H:i:s') }}
            </native:text>
        </native:column>
    @endif

    <native:row class="w-full gap-2">
        <native:text class="flex-1 text-center text-theme-on-surface-variant">Cara: {{ $counts['heads'] }}</native:text>
        <native:text class="flex-1 text-center text-theme-on-surface-variant">Coroa: {{ $counts['tails'] }}</native:text>
        <native:text class="flex-1 text-center text-theme-on-surface-variant">Total: {{ $counts['heads'] + $counts['tails'] }}</native:text>
    </native:row>

    <native:text class="font-bold uppercase text-theme-primary">Histórico</native:text>

    @if ($history->isEmpty())
        <native:text class="text-theme-on-surface-variant">Nenhuma jogada ainda.</native:text>
    @else
        <native:list class="w-full flex-1">
            @foreach ($history as $flip)
                <native:list-item
                    native:key="flip-{{ $flip->id }}"
                    :headline="$flip->result === 'heads' ? 'Cara' : 'Coroa'"
                    :supporting="$flip->created_at->format('H:i:s')"
                />
            @endforeach
        </native:list>
    @endif
</native:column>
