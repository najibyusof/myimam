<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800 leading-tight">
                {{ __('Butiran Log Aktiviti') }}
            </h2>
            <a href="{{ route('admin.log-aktiviti.index') }}"
               class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                &larr; Senarai Log
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @php
                $jenisBadge = match($record->jenis) {
                    'LOGIN_OK'   => 'bg-green-100 text-green-800',
                    'LOGIN_FAIL' => 'bg-red-100 text-red-800',
                    'CREATE'     => 'bg-blue-100 text-blue-800',
                    'UPDATE'     => 'bg-amber-100 text-amber-800',
                    'DELETE'     => 'bg-rose-100 text-rose-800',
                    'APPROVE'    => 'bg-purple-100 text-purple-800',
                    'EXPORT'     => 'bg-teal-100 text-teal-800',
                    default      => 'bg-gray-100 text-gray-700',
                };
            @endphp

            {{-- Header card --}}
            <div class="bg-white shadow rounded-xl p-6">
                <div class="flex flex-wrap items-start gap-4 justify-between">
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $jenisBadge }}">
                            {{ $record->jenis }}
                        </span>
                        @if ($record->modul)
                            <span class="ml-2 text-sm font-semibold text-gray-700">{{ $record->modul }}</span>
                        @endif
                        @if ($record->aksi)
                            <span class="ml-2 text-sm text-gray-500">— {{ $record->aksi }}</span>
                        @endif
                    </div>
                    <span class="text-xs text-gray-400 font-mono">
                        {{ $record->created_at?->format('d/m/Y H:i:s') }}
                    </span>
                </div>

                @if ($record->butiran)
                    <p class="mt-4 text-sm text-gray-600 leading-relaxed">{{ $record->butiran }}</p>
                @endif
            </div>

            {{-- Details grid --}}
            <div class="bg-white shadow rounded-xl overflow-hidden">
                <dl class="divide-y divide-gray-100">
                    <div class="grid grid-cols-3 px-6 py-4">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider self-center">ID Log</dt>
                        <dd class="col-span-2 text-sm text-gray-800 font-mono">#{{ $record->id }}</dd>
                    </div>
                    <div class="grid grid-cols-3 px-6 py-4">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider self-center">Pengguna</dt>
                        <dd class="col-span-2 text-sm text-gray-800">
                            {{ $record->user?->name ?? '–' }}
                            @if ($record->user?->email)
                                <span class="block text-xs text-gray-400">{{ $record->user->email }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-3 px-6 py-4">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider self-center">Masjid</dt>
                        <dd class="col-span-2 text-sm text-gray-800">{{ $record->masjid?->nama ?? '–' }}</dd>
                    </div>
                    @if ($record->rujukan_id)
                        <div class="grid grid-cols-3 px-6 py-4">
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider self-center">Rujukan ID</dt>
                            <dd class="col-span-2 text-sm text-gray-800 font-mono">{{ $record->rujukan_id }}</dd>
                        </div>
                    @endif
                    <div class="grid grid-cols-3 px-6 py-4">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider self-center">Alamat IP</dt>
                        <dd class="col-span-2 text-sm font-mono text-gray-700">{{ $record->ip ?? '–' }}</dd>
                    </div>
                    @if ($record->user_agent)
                        <div class="grid grid-cols-3 px-6 py-4">
                            <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider self-start pt-0.5">Peranti</dt>
                            <dd class="col-span-2 text-xs text-gray-500 break-all">{{ $record->user_agent }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Data diff --}}
            @if ($record->data_lama || $record->data_baru)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if ($record->data_lama)
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                            <h3 class="text-xs font-semibold text-red-600 uppercase tracking-wider mb-3">Data Lama</h3>
                            <pre class="text-xs text-red-800 whitespace-pre-wrap break-all">{{ json_encode($record->data_lama, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                    @if ($record->data_baru)
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                            <h3 class="text-xs font-semibold text-green-600 uppercase tracking-wider mb-3">Data Baru</h3>
                            <pre class="text-xs text-green-800 whitespace-pre-wrap break-all">{{ json_encode($record->data_baru, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
