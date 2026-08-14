@props([
    'label',
    'value',
    'icon' => null,
    'color' => 'brand',
    'sub' => null,
])

@php
    $colors = [
        'brand' => 'bg-brand-50 text-brand-600',
        'green' => 'bg-emerald-50 text-emerald-600',
        'red' => 'bg-red-50 text-red-600',
        'yellow' => 'bg-amber-50 text-amber-600',
        'blue' => 'bg-blue-50 text-blue-600',
        'purple' => 'bg-purple-50 text-purple-600',
        'gray' => 'bg-gray-100 text-gray-600',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'card flex items-center gap-4 p-5']) }}>
    @if ($icon)
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $colors[$color] }}">
            {!! $icon !!}
        </div>
    @endif
    <div class="min-w-0">
        <p class="truncate text-sm font-medium text-gray-500">{{ $label }}</p>
        <p class="text-xl font-bold text-gray-900">{{ $value }}</p>
        @if ($sub)
            <p class="mt-0.5 text-xs text-gray-400">{{ $sub }}</p>
        @endif
    </div>
</div>