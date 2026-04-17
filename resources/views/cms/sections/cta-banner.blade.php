@php
    $title = (string) ($props['title'] ?? 'Mulakan Sekarang');
    $subtitle = (string) ($props['subtitle'] ?? 'Daftar untuk mula menggunakan platform.');
    $button = is_array($props['button'] ?? null) ? $props['button'] : [];
    $buttonText = (string) ($button['text'] ?? 'Daftar Masjid Anda');
    $buttonLink = (string) ($button['link'] ?? '/login');
@endphp

<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-slate-900 px-6 py-10 sm:px-10 sm:py-12 shadow-md">
            <div class="grid gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-bold text-white">{{ $title }}</h2>
                    <p class="mt-2 text-sm sm:text-base text-slate-200">{{ $subtitle }}</p>
                </div>
                <div>
                    <a href="{{ $buttonLink }}"
                        class="inline-flex w-full sm:w-auto justify-center rounded-xl bg-cyan-400 px-5 py-3 text-sm font-semibold text-slate-900 hover:bg-cyan-300 transition">
                        {{ $buttonText }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
