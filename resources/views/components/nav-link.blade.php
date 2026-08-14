@props([
    'route' => null,
    'href' => null,
    'current' => '',
    'label',
    'badge' => null,
])

@php
    $url = $href ?? ($route ? route($route) : '#');
    $isActive = $route && $current === $route;
    $activeClass = $isActive ? 'bg-gray-800 text-white' : 'text-gray-400 hover:bg-gray-800/60 hover:text-gray-100';
@endphp

<a href="{{ $url }}" {{ $attributes->class(['group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition', $activeClass]) }}>
    <span class="shrink-0">{{ $slot }}</span>
    <span class="flex-1 truncate">{{ $label }}</span>
    @if ($badge)
        <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-xs font-semibold text-amber-400">{{ $badge }}</span>
    @endif
</a>