<native:column class="w-full h-full">
    <native:scroll-view class="w-full flex-1">
        <native:column class="w-full gap-5 p-4">

            <native:column class="w-full gap-2">
                <native:text class="font-bold uppercase text-theme-primary">Capacidade da Arena</native:text>
                <native:text class="text-theme-on-surface-variant">1 a {{ $maxPlayers }} duelistas na mesma mesa</native:text>

                <native:row class="w-full gap-2 flex-wrap">
                    @foreach ($capacityLabels as $count => $label)
                        <native:button
                            label="{{ $count }} · {{ $label }}"
                            size="sm"
                            variant="{{ count($playerNames) === $count ? 'primary' : 'secondary' }}"
                            @press="setPlayerCount({{ $count }})"
                        />
                    @endforeach
                </native:row>
            </native:column>

            <native:column class="w-full gap-2">
                <native:text class="font-bold uppercase text-theme-primary">Pontos de Vida Iniciais (PV / LP)</native:text>

                <native:row class="w-full gap-2 flex-wrap">
                    @foreach ($lifePointPresets as $preset)
                        <native:button
                            label="{{ $preset['label'] }} · {{ number_format($preset['value'], 0, ',', '.') }}"
                            size="sm"
                            variant="{{ (int) $startingLifePoints === $preset['value'] ? 'primary' : 'secondary' }}"
                            @press="setStartingLifePoints({{ $preset['value'] }})"
                        />
                    @endforeach
                </native:row>

                <native:outlined-text-input
                    class="w-full"
                    label="Pontos de vida personalizados"
                    placeholder="8000"
                    keyboard="number"
                    native:model.debounce.300ms="startingLifePoints"
                />

                @error('startingLifePoints')
                    <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
                @enderror
            </native:column>

            <native:column class="w-full gap-2">
                <native:text class="font-bold uppercase text-theme-primary">Duelistas</native:text>

                @foreach ($playerNames as $index => $name)
                    <native:column class="w-full gap-3 rounded-xl bg-theme-surface-variant p-3">
                        <native:row class="w-full items-center gap-2">
                            <native:text class="font-bold text-theme-primary">P{{ $index + 1 }}</native:text>

                            <native:outlined-text-input
                                class="flex-1"
                                placeholder="Jogador {{ $index + 1 }}"
                                max-length="40"
                                native:model.debounce.300ms="playerNames.{{ $index }}"
                            />

                            <native:button
                                icon="xmark"
                                variant="ghost"
                                size="sm"
                                a11y-label="Remover jogador {{ $index + 1 }}"
                                :disabled="count($playerNames) === 1"
                                @press="removePlayer({{ $index }})"
                            />
                        </native:row>

                        <native:row class="w-full gap-2 flex-wrap">
                            @foreach ($playerAccents as $accentIndex => $accent)
                                <native:pressable
                                    class="w-8 h-8 rounded-full border-2 {{ ($playerColors[$index] ?? null) === $accent['key'] ? 'border-theme-on-surface' : 'border-transparent' }}"
                                    a11y-label="Cor {{ $accent['label'] }}"
                                    @press="setPlayerColor({{ $index }}, {{ $accentIndex }})"
                                >
                                    <native:column class="w-full h-full rounded-full bg-theme-player-{{ $accent['key'] }}"></native:column>
                                </native:pressable>
                            @endforeach
                        </native:row>

                        <native:row class="w-full gap-2 flex-wrap">
                            @foreach ($playerIcons as $iconIndex => $icon)
                                <native:chip
                                    ios-icon="{{ $icon['ios'] }}"
                                    android-icon="{{ $icon['android'] }}"
                                    :selected="($playerIcons[$index] ?? null) === $icon['key']"
                                    a11y-label="Ícone {{ $icon['label'] }}"
                                    @press="setPlayerIcon({{ $index }}, {{ $iconIndex }})"
                                />
                            @endforeach
                        </native:row>

                        @error('playerColors.'.$index)
                            <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
                        @enderror

                        @error('playerIcons.'.$index)
                            <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
                        @enderror
                    </native:column>
                @endforeach

                @error('playerNames')
                    <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
                @enderror

                @error('playerNames.*')
                    <native:text class="text-sm text-theme-destructive">{{ $message }}</native:text>
                @enderror

                <native:button
                    label="Adicionar Duelista (Máx. {{ $maxPlayers }})"
                    icon="plus"
                    variant="secondary"
                    class="w-full"
                    :disabled="count($playerNames) >= $maxPlayers"
                    @press="addPlayer"
                />
            </native:column>

            <native:button
                label="Iniciar Arena de Duelo"
                icon="play"
                variant="primary"
                size="lg"
                class="w-full"
                @press="start"
            />
        </native:column>
    </native:scroll-view>
</native:column>
