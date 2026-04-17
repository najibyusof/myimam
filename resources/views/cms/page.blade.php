<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seoTitle ?? ($pageTitle ?? config('app.name', 'MyImam')) }}</title>
    @if (!empty($seoDescription))
        <meta name="description" content="{{ $seoDescription }}">
    @endif
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="{{ $bodyClass ?? 'min-h-screen bg-[radial-gradient(circle_at_top,_rgba(15,118,110,0.14),_transparent_35%),linear-gradient(180deg,_#f8fafc_0%,_#eef4f7_45%,_#f8fafc_100%)] text-slate-900 antialiased' }}">
    <div class="relative overflow-hidden">
        <div
            class="pointer-events-none absolute inset-x-0 top-0 h-80 bg-[radial-gradient(circle_at_top,_rgba(251,191,36,0.18),_transparent_40%)]">
        </div>
        <div class="pointer-events-none absolute right-0 top-24 h-64 w-64 rounded-full bg-emerald-200/20 blur-3xl">
        </div>
        <div class="pointer-events-none absolute left-0 top-[28rem] h-72 w-72 rounded-full bg-sky-200/25 blur-3xl">
        </div>

        <main
            class="relative mx-auto flex min-h-screen max-w-7xl flex-col gap-6 px-3 py-5 sm:gap-8 sm:px-6 sm:py-6 lg:px-8 lg:py-10">
            @if (!empty($tenantMasjid))
                <div
                    class="mx-auto flex w-full max-w-6xl flex-col items-start justify-between gap-3 rounded-2xl border border-white/80 bg-white/75 px-4 py-3 text-sm shadow-[0_18px_45px_-34px_rgba(15,23,42,0.35)] backdrop-blur sm:flex-row sm:items-center">
                    <div class="flex items-center gap-3">
                        <span
                            class="inline-flex rounded-full bg-slate-900 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-white">
                            {{ $tenantSource ?: 'tenant' }}
                        </span>
                        <div>
                            <p class="font-semibold text-slate-900">{{ $tenantMasjid->nama }}</p>
                            @if (!empty($tenantMasjid->code))
                                <p class="text-xs uppercase tracking-[0.22em] text-slate-500">{{ $tenantMasjid->code }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('landing') }}"
                        class="text-xs font-semibold text-cyan-800 transition hover:text-cyan-950">
                        MyImam Public View
                    </a>
                </div>
            @endif

            {!! $renderedHtml !!}
        </main>
    </div>
</body>

</html>
