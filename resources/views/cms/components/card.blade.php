@php
    $title = $props['title'] ?? null;
    $text = $props['text'] ?? null;
    $padding = $props['padding'] ?? '24px';
    $margin = $props['margin'] ?? '0';
    $color = $props['color'] ?? '#0f172a';
    $variantPool = ['emerald', 'indigo', 'amber'];
    $variant = $props['variant'] ?? $variantPool[abs(crc32((string) ($title . '|' . $text))) % count($variantPool)];

    $variantClasses = [
        'emerald' => [
            'container' => 'border-emerald-100/90 bg-emerald-50/55',
            'title' => 'text-emerald-950',
            'accent' => 'from-emerald-400/70 via-cyan-400/55 to-transparent',
        ],
        'indigo' => [
            'container' => 'border-indigo-100/90 bg-indigo-50/55',
            'title' => 'text-indigo-950',
            'accent' => 'from-indigo-400/70 via-sky-400/55 to-transparent',
        ],
        'amber' => [
            'container' => 'border-amber-100/90 bg-amber-50/55',
            'title' => 'text-amber-950',
            'accent' => 'from-amber-400/75 via-orange-400/55 to-transparent',
        ],
    ];

    $activeVariant = $variantClasses[$variant] ?? $variantClasses['indigo'];
@endphp

<section class="mx-auto max-w-5xl"
    style="padding: {{ $padding }}; margin: {{ $margin }}; color: {{ $color }};">
    <div
        class="relative overflow-hidden rounded-[1.3rem] border p-5 shadow-[0_22px_55px_-38px_rgba(15,23,42,0.45)] backdrop-blur sm:rounded-[1.6rem] sm:p-7 {{ $activeVariant['container'] }}">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $activeVariant['accent'] }}">
        </div>

        @if ($title)
            <h3 class="text-lg font-bold tracking-tight sm:text-xl {{ $activeVariant['title'] }}">{{ $title }}
            </h3>
        @endif

        @if ($text)
            <p class="mt-3 text-sm leading-7 text-slate-600 sm:text-[0.98rem]">{{ $text }}</p>
        @endif

        @if (!empty($props['_children_html']))
            <div class="mt-5">{!! $props['_children_html'] !!}</div>
        @endif
    </div>
</section>
