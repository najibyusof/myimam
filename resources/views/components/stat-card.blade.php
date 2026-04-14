<!-- Stat Card Component -->
@props(['icon' => null, 'title' => '', 'value' => '', 'subtitle' => '', 'trend' => null, 'color' => 'indigo'])

<div
    class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-200 p-6 border-l-4 border-{{ $color }}-500">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-gray-600 text-sm font-medium">{{ $title }}</p>
            <div class="mt-2">
                <p class="text-3xl font-bold text-gray-900">{{ $value }}</p>
                @if ($subtitle)
                    <p class="mt-1 text-xs text-gray-500">{{ $subtitle }}</p>
                @endif
            </div>
            @if ($trend)
                <div class="mt-3 flex items-center text-sm">
                    @if ($trend['direction'] === 'up')
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414-1.414L13.586 7H12z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="ml-1 text-green-600 font-semibold">{{ $trend['value'] }}%</span>
                    @else
                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M12 13a1 1 0 110 2H7a1 1 0 01-1-1V9a1 1 0 112 0v3.586l4.293-4.293a1 1 0 011.414 1.414L9.414 13H12z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="ml-1 text-red-600 font-semibold">{{ $trend['value'] }}%</span>
                    @endif
                    <span class="ml-2 text-gray-500">vs last month</span>
                </div>
            @endif
        </div>
        @if ($icon)
            <div class="w-16 h-16 rounded-full bg-{{ $color }}-100 flex items-center justify-center">
                {!! $icon !!}
            </div>
        @endif
    </div>
</div>
