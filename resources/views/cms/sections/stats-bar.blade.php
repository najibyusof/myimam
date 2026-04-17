@php
    $items = is_array($props['items'] ?? null) ? $props['items'] : [];
@endphp

<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($items as $item)
                @php
                    $value = (string) ($item['value'] ?? '-');
                    $label = (string) ($item['label'] ?? 'Statistik');
                @endphp
                <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-md text-center">
                    <p class="text-3xl font-bold text-slate-900">{{ $value }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $label }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
