<!-- Radio Input Component -->
@props(['label' => '', 'name' => '', 'value' => '', 'checked' => false, 'disabled' => false])

<div class="flex items-center">
    <input type="radio" id="{{ $name }}_{{ $value }}" name="{{ $name }}"
        value="{{ $value }}" {{ $checked ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'h-4 w-4 rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-2 disabled:bg-gray-100 disabled:cursor-not-allowed transition']) }} />
    @if ($label)
        <label for="{{ $name }}_{{ $value }}" class="ml-3 text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif
</div>
