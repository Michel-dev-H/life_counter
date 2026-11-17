<native:column class="w-full h-full gap-4 p-4">
    <native:row class="w-full items-end gap-2">
        <native:outlined-text-input
            class="flex-1"
            label="Quantidade"
            keyboard="number"
            native:model.debounce.300ms="diceCount"
        />

        <native:button label="Lançar" variant="primary" class="flex-1" @press="roll" />
    </native:row>

    @if ($lastRoll)
        <native:column class="w-full gap-1 rounded-xl bg-theme-surface-variant p-3">
            <native:text class="text-center text-3xl font-extrabold" font="display">
                {{ $lastRoll->total }}
            </native:text>
            <native:text class="text-center text-theme-on-surface-variant">
                {{ implode(' · ', $lastRoll->results) }}
            </native:text>
        </native:column>
    @endif

    <native:text class="font-bold uppercase text-theme-primary">Histórico</native:text>

    @if ($history->isEmpty())
        <native:text class="text-theme-on-surface-variant">Nenhum lançamento ainda.</native:text>
    @else
        <native:list class="w-full flex-1">
            @foreach ($history as $roll)
                <native:list-item
                    native:key="roll-{{ $roll->id }}"
                    :headline="implode(' · ', $roll->results)"
                    :supporting="$roll->created_at->format('H:i:s')"
                    :trailingText="(string) $roll->total"
                />
            @endforeach
        </native:list>
    @endif
</native:column>
