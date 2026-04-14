<!-- Button Component -->
@props([
    'type' => 'primary',
    'buttonType' => 'button',
    'size' => 'md',
    'icon' => null,
    'iconPosition' => 'left',
    'disabled' => false,
    'loading' => false,
    'href' => null,
])

@php
    $typeClasses = match ($type) {
        'primary' => 'bg-indigo-600 hover:bg-indigo-700 text-white disabled:bg-indigo-400',
        'secondary' => 'bg-gray-200 hover:bg-gray-300 text-gray-900 disabled:bg-gray-100',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white disabled:bg-red-400',
        'success' => 'bg-green-600 hover:bg-green-700 text-white disabled:bg-green-400',
        'warning' => 'bg-yellow-600 hover:bg-yellow-700 text-white disabled:bg-yellow-400',
        'ghost' => 'bg-transparent hover:bg-gray-100 text-gray-700 border border-gray-300',
        'link' => 'bg-transparent text-indigo-600 hover:text-indigo-900 underline',
        default => 'bg-indigo-600 hover:bg-indigo-700 text-white',
    };

    $sizeClasses = match ($size) {
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-base',
        'lg' => 'px-6 py-3 text-lg',
        'xl' => 'px-8 py-4 text-xl',
        default => 'px-4 py-2 text-base',
    };

    $commonClasses =
        'inline-flex items-center justify-center rounded-lg font-medium transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500';
@endphp

@if ($href)
    <a href="{{ $href }}"
        {{ $attributes->merge(['class' => "{$commonClasses} {$typeClasses} {$sizeClasses}"]) }}>
        @if ($icon && $iconPosition === 'left')
            <span class="mr-2">{!! $icon !!}</span>
        @endif
        {{ $slot }}
        @if ($icon && $iconPosition === 'right')
            <span class="ml-2">{!! $icon !!}</span>
        @endif
    </a>
@else
    <button
        {{ $attributes->merge(['type' => $buttonType, 'class' => "{$commonClasses} {$typeClasses} {$sizeClasses}", 'disabled' => $disabled || $loading]) }}>
        @if ($loading)
            <svg class="animate-spin mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        @elseif($icon && $iconPosition === 'left')
            <span class="mr-2">{!! $icon !!}</span>
        @endif
        {{ $slot }}
        @if ($icon && $iconPosition === 'right' && !$loading)
            <span class="ml-2">{!! $icon !!}</span>
        @endif
    </button>
@endif
