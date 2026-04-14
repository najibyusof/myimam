<!-- Checkbox Input Component -->
@props(['label' => '', 'name' => '', 'value' => '1', 'checked' => false, 'disabled' => false, 'error' => null])

<div class="flex items-center">
    <input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="{{ $value }}"
        {{ $checked ? 'checked' : '' }} {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 focus:ring-2 disabled:bg-gray-100 disabled:cursor-not-allowed transition']) }} />
    @if ($label)
        <label for="{{ $name }}" class="ml-3 text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif
    @if ($error)
        <p class="ml-3 text-sm text-red-500">{{ $error }}</p>
    @endif
</div>
