@props(['placement'])

@php
    $items = [
        ['label' => 'Início', 'route' => 'home', 'active' => ['home'], 'icon' => 'home'],
        ['label' => 'Nova partida', 'route' => 'duel.setup', 'active' => ['duel.setup'], 'icon' => 'plus'],
        ['label' => 'Aparência', 'route' => 'appearance.edit', 'active' => ['appearance.edit'], 'icon' => 'settings'],
    ];
@endphp

<nav
    aria-label="Navegação principal"
    @class([
        'flex items-center justify-around px-2 py-2' => $placement === 'mobile',
        'flex flex-col gap-1' => $placement === 'sidebar',
    ])
>
    @foreach ($items as $item)
        @php($isActive = request()->routeIs(...$item['active']))

        <a
            href="{{ route($item['route']) }}"
            @if ($isActive) aria-current="page" @endif
            @class([
                'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors' => $placement === 'sidebar',
                'flex min-w-0 flex-1 flex-col items-center gap-1 rounded-xl px-1 py-1.5 text-[0.7rem] font-medium transition-colors' => $placement === 'mobile',
                'bg-primary-container/10 text-primary' => $isActive,
                'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' => ! $isActive,
            ])
        >
            <x-icon :name="$item['icon']" class="size-5 shrink-0" />
            <span class="truncate">{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
