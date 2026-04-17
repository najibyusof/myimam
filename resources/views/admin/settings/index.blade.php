<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Tetapan Sistem</h2>
            <p class="mt-1 text-sm text-gray-500">Konfigurasi global untuk sistem MyImam.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                @method('PUT')

                {{-- Landing Page Mode --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                        <h3 class="text-base font-semibold text-slate-900">Halaman Utama (Landing Page)</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Pilih sama ada halaman utama awam dipaparkan menggunakan CMS Builder atau halaman statik
                            lalai.
                        </p>
                    </div>

                    <div class="space-y-3 px-6 py-5">
                        @php $currentMode = $settings['landing_page_mode'] ?? 'cms'; @endphp

                        <label
                            class="flex cursor-pointer items-start gap-4 rounded-xl border p-4 transition
                            {{ $currentMode === 'cms' ? 'border-indigo-400 bg-indigo-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                            <input type="radio" name="landing_page_mode" value="cms"
                                class="mt-0.5 text-indigo-600 focus:ring-indigo-500"
                                {{ $currentMode === 'cms' ? 'checked' : '' }}>
                            <div>
                                <p class="font-semibold text-slate-800">CMS Builder</p>
                                <p class="mt-0.5 text-sm text-slate-500">
                                    Gunakan halaman yang dibina melalui CMS Builder. Setiap masjid boleh mempunyai
                                    halaman
                                    tersuai, jika tiada ia akan menggunakan halaman CMS global.
                                </p>
                            </div>
                        </label>

                        <label
                            class="flex cursor-pointer items-start gap-4 rounded-xl border p-4 transition
                            {{ $currentMode === 'static' ? 'border-indigo-400 bg-indigo-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
                            <input type="radio" name="landing_page_mode" value="static"
                                class="mt-0.5 text-indigo-600 focus:ring-indigo-500"
                                {{ $currentMode === 'static' ? 'checked' : '' }}>
                            <div>
                                <p class="font-semibold text-slate-800">Halaman Statik Lalai</p>
                                <p class="mt-0.5 text-sm text-slate-500">
                                    Guna halaman Blade statik lalai (welcome.blade.php). CMS Builder tidak akan
                                    digunakan.
                                </p>
                            </div>
                        </label>

                        @error('landing_page_mode')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end border-t border-slate-200 px-6 py-4">
                        <x-primary-button>Simpan Tetapan</x-primary-button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>
