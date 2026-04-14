<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Akaun</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Maklumat Akaun</h3>
                    <p class="mt-1 text-sm text-slate-600">Cipta akaun kewangan jenis tunai atau bank.</p>
                </div>

                <form method="POST" action="{{ route('admin.akaun.store') }}" class="space-y-6">
                    @csrf

                    <div class="px-6 py-6">
                        @include('admin.akaun._form')
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4">
                        <a href="{{ route('admin.akaun.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Batal
                        </a>
                        <x-primary-button>Simpan Akaun</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
