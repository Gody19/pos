@props([
    'title' => null,
    'subtitle' => null,
    'footer' => null,
    'padding' => 'p-6',
])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title)
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-6 py-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
                @if ($subtitle)
                    <p class="mt-0.5 text-sm text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="shrink-0">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $title ? $padding : $padding }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t border-gray-100 px-6 py-4">{{ $footer }}</div>
    @endisset
</div>
