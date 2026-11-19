<native:column class="w-full h-full">
    <native:scroll-view class="w-full flex-1">
        <native:column class="w-full gap-3 p-3">

            {{-- Phase ribbon --}}
            <native:column class="w-full gap-2 rounded-xl bg-theme-surface-variant p-3">
                <native:row class="w-full items-center justify-between">
                    <native:text class="font-bold text-theme-gold">{{ $duel->turnLabel() }}</native:text>
                    <native:text class="text-theme-on-surface-variant">{{ $turnOwner }}</native:text>
                    <native:text class="font-bold text-theme-primary">{{ $timerRemaining }}</native:text>
                </native:row>

                <native:row class="w-full gap-1 flex-wrap">
                    @foreach ($phases as $index => $phase)
                        <native:button
                            label="{{ $phase->value }}"
                            size="sm"
                            variant="{{ $currentPhase === $phase ? 'primary' : 'secondary' }}"
                            a11y-label="Fase {{ $phase->label() }}"
                            @press="setPhase({{ $index }})"
                        />
                    @endforeach
                </native:row>

                <native:row class="w-full items-center justify-between">
                    <native:button label="Turno" icon="arrow.right" size="sm" @press="advanceTurn" />
                    <native:button
                        label="{{ $timerRunning ? 'Pausar' : 'Iniciar' }}"
                        icon="{{ $timerRunning ? 'pause' : 'play' }}"
                        size="sm"
                        @press="toggleTimer"
                    />
                    <native:button label="Zerar" icon="arrow.counterclockwise" size="sm" @press="resetTimer" />
                    <native:button
                        icon="arrow.uturn.backward"
                        size="sm"
                        a11y-label="Desfazer última alteração"
                        :disabled="! $canUndo"
                        @press="undo"
                    />
                    <native:button
                        icon="arrow.uturn.forward"
                        size="sm"
                        a11y-label="Refazer alteração desfeita"
                        :disabled="! $canRedo"
                        @press="redo"
                    />
                </native:row>

                <native:text class="text-sm text-theme-on-surface-variant">
                    @if ($lastEvent)
                        <native:text class="text-theme-on-surface">{{ $lastEvent->duelPlayer->name }}</native:text>
                        <native:text class="text-theme-damage">{{ $lastEvent->signedAmount() < 0 ? '' : '+' }}{{ $lastEvent->signedAmount() }}</native:text>
                    @else
                        Nenhuma alteração
                    @endif
                </native:text>
            </native:column>

            @if ($firstPlayerDrawn)
                <native:column class="w-full gap-1 rounded-xl bg-theme-gold p-3">
                    <native:text class="font-bold">{{ $firstPlayerDrawn }} começa.</native:text>
                    <native:text class="text-sm">O sorteio é apenas um auxílio; sigam o formato aplicável.</native:text>
                </native:column>
            @endif

            {{-- Players --}}
            @foreach ($players as $player)
                <native:column class="w-full gap-2 rounded-xl bg-theme-surface p-3">
                    <native:row class="w-full items-center justify-between">
                        <native:text class="font-bold text-theme-primary">{{ $player->name }}</native:text>
                        <native:text class="text-sm text-theme-on-surface-variant">{{ $player->seatLabel() }}</native:text>
                    </native:row>

                    <native:text class="text-4xl font-extrabold text-center" font="display">
                        {{ $player->life_points }}
                    </native:text>

                    <native:row class="w-full gap-1 flex-wrap">
                        <native:button
                            label="−1000"
                            variant="destructive"
                            size="sm"
                            class="flex-1"
                            @press="subtract({{ $player->id }}, 1000)"
                        />
                        <native:button
                            label="−500"
                            variant="destructive"
                            size="sm"
                            class="flex-1"
                            @press="subtract({{ $player->id }}, 500)"
                        />
                        <native:button
                            label="+500"
                            variant="success"
                            size="sm"
                            class="flex-1"
                            @press="add({{ $player->id }}, 500)"
                        />
                        <native:button
                            label="+1000"
                            variant="success"
                            size="sm"
                            class="flex-1"
                            @press="add({{ $player->id }}, 1000)"
                        />
                    </native:row>

                    <native:row class="w-full gap-1 flex-wrap">
                        <native:button label="÷2" size="sm" class="flex-1" @press="halve({{ $player->id }})" />
                        <native:button label="×2" size="sm" class="flex-1" @press="doubleLifePoints({{ $player->id }})" />
                        <native:button
                            label="Calc"
                            size="sm"
                            class="flex-1"
                            @press="openLifePointCalculator({{ $player->id }})"
                        />
                        <native:button label="Rest." size="sm" class="flex-1" @press="restore({{ $player->id }})" />
                    </native:row>

                    <native:row class="w-full items-center justify-end gap-2">
                        <native:button
                            icon="arrow.up"
                            size="sm"
                            variant="ghost"
                            a11y-label="Mover {{ $player->name }} para cima"
                            @press="movePlayer({{ $player->id }}, 'up')"
                        />
                        <native:button
                            icon="arrow.down"
                            size="sm"
                            variant="ghost"
                            a11y-label="Mover {{ $player->name }} para baixo"
                            @press="movePlayer({{ $player->id }}, 'down')"
                        />
                    </native:row>

                    <native:button
                        label="Editar identidade"
                        variant="ghost"
                        size="sm"
                        class="w-full"
                        @press="editPlayerIdentity({{ $player->id }})"
                    />
                </native:column>
            @endforeach

            {{-- Tools --}}
            <native:row class="w-full gap-2 flex-wrap">
                <native:button
                    label="Dados"
                    icon="casino"
                    variant="secondary"
                    class="flex-1"
                    @navigate="'/duel/'.$duel->id.'/dice'"
                />
                <native:button
                    label="Moeda"
                    icon="toll"
                    variant="secondary"
                    class="flex-1"
                    @navigate="'/duel/'.$duel->id.'/coin'"
                />
                <native:button
                    label="Lembretes"
                    icon="bell"
                    variant="secondary"
                    class="flex-1"
                    @navigate="'/duel/'.$duel->id.'/reminders'"
                />
            </native:row>

            <native:row class="w-full gap-2 flex-wrap">
                <native:button label="Sorteio" icon="dice" variant="ghost" class="flex-1" @press="drawFirstPlayer" />
                <native:button label="Vibração" icon="waveform" variant="ghost" class="flex-1" @press="toggleHapticFeedback" />
            </native:row>

            @error('amount')
                <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
            @enderror

            @error('hapticFeedback')
                <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
            @enderror

            @error('phase')
                <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
            @enderror

            @error('layout')
                <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
            @enderror
        </native:column>
    </native:scroll-view>

    {{-- Life point calculator --}}
    <native:modal :visible="$calculatorPlayerId !== null" @dismiss="closeLifePointCalculator">
        <native:column class="w-full gap-3 p-4">
            <native:text class="text-lg font-bold">
                {{ $calculatorPlayer?->name ?? '' }} · Pontos de vida
            </native:text>

            <native:row class="w-full gap-2 flex-wrap">
                @foreach ($calculatorOperations as $index => $operation)
                    <native:chip
                        label="{{ $operation['label'] }}"
                        :selected="$calculatorOperation === $operation['value']"
                        @press="setCalculatorOperation({{ $index }})"
                    />
                @endforeach
            </native:row>

            <native:outlined-text-input
                class="w-full"
                label="Valor"
                placeholder="0"
                keyboard="number"
                native:model.debounce.300ms="calculatorInput"
            />

            @error('input')
                <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
            @enderror

            @error('operation')
                <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
            @enderror

            <native:row class="w-full gap-2">
                <native:button label="Cancelar" variant="ghost" class="flex-1" @press="closeLifePointCalculator" />
                <native:button label="Aplicar" variant="primary" class="flex-1" @press="applyLifePointCalculation({{ $calculatorPlayerId ?? 0 }})" />
            </native:row>
        </native:column>
    </native:modal>

    {{-- Player identity --}}
    <native:modal :visible="$editingPlayerId !== null" @dismiss="cancelPlayerIdentityEdit">
        <native:column class="w-full gap-3 p-4">
            <native:text class="text-lg font-bold">Identidade do participante</native:text>

            <native:outlined-text-input
                class="w-full"
                label="Nome"
                placeholder="Jogador"
                max-length="40"
                native:model.debounce.300ms="editingPlayerName"
            />

            <native:text class="text-sm text-theme-on-surface-variant">Cor de identificação</native:text>
            <native:row class="w-full gap-2 flex-wrap">
                @foreach ($playerAccents as $index => $accent)
                    <native:pressable
                        class="w-8 h-8 rounded-full border-2 {{ $editingPlayerColor === $accent['key'] ? 'border-theme-on-surface' : 'border-transparent' }}"
                        a11y-label="Cor {{ $accent['label'] }}"
                        @press="setEditingPlayerColor({{ $index }})"
                    >
                        <native:column class="w-full h-full rounded-full bg-theme-player-{{ $accent['key'] }}"></native:column>
                    </native:pressable>
                @endforeach
            </native:row>

            <native:text class="text-sm text-theme-on-surface-variant">Ícone</native:text>
            <native:row class="w-full gap-2 flex-wrap">
                @foreach ($playerIcons as $index => $icon)
                    <native:chip
                        ios-icon="{{ $icon['ios'] }}"
                        android-icon="{{ $icon['android'] }}"
                        :selected="$editingPlayerIcon === $icon['key']"
                        a11y-label="Ícone {{ $icon['label'] }}"
                        @press="setEditingPlayerIcon({{ $index }})"
                    />
                @endforeach
            </native:row>

            @error('editingPlayerName')
                <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
            @enderror

            @error('editingPlayerColor')
                <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
            @enderror

            @error('editingPlayerIcon')
                <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
            @enderror

            <native:row class="w-full gap-2">
                <native:button label="Cancelar" variant="ghost" class="flex-1" @press="cancelPlayerIdentityEdit" />
                <native:button label="Salvar" variant="primary" class="flex-1" @press="savePlayerIdentity" />
            </native:row>
        </native:column>
    </native:modal>
</native:column>
