<!-- Summary Card Component -->
@props(['icon' => null, 'title' => '', 'items' => [], 'color' => 'blue', 'action' => null])

<div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200 overflow-hidden">
    <!-- Header -->
    <div
        class="bg-gradient-to-r from-{{ $color }}-500 to-{{ $color }}-600 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            @if ($icon)
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    {!! $icon !!}
                </div>
            @endif
            <h3 class="text-lg font-semibold text-white">{{ $title }}</h3>
        </div>
        @if ($action)
            <a href="{{ $action['url'] }}"
                class="text-white hover:bg-white hover:bg-opacity-20 px-3 py-1 rounded transition">
                {{ $action['label'] }}
            </a>
        @endif
    </div>

    <!-- Body -->
    <div class="p-6">
        @if ($items)
            <ul class="space-y-3">
                @foreach ($items as $item)
                    <li class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                        <span class="text-gray-700">{{ $item['label'] }}</span>
                        <span class="font-semibold text-gray-900">{{ $item['value'] }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            {{ $slot }}
        @endif
    </div>
</div>
