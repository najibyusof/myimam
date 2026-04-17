@php
    $style = (string) ($props['style'] ?? 'gradient');
    $badge = (string) ($props['badge'] ?? 'SaaS Kewangan Masjid');
    $title = (string) ($props['title'] ?? 'Platform Kewangan Masjid Berbilang Cawangan');
    $subtitle = (string) ($props['subtitle'] ?? 'Urus kewangan masjid dengan lebih moden dan telus.');
    $primary = is_array($props['primary_cta'] ?? null) ? $props['primary_cta'] : [];
    $secondary = is_array($props['secondary_cta'] ?? null) ? $props['secondary_cta'] : [];
    $primaryText = (string) ($primary['text'] ?? 'Daftar Masjid Anda');
    $primaryLink = (string) ($primary['link'] ?? '/login');
    $secondaryText = (string) ($secondary['text'] ?? 'Lihat Demo');
    $secondaryLink = (string) ($secondary['link'] ?? '/login');
    $image = (string) ($props['image'] ?? '/cms/defaults/landing-premium.svg');

    $containerClass = 'rounded-3xl shadow-md overflow-hidden';
    $heroClass = 'grid gap-8 lg:grid-cols-2 items-center p-6 sm:p-10 lg:p-12';
    $badgeClass = 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide';
    $titleClass = 'mt-4 text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight';
    $subtitleClass = 'mt-4 text-base sm:text-lg max-w-xl';
    $primaryClass = 'inline-flex w-full sm:w-auto justify-center rounded-xl px-5 py-3 text-sm font-semibold transition';
    $secondaryClass =
        'inline-flex w-full sm:w-auto justify-center rounded-xl border px-5 py-3 text-sm font-semibold transition';

    if ($style === 'soft') {
        $containerClass .= ' border border-cyan-100 bg-gradient-to-br from-cyan-50 via-white to-slate-100';
        $badgeClass .= ' bg-cyan-100 text-cyan-800';
        $titleClass .= ' text-slate-900';
        $subtitleClass .= ' text-slate-600';
        $primaryClass .= ' bg-slate-900 text-white hover:bg-slate-800';
        $secondaryClass .= ' border-slate-300 bg-white text-slate-700 hover:bg-slate-50';
    } elseif ($style === 'split-dark') {
        $containerClass .= ' border border-slate-700 bg-slate-900';
        $heroClass .= ' lg:grid-cols-[1.2fr_1fr]';
        $badgeClass .= ' bg-white/10 text-slate-100';
        $titleClass .= ' text-white';
        $subtitleClass .= ' text-slate-300';
        $primaryClass .= ' bg-cyan-400 text-slate-900 hover:bg-cyan-300';
        $secondaryClass .= ' border-white/35 bg-white/5 text-white hover:bg-white/10';
    } else {
        $containerClass .= ' bg-gradient-to-br from-slate-900 via-blue-900 to-cyan-800';
        $badgeClass .= ' bg-white/15 text-cyan-100';
        $titleClass .= ' text-white';
        $subtitleClass .= ' text-cyan-100';
        $primaryClass .= ' bg-white text-slate-900 shadow hover:bg-slate-100';
        $secondaryClass .= ' border-white/40 bg-white/5 text-white hover:bg-white/10';
    }
@endphp

<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="{{ $containerClass }}">
            <div class="{{ $heroClass }}">
                <div>
                    <span class="{{ $badgeClass }}">{{ $badge }}</span>
                    <h1 class="{{ $titleClass }}">
                        {{ $title }}</h1>
                    <p class="{{ $subtitleClass }}">{{ $subtitle }}</p>

                    <div class="mt-7 grid grid-cols-1 sm:flex sm:flex-wrap gap-3">
                        <a href="{{ $primaryLink }}" class="{{ $primaryClass }}">
                            {{ $primaryText }}
                        </a>
                        <a href="{{ $secondaryLink }}" class="{{ $secondaryClass }}">
                            {{ $secondaryText }}
                        </a>
                    </div>
                </div>

                <div class="lg:justify-self-end">
                    <div class="rounded-2xl bg-white/10 p-3 backdrop-blur shadow-md">
                        <img src="{{ $image }}" alt="Hero image"
                            class="w-full max-w-xl rounded-xl border border-white/15 object-cover">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
