<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Baucar Bayaran</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $baucarNo }}</p>
            </div>
            <div class="no-print flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.belanja.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Kembali
                </a>
                <a href="{{ route('baucar.pdf', ['belanja_id' => $belanja->id]) }}"
                    class="inline-flex items-center gap-1.5 rounded-md border border-red-300 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 shadow-sm hover:bg-red-100">
                    Baucar Rasmi (PDF)
                </a>
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Cetak
                </button>
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            .print-card {
                border: 0 !important;
                box-shadow: none !important;
            }

            @page {
                size: A4;
                margin: 12mm;
            }

            body {
                font-size: 11px;
            }
        }
    </style>

    <div class="py-8">
        <div class="mx-auto max-w-4xl space-y-4 px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="rounded-md bg-green-50 p-3 text-sm text-green-800 no-print">{{ session('status') }}</div>
            @endif
            @if (session('status_error'))
                <div class="rounded-md bg-red-50 p-3 text-sm text-red-800 no-print">{{ session('status_error') }}</div>
            @endif

            @if ($belanja->is_baucar_locked)
                <div
                    class="no-print rounded-xl border border-emerald-300 bg-emerald-50 p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-emerald-800">Baucar rasmi telah diluluskan dan dikunci.</p>
                        <p class="text-xs text-emerald-600 mt-0.5">
                            Dikunci oleh {{ $belanja->lockedBy->name ?? '-' }} pada
                            {{ optional($belanja->locked_at)->format('d/m/Y H:i') ?? '-' }}.
                        </p>
                    </div>
                    <span
                        class="inline-flex rounded-full bg-emerald-600 px-3 py-1 text-xs font-semibold text-white">LOCKED</span>
                </div>
            @elseif((int) $belanja->approval_step === 0)
                <div
                    class="no-print rounded-xl border border-amber-200 bg-amber-50 p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Langkah 1/2: Semakan Bendahari</p>
                        <p class="text-xs text-amber-600 mt-0.5">Bendahari perlu semak dan sahkan sebelum dihantar
                            kepada Pengerusi.</p>
                    </div>
                    <div class="flex gap-2">
                        @can('approveBendahari', $belanja)
                            <form method="POST" action="{{ route('baucar.approve', ['belanja_id' => $belanja->id]) }}">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                                    Sahkan Bendahari
                                </button>
                            </form>
                        @endcan
                        @can('reject', $belanja)
                            <button type="button"
                                onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                                class="inline-flex items-center gap-1.5 rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
                                Tolak
                            </button>
                        @endcan
                    </div>
                </div>
            @elseif((int) $belanja->approval_step === 1)
                <div
                    class="no-print rounded-xl border border-blue-200 bg-blue-50 p-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-blue-800">Langkah 2/2: Menunggu Kelulusan Pengerusi</p>
                        <p class="text-xs text-blue-600 mt-0.5">
                            Disahkan oleh Bendahari: {{ $belanja->bendahariLulusOleh->name ?? '-' }}
                            @if ($belanja->bendahari_lulus_pada)
                                &bull; {{ $belanja->bendahari_lulus_pada->format('d/m/Y H:i') }}
                            @endif
                        </p>
                    </div>
                    <div class="flex gap-2">
                        @can('approvePengerusi', $belanja)
                            <form method="POST" action="{{ route('baucar.approve', ['belanja_id' => $belanja->id]) }}">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-600">
                                    Luluskan Pengerusi
                                </button>
                            </form>
                        @endcan
                        @can('reject', $belanja)
                            <button type="button"
                                onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                                class="inline-flex items-center gap-1.5 rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
                                Tolak
                            </button>
                        @endcan
                    </div>
                </div>
            @endif

            @if ($belanja->catatan_tolak && !$belanja->is_baucar_locked)
                <div class="no-print rounded-xl border border-red-200 bg-red-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-red-500">Sebab Ditolak</p>
                    <p class="mt-1 text-sm text-red-800">{{ $belanja->catatan_tolak }}</p>
                    <p class="mt-1 text-xs text-red-500">
                        Ditolak oleh: {{ $belanja->ditolakOleh->name ?? '-' }} &bull;
                        {{ optional($belanja->tarikh_tolak)->format('d/m/Y H:i') ?? '-' }}
                    </p>
                </div>
            @endif

            <section class="print-card rounded-2xl border border-slate-300 bg-white p-8 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4 border-b-2 border-slate-800 pb-4">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-widest text-slate-500">
                            {{ $belanja->masjid->nama ?? 'Masjid' }}</p>
                        <p class="text-xs text-slate-400">{{ $belanja->masjid->alamat ?? '' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold uppercase tracking-widest text-slate-800">Baucar Bayaran</p>
                        <p class="mt-1 text-base font-bold text-red-600">{{ $baucarNo }}</p>
                        <span
                            class="mt-1 inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $belanja->is_baucar_locked ? 'bg-emerald-100 text-emerald-800' : ((int) $belanja->approval_step === 1 ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                            {{ $belanja->is_baucar_locked ? 'LULUS & LOCKED' : ((int) $belanja->approval_step === 1 ? 'MENUNGGU PENGERUSI' : 'DRAF') }}
                        </span>
                    </div>
                </div>

                <div class="mt-5 grid gap-1 text-sm">
                    <div class="grid grid-cols-[160px_1fr] gap-x-2 py-1 border-b border-slate-100">
                        <span class="font-semibold text-slate-500">Tarikh</span>
                        <span class="text-slate-800">{{ optional($belanja->tarikh)->format('d/m/Y') ?? '-' }}</span>
                    </div>
                    <div class="grid grid-cols-[160px_1fr] gap-x-2 py-1 border-b border-slate-100">
                        <span class="font-semibold text-slate-500">Penerima Bayaran</span>
                        <span class="text-slate-800">{{ $belanja->penerima ?? '-' }}</span>
                    </div>
                    <div class="grid grid-cols-[160px_1fr] gap-x-2 py-1 border-b border-slate-100">
                        <span class="font-semibold text-slate-500">Akaun Dikenakan</span>
                        <span class="text-slate-800">{{ $belanja->akaun->nama_akaun ?? '-' }}</span>
                    </div>
                    <div class="grid grid-cols-[160px_1fr] gap-x-2 py-1 border-b border-slate-100">
                        <span class="font-semibold text-slate-500">Kategori</span>
                        <span class="text-slate-800">{{ $belanja->kategoriBelanja->nama_kategori ?? '-' }}</span>
                    </div>
                    <div class="grid grid-cols-[160px_1fr] gap-x-2 py-1 border-b border-slate-100">
                        <span class="font-semibold text-slate-500">Status Kelulusan</span>
                        <span class="text-slate-800">
                            @if ($belanja->is_baucar_locked)
                                LULUS MUKTAMAD (DIKUNCI)
                            @elseif((int) $belanja->approval_step === 1)
                                MENUNGGU KELULUSAN PENGERUSI
                            @else
                                DRAF
                            @endif
                        </span>
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rantaian Kelulusan</p>
                    <div class="mt-3 grid gap-3 md:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-white p-3">
                            <p class="text-xs font-semibold text-slate-600">1. Bendahari</p>
                            <p class="mt-1 text-sm text-slate-800">{{ $belanja->bendahariLulusOleh->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500">
                                {{ optional($belanja->bendahari_lulus_pada)->format('d/m/Y H:i') ?? '-' }}</p>
                            @if (!empty($signatureImages['bendahari']))
                                <img src="{{ $signatureImages['bendahari'] }}" alt="Signature Bendahari"
                                    class="mt-2 h-10 w-auto rounded border border-slate-300 bg-white p-1" />
                            @endif
                            <p class="mt-2 text-[11px] font-mono text-emerald-700 break-all">
                                {{ $belanja->bendahari_signature ?? '-' }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-white p-3">
                            <p class="text-xs font-semibold text-slate-600">2. Pengerusi</p>
                            <p class="mt-1 text-sm text-slate-800">{{ $belanja->pengerusiLulusOleh->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500">
                                {{ optional($belanja->pengerusi_lulus_pada)->format('d/m/Y H:i') ?? '-' }}</p>
                            @if (!empty($signatureImages['pengerusi']))
                                <img src="{{ $signatureImages['pengerusi'] }}" alt="Signature Pengerusi"
                                    class="mt-2 h-10 w-auto rounded border border-slate-300 bg-white p-1" />
                            @endif
                            <p class="mt-2 text-[11px] font-mono text-emerald-700 break-all">
                                {{ $belanja->pengerusi_signature ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 rounded-xl border border-slate-200 overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-800">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white">
                                    Perkara</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-white">
                                    Catatan</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-white">
                                    Amaun (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-4 py-4 text-sm text-slate-800">Perbelanjaan -
                                    {{ $belanja->kategoriBelanja->nama_kategori ?? 'Umum' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ $belanja->catatan ?? '-' }}</td>
                                <td class="px-4 py-4 text-right text-sm font-semibold text-slate-900">
                                    {{ number_format((float) $belanja->amaun, 2) }}</td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <td colspan="2" class="px-4 py-3 text-right text-sm font-semibold text-slate-700">
                                    JUMLAH KESELURUHAN</td>
                                <td class="px-4 py-3 text-right text-base font-bold text-slate-900">RM
                                    {{ number_format((float) $belanja->amaun, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-10 flex flex-wrap items-end justify-between gap-6">
                    <div class="grid flex-1 gap-10 md:grid-cols-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Disediakan Oleh
                            </p>
                            <div class="mt-12 border-t border-slate-400 pt-2 text-xs text-slate-500">Nama &amp;
                                Tandatangan</div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Disemak Bendahari
                            </p>
                            @if ($belanja->bendahariLulusOleh)
                                <p class="mt-2 text-sm font-medium text-slate-800">
                                    {{ $belanja->bendahariLulusOleh->name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ optional($belanja->bendahari_lulus_pada)->format('d/m/Y') }}</p>
                                @if (!empty($signatureImages['bendahari']))
                                    <img src="{{ $signatureImages['bendahari'] }}" alt="Signature Bendahari"
                                        class="mt-1 h-9 w-auto rounded border border-slate-300 bg-white p-1" />
                                @endif
                                <p class="text-[11px] font-mono text-emerald-700 break-all">
                                    {{ $belanja->bendahari_signature }}</p>
                            @else
                                <div class="mt-12 border-t border-slate-400 pt-2 text-xs text-slate-500">Nama &amp;
                                    Tandatangan</div>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Diluluskan
                                Pengerusi</p>
                            @if ($belanja->is_baucar_locked && $belanja->pengerusiLulusOleh)
                                <p class="mt-2 text-sm font-medium text-slate-800">
                                    {{ $belanja->pengerusiLulusOleh->name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ optional($belanja->pengerusi_lulus_pada)->format('d/m/Y') }}</p>
                                @if (!empty($signatureImages['pengerusi']))
                                    <img src="{{ $signatureImages['pengerusi'] }}" alt="Signature Pengerusi"
                                        class="mt-1 h-9 w-auto rounded border border-slate-300 bg-white p-1" />
                                @endif
                                <p class="text-[11px] font-mono text-emerald-700 break-all">
                                    {{ $belanja->pengerusi_signature }}</p>
                                <div
                                    class="mt-2 border-t border-emerald-400 pt-2 text-xs text-emerald-700 font-semibold">
                                    Diluluskan</div>
                            @else
                                <div class="mt-12 border-t border-slate-400 pt-2 text-xs text-slate-500">Nama &amp;
                                    Tandatangan</div>
                            @endif
                        </div>
                    </div>
                    <div class="shrink-0 text-center">
                        <div
                            class="mx-auto h-28 w-28 rounded-lg border border-slate-200 p-1 [&>svg]:h-full [&>svg]:w-full">
                            {!! $qrCode !!}
                        </div>
                        <p class="mt-1 text-xs text-slate-400">Imbas untuk pengesahan</p>
                    </div>
                </div>

                <p class="mt-4 text-center text-xs text-slate-300 break-all">{{ $verifyUrl }}</p>
            </section>
        </div>
    </div>

    @canany(['approve', 'reject'], $belanja)
        <div id="reject-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 no-print">
            <div class="mx-4 w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
                <h3 class="text-base font-semibold text-slate-800">Tolak / Pulangkan Semula</h3>
                <p class="mt-1 text-sm text-slate-500">Sila nyatakan sebab baucar perlu dibetulkan.</p>
                <form method="POST" action="{{ route('baucar.reject', ['belanja_id' => $belanja->id]) }}"
                    class="mt-4">
                    @csrf
                    <textarea name="catatan_tolak" rows="4" required maxlength="500"
                        class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-red-400 focus:ring-red-400"
                        placeholder="Contoh: Butiran penerima tidak lengkap...">{{ old('catatan_tolak') }}</textarea>
                    @error('catatan_tolak')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">
                            Sahkan Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endcanany

</x-app-layout>
