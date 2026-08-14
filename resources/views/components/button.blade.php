@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
])

@php
    $sizes = [
        'xs' => 'px-2.5 py-1.5 text-xs',
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
    ];
    $classes = "btn-{$variant} {$sizes[$size]} inline-flex items-center justify-center gap-2";
    $attributes = $attributes->class($classes);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes }}>{{ $slot }}</button>
@endif