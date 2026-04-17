@php
    $title = $props['title'] ?? 'Borang Hubungi';
    $text = $props['text'] ?? 'Isikan maklumat anda.';
    $buttonText = $props['button_text'] ?? 'Hantar';
    $padding = $props['padding'] ?? '24px';
    $margin = $props['margin'] ?? '0';
@endphp

<section class="mx-auto max-w-3xl" style="padding: {{ $padding }}; margin: {{ $margin }};">
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-bold text-slate-900">{{ $title }}</h3>
        <p class="mt-2 text-sm text-slate-600">{{ $text }}</p>

        <form class="mt-5 space-y-3" action="javascript:void(0)">
            <input type="text" placeholder="Nama"
                class="w-full rounded-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
            <input type="email" placeholder="Email"
                class="w-full rounded-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
            <textarea rows="4" placeholder="Mesej"
                class="w-full rounded-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            <button type="button"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                {{ $buttonText }}
            </button>
        </form>
    </div>
</section>
