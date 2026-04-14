<!-- Modal Component -->
@props(['id' => '', 'title' => '', 'size' => 'md', 'closeButton' => true])

@php
    $sizeClass = match ($size) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-md',
    };
@endphp

<div x-cloak x-show="modals['{{ $id }}']" @keydown.escape="modals['{{ $id }}'] = false"
    class="fixed inset-0 bg-black bg-opacity-50 z-40 flex items-center justify-center p-4">
    <div x-show="modals['{{ $id }}']" @click.away="modals['{{ $id }}'] = false"
        x-transition:enter="transition ease-in-out duration-150" x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        class="bg-white rounded-lg shadow-xl {{ $sizeClass }} w-full">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
            @if ($closeButton)
                <button @click="modals['{{ $id }}'] = false"
                    class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>

        <!-- Body -->
        <div class="px-6 py-4 max-h-96 overflow-y-auto">
            {{ $slot }}
        </div>

        <!-- Footer (optional) -->
        @if (isset($footer))
            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end space-x-3">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
