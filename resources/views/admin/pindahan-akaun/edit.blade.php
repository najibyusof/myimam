<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('pindahan_akaun.edit_title') }}
            </h2>
            <a href="{{ route('admin.pindahan-akaun.index') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                &larr; {{ __('pindahan_akaun.form.back') }}
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-5">

            @if (session('status'))
                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Transfer summary card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex flex-wrap gap-6 text-sm">
                    <div>
                        <span class="text-gray-500">{{ __('pindahan_akaun.summary.from') }}:</span>
                        <span
                            class="ml-1 font-semibold text-red-700">{{ $record->dariAkaun?->nama_akaun ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('pindahan_akaun.summary.to') }}:</span>
                        <span
                            class="ml-1 font-semibold text-green-700">{{ $record->keAkaun?->nama_akaun ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('pindahan_akaun.summary.amount') }}:</span>
                        <span class="ml-1 font-semibold text-indigo-700">RM
                            {{ number_format($record->amaun, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('pindahan_akaun.summary.date') }}:</span>
                        <span class="ml-1 font-medium">{{ $record->tarikh->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

                @if ($errors->any())
                    <div class="mb-5 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.pindahan-akaun.update', $record) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('admin.pindahan-akaun._form', [
                        'record' => $record,
                        'akaunOptions' => $akaunOptions,
                    ])
                    <div class="flex items-center justify-between gap-3 pt-2 border-t border-gray-100">
                        @can('delete', $record)
                            <form method="POST" action="{{ route('admin.pindahan-akaun.destroy', $record) }}"
                                data-confirm="{{ __('pindahan_akaun.confirm_delete') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="rounded-lg border border-rose-300 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-100">
                                    {{ __('pindahan_akaun.table.delete') }}
                                </button>
                            </form>
                        @else
                            <div></div>
                        @endcan
                        <div class="flex gap-3">
                            <a href="{{ route('admin.pindahan-akaun.index') }}"
                                class="rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">
                                {{ __('pindahan_akaun.form.cancel') }}
                            </a>
                            <button type="submit"
                                class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                {{ __('pindahan_akaun.form.update') }}
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
