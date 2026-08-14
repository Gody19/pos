@props([
    'name',
    'label' => null,
    'value' => null,
    'rows' => 3,
    'placeholder' => null,
    'hint' => null,
])

@php
    $error = $errors->first($name);
@endphp

<div>
    @if ($label)
        <label for="{{ $name }}" class="label">{{ $label }}</label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $attributes->class(['input', 'border-red-300 focus:border-red-500 focus:ring-red-500' => $error]) }}
    >{{ old($name, $value) }}</textarea>

    @if ($hint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>