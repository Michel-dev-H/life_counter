<native:column class="w-full h-full bg-theme-background">
    <native:image :src="$url" class="w-full h-full" />

    <native:column class="absolute inset-0 bg-theme-on-background opacity-{{ (int) round($scrim * 100) }}" />
</native:column>
