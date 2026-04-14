<!-- Form Input Component -->
@props([
    'label' => '',
    'type' => 'text',
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'help' => null,
    'icon' => null,
])

<div class="space-y-2">
    @if ($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if ($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if ($icon)
            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                {!! $icon !!}
            </span>
        @endif

        @if ($type === 'textarea')
            <textarea id="{{ $name }}" name="{{ $name }}"
                {{ $attributes->merge([
                    'class' =>
                        'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed transition ' .
                        ($icon ? 'pl-10' : '') .
                        ($error ? 'border-red-500' : 'border-gray-300'),
                    'required' => $required,
                    'disabled' => $disabled,
                    'placeholder' => $placeholder,
                ]) }}>{{ $value }}</textarea>
        @elseif($type === 'select')
            <select id="{{ $name }}" name="{{ $name }}"
                {{ $attributes->merge([
                    'class' =>
                        'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed transition ' .
                        ($icon ? 'pl-10' : '') .
                        ($error ? 'border-red-500' : 'border-gray-300'),
                    'required' => $required,
                    'disabled' => $disabled,
                ]) }}>
                {{ $slot }}
            </select>
        @else
            <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
                value="{{ $value }}"
                {{ $attributes->merge([
                    'class' =>
                        'w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed transition ' .
                        ($icon ? 'pl-10' : '') .
                        ($error ? 'border-red-500' : 'border-gray-300'),
                    'required' => $required,
                    'disabled' => $disabled,
                    'placeholder' => $placeholder,
                ]) }} />
        @endif
    </div>

    @if ($help)
        <p class="text-sm text-gray-500">{{ $help }}</p>
    @endif

    @if ($error)
        <p class="text-sm text-red-500 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M18.101 12.93a1 1 0 00-1.414-1.414L10 16.586l-6.687-6.687a1 1 0 00-1.414 1.414l8.1 8.1a1 1 0 001.414 0l8.1-8.1z"
                    clip-rule="evenodd" />
            </svg>
            {{ $error }}
        </p>
    @endif
</div>
