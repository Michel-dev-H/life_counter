<native:column class="w-full h-full gap-4 p-4">
    <native:button label="Novo lembrete" icon="plus" variant="primary" class="w-full" @press="openForm" />

    @if ($reminders->isEmpty())
        <native:text class="text-theme-on-surface-variant">Nenhum lembrete ainda.</native:text>
    @else
        <native:list class="w-full flex-1">
            @foreach ($reminders as $reminder)
                <native:list-item
                    native:key="reminder-{{ $reminder->id }}"
                    :headline="$reminder->name"
                    :supporting="$reminder->description"
                    :leadingCheckbox="$reminder->completed"
                    on-leading-change="toggle({{ $reminder->id }})"
                    :trailingBadges="in_array($reminder->id, $highlightedIds, true) ? [['icon' => 'flag', 'color' => '#D6B56D']] : []"
                    trailingIconButton="trash"
                    trailing-a11y-label="Excluir lembrete {{ $reminder->name }}"
                    on-trailing-press="delete({{ $reminder->id }})"
                />
            @endforeach
        </native:list>
    @endif

    <native:modal :visible="$showForm" @dismiss="closeForm">
        <native:column class="w-full gap-3 p-4">
            <native:text class="text-lg font-bold">Novo lembrete</native:text>

            <native:outlined-text-input
                class="w-full"
                label="Nome"
                placeholder="Custo de manutenção"
                max-length="120"
                native:model.debounce.300ms="name"
            />

            <native:outlined-text-input
                class="w-full"
                label="Descrição"
                placeholder="Pagar 700 pontos de vida"
                native:model.debounce.300ms="description"
            />

            <native:outlined-text-input
                class="w-full"
                label="Gatilho por turno"
                placeholder="Ex.: 3"
                keyboard="number"
                native:model.debounce.300ms="triggerTurn"
            />

            <native:text class="text-sm text-theme-on-surface-variant">Gatilho por fase</native:text>
            <native:row class="w-full gap-2 flex-wrap">
                @foreach ($phases as $index => $phase)
                    <native:chip
                        label="{{ $phase->value }}"
                        :selected="$triggerPhase === $phase->value"
                        a11y-label="Fase {{ $phase->label() }}"
                        @press="setTriggerPhase({{ $index }})"
                    />
                @endforeach
            </native:row>

            @error('name')
                <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
            @enderror

            @error('triggerTurn')
                <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
            @enderror

            @error('triggerPhase')
                <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
            @enderror

            <native:row class="w-full gap-2">
                <native:button label="Cancelar" variant="ghost" class="flex-1" @press="closeForm" />
                <native:button label="Salvar" variant="primary" class="flex-1" @press="save" />
            </native:row>
        </native:column>
    </native:modal>
</native:column>
