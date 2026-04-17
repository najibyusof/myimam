@php
    $title = $props['title'] ?? 'Selamat Datang';
    $subtitle = $props['subtitle'] ?? null;
    $buttonText = $props['button_text'] ?? null;
    $buttonLink = $props['button_link'] ?? '#';
    $align = $props['align'] ?? 'left';
    $padding = $props['padding'] ?? '64px 24px';
    $margin = $props['margin'] ?? '0';
    $color = $props['color'] ?? '#0f172a';
    $alignmentClass = match ($align) {
        'center' => 'items-center text-center',
        'right' => 'items-end text-right',
        default => 'items-start text-left',
    };
@endphp

<section class="w-full"
    style="padding: {{ $padding }}; margin: {{ $margin }}; text-align: {{ $align }}; color: {{ $color }};">
    <div
        class="relative mx-auto max-w-6xl overflow-hidden rounded-[1.5rem] border border-white/35 bg-gradient-to-br from-sky-950 via-cyan-900 to-emerald-700 px-4 py-10 shadow-[0_28px_70px_-32px_rgba(15,23,42,0.6)] sm:rounded-[1.75rem] sm:px-8 sm:py-14 lg:rounded-[2rem] lg:px-10 lg:py-20">
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(255,255,255,0.2),_transparent_30%),radial-gradient(circle_at_bottom_left,_rgba(251,191,36,0.24),_transparent_22%)]">
        </div>
        <div class="relative flex {{ $alignmentClass }} gap-3 sm:gap-5">
            <span
                class="inline-flex rounded-full border border-white/15 bg-white/10 px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-cyan-100/95 sm:px-4 sm:text-[11px] sm:tracking-[0.28em]">
                MyImam CMS Experience
            </span>
        </div>

        <div class="relative mt-5 flex flex-col {{ $alignmentClass }} sm:mt-6">
            <h1
                class="max-w-4xl text-3xl font-black leading-[1.08] tracking-tight text-white sm:text-5xl sm:leading-[1.04] lg:text-6xl">
                {{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-4 max-w-3xl text-sm leading-7 text-cyan-50/88 sm:mt-5 sm:text-lg sm:leading-8 lg:text-xl">
                    {{ $subtitle }}</p>
            @endif

            @if ($buttonText)
                <a href="{{ $buttonLink }}"
                    class="mt-7 inline-flex rounded-2xl border border-white/70 bg-white px-5 py-2.5 text-xs font-semibold text-sky-950 shadow-lg shadow-slate-950/10 transition hover:-translate-y-0.5 hover:bg-cyan-50 sm:mt-8 sm:px-6 sm:py-3 sm:text-sm">
                    {{ $buttonText }}
                </a>
            @endif
        </div>
    </div>
</section>
