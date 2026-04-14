<!-- Badge Component -->
@props(['type' => 'gray', 'size' => 'md', 'dot' => false])

@php
    $typeClasses = match ($type) {
        'success' => 'bg-green-100 text-green-800',
        'error' => 'bg-red-100 text-red-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'info' => 'bg-blue-100 text-blue-800',
        'purple' => 'bg-purple-100 text-purple-800',
        'pink' => 'bg-pink-100 text-pink-800',
        'indigo' => 'bg-indigo-100 text-indigo-800',
        'gray' => 'bg-gray-100 text-gray-800',
        default => 'bg-gray-100 text-gray-800',
    };

    $sizeClasses = match ($size) {
        'sm' => 'px-2 py-1 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-2 text-base',
        default => 'px-3 py-1 text-sm',
    };
@endphp

<span class="inline-flex items-center {{ $sizeClasses }} rounded-full font-medium {{ $typeClasses }}">
    @if ($dot)
        <span class="mr-2 h-2 w-2 rounded-full"
            :class="{
                'bg-green-600': '{{ $type }}'
                === 'success',
                'bg-red-600': '{{ $type }}'
                === 'error',
                'bg-yellow-600': '{{ $type }}'
                === 'warning',
                'bg-blue-600': '{{ $type }}'
                === 'info',
                'bg-gray-600': true
            }"></span>
    @endif
    {{ $slot }}
</span>
