<native:column class="w-full h-full">
    <native:scroll-view class="w-full flex-1">
        <native:column class="w-full gap-5 p-4">

            <native:column class="w-full gap-2">
                <native:text class="font-bold uppercase text-theme-primary">Tema</native:text>

                @foreach ($themes as $index => $theme)
                    <native:button
                        label="{{ $theme->label() }}"
                        variant="{{ $selectedTheme === $theme->value ? 'primary' : 'secondary' }}"
                        class="w-full"
                        a11y-label="{{ $theme->description() }}"
                        @press="selectTheme({{ $index }})"
                    />
                @endforeach
            </native:column>

            <native:column class="w-full gap-2">
                <native:text class="font-bold uppercase text-theme-primary">Papel de parede</native:text>

                @if ($wallpaperUrl !== '')
                    <native:image :src="$wallpaperUrl" class="w-full h-48 rounded-xl" />

                    <native:button
                        label="Remover papel de parede"
                        icon="trash"
                        variant="ghost"
                        class="w-full"
                        @press="confirmRemoveWallpaper"
                    />
                @else
                    <native:text class="text-theme-on-surface-variant">
                        Nenhum papel de parede escolhido.
                    </native:text>
                @endif

                <native:button
                    label="Escolher imagem do dispositivo"
                    icon="photo"
                    variant="secondary"
                    class="w-full"
                    @press="pickWallpaper"
                />

                @if ($wallpaperError !== '')
                    <native:text class="text-sm text-theme-destructive">{{ $wallpaperError }}</native:text>
                @endif
            </native:column>

            <native:column class="w-full gap-2">
                <native:text class="font-bold uppercase text-theme-primary">Escurecimento do fundo</native:text>

                <native:slider
                    class="w-full"
                    :value="$scrimOpacity"
                    :min="0"
                    :max="0.8"
                    :step="0.05"
                    @change="updateScrimOpacity"
                />

                <native:text class="text-theme-on-surface-variant">
                    {{ (int) round($scrimOpacity * 100) }}%
                </native:text>
            </native:column>
        </native:column>
    </native:scroll-view>

    <native:modal :visible="$showRemoveConfirm" @dismiss="cancelRemoveWallpaper">
        <native:column class="w-full gap-3 p-4">
            <native:text class="text-lg font-bold">Remover o papel de parede?</native:text>
            <native:text class="text-theme-on-surface-variant">
                A imagem será apagada do dispositivo.
            </native:text>

            <native:row class="w-full gap-2">
                <native:button label="Cancelar" variant="ghost" class="flex-1" @press="cancelRemoveWallpaper" />
                <native:button label="Apagar imagem" variant="destructive" class="flex-1" @press="removeWallpaper" />
            </native:row>
        </native:column>
    </native:modal>
</native:column>
