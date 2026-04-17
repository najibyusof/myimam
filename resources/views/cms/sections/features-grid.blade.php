@php
    $title = (string) ($props['title'] ?? 'Ciri-Ciri Utama');
    $subtitle = (string) ($props['subtitle'] ?? 'Modul penting untuk operasi kewangan masjid.');
    $items = is_array($props['items'] ?? null) ? $props['items'] : [];
@endphp

<section class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-slate-900">{{ $title }}</h2>
            <p class="mt-3 text-base text-slate-600">{{ $subtitle }}</p>
        </div>

        <div class="mt-10 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach ($items as $item)
                @php
                    $icon = (string) ($item['icon'] ?? 'sparkles');
                    $featureTitle = (string) ($item['title'] ?? 'Ciri');
                    $desc = (string) ($item['desc'] ?? 'Deskripsi ciri.');
                @endphp
                <article class="rounded-xl border border-slate-200 bg-white p-6 shadow-md">
                    <div
                        class="h-10 w-10 rounded-lg bg-cyan-100 text-cyan-700 grid place-items-center text-xs font-bold uppercase">
                        {{ strtoupper(substr($icon, 0, 2)) }}
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $featureTitle }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $desc }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
