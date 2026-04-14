<!-- Breadcrumb Component -->
@props(['items' => []])

<nav class="flex items-center space-x-2 mb-4" aria-label="Breadcrumb">
    @foreach ($items as $index => $item)
        @if ($index < count($items) - 1)
            <a href="{{ $item['url'] }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium transition">
                {{ $item['label'] }}
            </a>
            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                    clip-rule="evenodd" />
            </svg>
        @else
            <span class="text-gray-700 text-sm font-medium">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
