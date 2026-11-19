<native:column class="w-full h-full gap-4 p-4">
    <native:button label="Lançar" variant="primary" size="lg" class="w-full" @press="flip" />

    @if ($flipping || $lastFlip)
        <native:column
            class="w-full items-center gap-2 rounded-xl bg-theme-surface-variant p-3"
            @if ($flipping) native:poll="90ms" @endif
            :scale="$flipping ? 1.05 : 1"
            animate-duration="90"
            animate-easing="ease-in-out"
        >
            <native:stack
                class="items-center justify-center"
                :a11y-label="'Resultado da moeda: '.($face === 'heads' ? 'Cara' : 'Coroa')"
            >
                <native:circle
                    class="w-24 h-24 border-2 border-theme-primary {{ $face === 'heads' ? 'bg-theme-surface' : 'bg-theme-surface-variant' }}"
                    :rotate="$flipping ? $frame * 45 : 0"
                    animate-duration="90"
                    animate-easing="linear"
                />
                <native:text
                    class="text-2xl font-extrabold"
                    font="display"
                    content-transition="opacity"
                    animate-duration="120"
                >{{ $face === 'heads' ? 'Cara' : 'Coroa' }}</native:text>
            </native:stack>

            @unless ($flipping)
                <native:text class="text-theme-on-surface-variant">{{ $lastFlip->created_at->format('H:i:s') }}</native:text>
            @endunless
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
