@php
    $text = $props['text'] ?? '';
    $align = $props['align'] ?? 'left';
    $padding = $props['padding'] ?? '24px';
    $margin = $props['margin'] ?? '0';
    $color = $props['color'] ?? '#334155';
@endphp

<section class="mx-auto max-w-5xl"
    style="padding: {{ $padding }}; margin: {{ $margin }}; text-align: {{ $align }}; color: {{ $color }};">
    <div
        class="rounded-[1.2rem] border border-white/80 bg-white/90 p-5 shadow-[0_18px_45px_-30px_rgba(15,23,42,0.4)] backdrop-blur sm:rounded-[1.5rem] sm:p-7">
        <p class="text-sm leading-7 text-slate-600 sm:text-base sm:leading-8 lg:text-[1.05rem]">{{ $text }}</p>
    </div>
</section>
