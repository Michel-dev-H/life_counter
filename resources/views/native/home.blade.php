<native:column class="w-full h-full items-center justify-center gap-3 px-6 py-10">
    <native:text class="text-4xl font-extrabold text-theme-primary text-center" font="display">DUELO</native:text>

    <native:text class="text-theme-on-surface-variant text-center">Prepare sua arena e comece a jogar.</native:text>

    <native:column class="w-full gap-3 mt-10">
        <native:button
            label="Iniciar Duelo"
            variant="primary"
            icon="plus"
            class="w-full"
            @navigate="'/duel/new'"
        />

        <native:button
            label="Duelos Recentes"
            variant="secondary"
            icon="clock"
            class="w-full"
            @navigate="'/duels'"
        />

        <native:button
            label="Aparência"
            variant="ghost"
            icon="paintbrush"
            class="w-full"
            @navigate="'/appearance/settings'"
        />
    </native:column>
</native:column>
