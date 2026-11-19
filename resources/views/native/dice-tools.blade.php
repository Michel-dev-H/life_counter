@php
    $pipMap = [
        1 => [[1, 1]],
        2 => [[0, 0], [2, 2]],
        3 => [[0, 0], [1, 1], [2, 2]],
        4 => [[0, 0], [2, 0], [0, 2], [2, 2]],
        5 => [[0, 0], [2, 0], [1, 1], [0, 2], [2, 2]],
        6 => [[0, 0], [2, 0], [0, 1], [2, 1], [0, 2], [2, 2]],
    ];
@endphp

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

    @if ($rolling || $lastRoll)
        <native:column class="w-full gap-2 rounded-xl bg-theme-surface-variant p-3">
            <native:row
                class="w-full flex-wrap justify-center gap-2"
                @if ($rolling) native:poll="90ms" @endif
                :rotate="$rolling ? ($frame % 2 === 0 ? 4 : -4) : 0"
                :scale="$rolling ? 1.05 : 1"
                animate-duration="90"
                animate-easing="ease-in-out"
            >
                @foreach ($faces as $index => $face)
                    <native:stack
                        class="{{ count($faces) > 5 ? 'w-9 h-9' : 'w-11 h-11' }} rounded-lg bg-theme-surface border border-theme-outline p-1.5"
                        :a11y-label="'Dado '.($index + 1).': '.$face"
                    >
                        <native:row class="w-full h-full justify-between">
                            @for ($column = 0; $column < 3; $column++)
                                <native:column class="justify-between items-center">
                                    @for ($row = 0; $row < 3; $row++)
                                        <native:circle
                                            class="w-1.5 h-1.5 {{ in_array([$column, $row], $pipMap[$face], true) ? 'bg-theme-on-surface' : 'opacity-0' }}"
                                        />
                                    @endfor
                                </native:column>
                            @endfor
                        </native:row>
                    </native:stack>
                @endforeach
            </native:row>

            @unless ($rolling)
                <native:text class="text-center text-3xl font-extrabold" font="display">{{ $lastRoll->total }}</native:text>
                <native:text class="text-center text-theme-on-surface-variant">{{ implode(' · ', $lastRoll->results) }}</native:text>
            @endunless
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
