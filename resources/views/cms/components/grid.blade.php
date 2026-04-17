@php
    $columns = max(1, min(4, (int) ($props['columns'] ?? 3)));
    $rawItems = $props['items'] ?? '';
    $items = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $rawItems) ?: [])));
    $padding = $props['padding'] ?? '16px';
    $margin = $props['margin'] ?? '0';
@endphp

<section class="mx-auto max-w-6xl" style="padding: {{ $padding }}; margin: {{ $margin }};">
    <div class="grid gap-4 lg:gap-5" style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr));">
        @foreach ($items as $index => $item)
            @php
                $variants = [
                    [
                        'container' => 'border-slate-200/80 bg-white/95',
                        'bar' => 'from-amber-400 via-cyan-500 to-emerald-500',
                    ],
                    [
                        'container' => 'border-indigo-100/80 bg-indigo-50/70',
                        'bar' => 'from-indigo-400 via-sky-500 to-cyan-500',
                    ],
                    [
                        'container' => 'border-emerald-100/80 bg-emerald-50/70',
                        'bar' => 'from-emerald-400 via-teal-500 to-cyan-500',
                    ],
                ];
                $active = $variants[$index % count($variants)];
            @endphp
            <article
                class="group relative overflow-hidden rounded-[1.2rem] border p-4 shadow-[0_18px_48px_-34px_rgba(15,23,42,0.35)] transition hover:-translate-y-1 hover:shadow-[0_24px_60px_-30px_rgba(15,23,42,0.4)] sm:rounded-[1.5rem] sm:p-5 {{ $active['container'] }}">
                <div
                    class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $active['bar'] }} opacity-80">
                </div>
                <p class="mt-2 text-sm font-semibold leading-7 text-slate-700 sm:mt-3">{{ $item }}</p>
            </article>
        @endforeach
    </div>

    @if (!empty($props['_children_html']))
        <div class="mt-4">{!! $props['_children_html'] !!}</div>
    @endif
</section>
