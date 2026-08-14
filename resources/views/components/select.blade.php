@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
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

    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $attributes->class(['input', 'border-red-300 focus:border-red-500 focus:ring-red-500' => $error]) }}
    >
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $key => $option)
            <option value="{{ $key }}" @selected((string) old($name, $selected) === (string) $key)>
                {{ $option }}
            </option>
        @endforeach
    </select>

    @if ($hint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>