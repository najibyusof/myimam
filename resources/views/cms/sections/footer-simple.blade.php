@php
    $brand = (string) ($props['brand'] ?? 'MyImam');
    $links = is_array($props['links'] ?? null) ? $props['links'] : [];
@endphp

<footer class="py-12 border-t border-slate-200 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-semibold text-slate-900">{{ $brand }}</p>
            <nav class="flex flex-col sm:flex-row gap-3 sm:gap-6">
                @foreach ($links as $link)
                    @php
                        $text = (string) ($link['text'] ?? 'Link');
                        $url = (string) ($link['link'] ?? '#');
                    @endphp
                    <a href="{{ $url }}"
                        class="text-sm text-slate-600 hover:text-slate-900 transition">{{ $text }}</a>
                @endforeach
            </nav>
        </div>
    </div>
</footer>
