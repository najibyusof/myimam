<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenantMasjid?->nama ? $tenantMasjid->nama . ' - ' : '' }}Sistem Pengurusan Kewangan Masjid</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-stone-50 font-sans text-gray-900 antialiased">
    <div
        class="relative overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.18),_transparent_30%),linear-gradient(135deg,_#0f172a,_#1e293b_45%,_#172554)]">
        <div class="absolute inset-0 opacity-30">
            <div class="absolute left-0 top-12 h-48 w-48 rounded-full bg-amber-300 blur-3xl"></div>
            <div class="absolute right-10 top-24 h-64 w-64 rounded-full bg-sky-300 blur-3xl"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 pb-24 pt-8 sm:px-6 lg:px-8">
            <header class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-amber-200">
                        {{ $tenantMasjid?->nama ? 'Tenant Landing Page' : 'Global Landing Page' }}
                    </p>
                    <h1 class="mt-2 text-xl font-bold text-white">{{ $tenantMasjid?->nama ?? 'Masjid Finance System' }}
                    </h1>
                    @if ($tenantMasjid)
                        <p class="mt-1 text-sm text-slate-200">
                            Kandungan dimuatkan untuk tenant {{ $tenantMasjid->code ?? $tenantMasjid->id }}
                            @if ($tenantSource)
                                melalui {{ $tenantSource }}.
                            @endif
                        </p>
                    @else
                        <p class="mt-1 text-sm text-slate-200">Template global sedang dipaparkan.</p>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="rounded-lg border border-white/30 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="rounded-lg border border-white/30 px-4 py-2 text-sm font-semibold text-white transition hover:bg-white/10">
                            Login
                        </a>
                    @endauth
                </div>
            </header>

            <section class="mt-16 grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-200">Sistem Pengurusan
                        Kewangan Masjid</p>
                    <h2 class="mt-4 text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                        {{ $landing['hero_title'] }}
                    </h2>
                    @if (!empty($landing['hero_subtitle']))
                        <p class="mt-5 max-w-xl text-base text-slate-100 sm:text-lg">
                            {{ $landing['hero_subtitle'] }}
                        </p>
                    @endif
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center rounded-xl bg-amber-300 px-5 py-3 text-sm font-semibold text-slate-950 shadow-lg shadow-slate-950/30 transition hover:bg-amber-200">
                            {{ $landing['hero_cta_text'] ?: 'Login' }}
                        </a>
                        <a href="#features"
                            class="inline-flex items-center rounded-xl border border-slate-200/40 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                            Lihat Ciri
                        </a>
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-white/15 bg-white/10 p-6 backdrop-blur-xl shadow-2xl shadow-slate-950/25">
                    @if (!empty($landing['hero_image']))
                        <div class="mb-5 overflow-hidden rounded-2xl border border-white/10 bg-slate-900/40">
                            <img src="{{ $landing['hero_image'] }}" alt="Hero image"
                                class="h-56 w-full object-cover" />
                        </div>
                    @endif

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
                        @foreach (collect($landing['features'])->take(4) as $feature)
                            <div class="rounded-2xl bg-white/10 p-4 text-white ring-1 ring-white/10">
                                <p class="text-xs uppercase tracking-wider text-amber-200">Feature</p>
                                <p class="mt-1 font-semibold">{{ $feature }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
    </div>

    <section id="features" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="mb-10 text-center">
            <h3 class="text-3xl font-bold text-slate-900">Features</h3>
            <p class="mt-2 text-slate-500">Kandungan ini dimuatkan secara dinamik dengan fallback global jika tenant
                belum override.</p>
        </div>

        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($landing['features'] as $feature)
                <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm shadow-stone-200/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700">•
                    </div>
                    <h4 class="mt-4 font-semibold text-slate-900">{{ $feature }}</h4>
                    <p class="mt-2 text-sm text-slate-600">Disusun melalui CMS dan boleh diubah untuk template global
                        atau tenant tertentu.</p>
                </article>
            @endforeach
        </div>
    </section>

    <footer class="border-t border-stone-200 bg-white">
        <div
            class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-8 text-sm text-slate-500 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <p>{{ $landing['footer_text'] }}</p>
            <div class="flex items-center gap-4">
                <a href="#features" class="hover:text-slate-700">Features</a>
                <a href="{{ route('login') }}" class="hover:text-slate-700">Login</a>
                @if ($tenantMasjid)
                    <span class="text-slate-400">Tenant: {{ $tenantMasjid->code ?? $tenantMasjid->id }}</span>
                @endif
            </div>
        </div>
    </footer>
</body>

</html>
