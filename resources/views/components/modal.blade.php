@props([
    'id' => null,
    'title' => null,
    'maxWidth' => 'max-w-lg',
])

<div
    id="{{ $id }}"
    class="fixed inset-0 z-50 hidden items-center justify-center p-4"
    aria-modal="true"
    role="dialog"
>
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" data-close-modal></div>
    <div class="relative z-10 w-full {{ $maxWidth }} card max-h-[90vh] overflow-y-auto">
        @if ($title)
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" data-close-modal>
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
        <div class="p-6">{{ $slot }}</div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('[data-close-modal]').forEach((el) => {
        el.addEventListener('click', () => {
            el.closest('.fixed.inset-0').classList.add('hidden');
            el.closest('.fixed.inset-0').classList.remove('flex');
        });
    });
</script>
@endpush