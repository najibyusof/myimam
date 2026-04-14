<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Masjid') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('status'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Senarai Masjid</h3>
                        @can('masjid.create')
                            <a href="{{ route('admin.masjid.create') }}"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Tambah Masjid
                            </a>
                        @endcan
                    </div>

                    <form action="{{ route('admin.masjid.index') }}" method="GET" class="mb-6">
                        <div class="flex gap-2">
                            <input type="text" name="q" value="{{ $q }}"
                                placeholder="Cari nama, kod, negeri, atau daerah..."
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" />
                            <button type="submit"
                                class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                                Cari
                            </button>
                        </div>
                    </form>

                    @if ($masjids->count())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Tenant</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Status</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Langganan</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Penggunaan</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($masjids as $masjid)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                <div>{{ $masjid->nama }}</div>
                                                <div class="text-xs text-gray-500">{{ $masjid->code ?? '-' }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                @if ($masjid->status === 'active')
                                                    <span
                                                        class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700">Active</span>
                                                @elseif($masjid->status === 'suspended')
                                                    <span
                                                        class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700">Suspended</span>
                                                @else
                                                    <span
                                                        class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-700">Pending</span>
                                                @endif
                                                <div class="text-xs mt-2 text-gray-500">{{ $masjid->daerah }},
                                                    {{ $masjid->negeri }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                <div>
                                                    <span
                                                        class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-700">
                                                        {{ strtoupper($masjid->subscription_status ?? 'none') }}
                                                    </span>
                                                </div>
                                                <div class="text-xs mt-2 text-gray-500">
                                                    Tamat: {{ $masjid->subscription_expiry?->format('d/m/Y') ?? '-' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                <div>Admin: {{ $masjid->admin_count }}</div>
                                                <div>User: {{ $masjid->users_count }}</div>
                                                <div>Hasil: {{ $masjid->hasil_count }}</div>
                                                <div>Belanja: {{ $masjid->belanja_count }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-sm font-medium space-y-2">
                                                @can('masjid.update')
                                                    <a href="{{ route('admin.masjid.edit', $masjid) }}"
                                                        class="text-indigo-600 hover:text-indigo-900 block">
                                                        Ubah
                                                    </a>

                                                    @if ($masjid->status !== 'suspended')
                                                        <form action="{{ route('admin.masjid.suspend', $masjid) }}"
                                                            method="POST" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit"
                                                                onclick="return confirm('Gantung tenant ini?')"
                                                                class="text-amber-600 hover:text-amber-900">
                                                                Gantung
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('admin.masjid.activate', $masjid) }}"
                                                            method="POST" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit"
                                                                onclick="return confirm('Aktifkan semula tenant ini?')"
                                                                class="text-green-600 hover:text-green-900">
                                                                Aktifkan
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan

                                                @can('masjid.delete')
                                                    <form action="{{ route('admin.masjid.destroy', $masjid) }}"
                                                        method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            onclick="return confirm('Adakah anda pasti?')"
                                                            class="text-red-600 hover:text-red-900">
                                                            Padam
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $masjids->links() }}
                        </div>
                    @else
                        <p class="text-center text-gray-500 py-8">Tiada masjid dijumpai.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
