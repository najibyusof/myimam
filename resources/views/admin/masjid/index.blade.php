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
                                placeholder="Cari nama, negeri, atau daerah..."
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
                                            Nama</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Lokasi</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            No. Pendaftaran</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Dibuat</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                            Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($masjids as $masjid)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $masjid->nama }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                {{ $masjid->daerah }}, {{ $masjid->negeri }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600">
                                                {{ $masjid->no_pendaftaran ?? '-' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                {{ $masjid->created_at?->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                @can('masjid.update')
                                                    <a href="{{ route('admin.masjid.edit', $masjid) }}"
                                                        class="text-indigo-600 hover:text-indigo-900">
                                                        Ubah
                                                    </a>
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
