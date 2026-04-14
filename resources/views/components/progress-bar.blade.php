<!-- Progress Bar Component -->
@props(['percentage' => 0, 'label' => '', 'color' => 'indigo', 'size' => 'md', 'showPercentage' => true])

@php
    $colorClass = match ($color) {
        'indigo' => 'bg-indigo-600',
        'green' => 'bg-green-600',
        'red' => 'bg-red-600',
        'yellow' => 'bg-yellow-600',
        'blue' => 'bg-blue-600',
        default => 'bg-indigo-600',
    };

    $heightClass = match ($size) {
        'sm' => 'h-1',
        'md' => 'h-2',
        'lg' => 'h-3',
        default => 'h-2',
    };
@endphp

<div>
    @if ($label)
        <div class="flex items-center justify-between mb-2">
            <label class="text-sm font-medium text-gray-700">{{ $label }}</label>
            @if ($showPercentage)
                <span class="text-sm font-semibold text-gray-900">{{ $percentage }}%</span>
            @endif
        </div>
    @endif

    <div class="w-full bg-gray-200 rounded-full overflow-hidden {{ $heightClass }}">
        <div class="{{ $colorClass }} {{ $heightClass }} rounded-full transition-all duration-300"
            style="width: {{ $percentage }}%"></div>
    </div>
</div>
