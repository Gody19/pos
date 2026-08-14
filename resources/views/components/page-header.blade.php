@props([
    'title',
    'description' => null,
    'crumbs' => [],
])

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        @if ($crumbs)
            <nav class="mb-1 text-xs text-gray-400" aria-label="Breadcrumb">
                <ol class="flex items-center gap-1">
                    <li><a href="{{ route('dashboard') }}" class="hover:text-brand-600">Dashboard</a></li>
                    @foreach ($crumbs as $label => $url)
                        <li class="flex items-center gap-1">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                            @if (is_string($url) && $url)
                                <a href="{{ $url }}" class="hover:text-brand-600">{{ $label }}</a>
                            @else
                                <span class="text-gray-600">{{ $label }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif
        <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>