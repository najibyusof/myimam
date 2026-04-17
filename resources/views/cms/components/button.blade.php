@php
    $buttonText = $props['button_text'] ?? 'Klik';
    $buttonLink = $props['button_link'] ?? '#';
    $align = $props['align'] ?? 'left';
    $padding = $props['padding'] ?? '8px';
    $margin = $props['margin'] ?? '0';
    $color = $props['color'] ?? '#2563eb';
@endphp

<section class="mx-auto max-w-5xl"
    style="padding: {{ $padding }}; margin: {{ $margin }}; text-align: {{ $align }};">
    <a href="{{ $buttonLink }}"
        class="inline-flex rounded-2xl px-4 py-2.5 text-xs font-semibold text-white shadow-[0_14px_30px_-18px_rgba(37,99,235,0.7)] transition hover:-translate-y-0.5 sm:px-5 sm:py-3 sm:text-sm"
        style="background-color: {{ $color }};">
        {{ $buttonText }}
    </a>
</section>
