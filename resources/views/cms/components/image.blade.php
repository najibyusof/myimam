@php
    $imageUrl = $props['image_url'] ?? null;
    $alt = $props['alt'] ?? 'Image';
    $align = $props['align'] ?? 'center';
    $padding = $props['padding'] ?? '16px';
    $margin = $props['margin'] ?? '0';
@endphp

@if ($imageUrl)
    <section class="mx-auto max-w-5xl"
        style="padding: {{ $padding }}; margin: {{ $margin }}; text-align: {{ $align }};">
        <div
            class="overflow-hidden rounded-[1.2rem] border border-white/80 bg-white/70 p-2 shadow-[0_28px_70px_-36px_rgba(15,23,42,0.45)] backdrop-blur sm:rounded-[2rem] sm:p-3">
            <img src="{{ $imageUrl }}" alt="{{ $alt }}"
                class="mx-auto w-full rounded-[0.95rem] object-cover sm:rounded-[1.5rem]" />
        </div>
    </section>
@endif
