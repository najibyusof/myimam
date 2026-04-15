<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('sumber_hasil.add_title') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('sumber_hasil.panel.source_information') }}
                    </h3>
                    <p class="mt-1 text-sm text-slate-600">{{ __('sumber_hasil.panel.create_subtitle') }}</p>
                </div>

                <form method="POST" action="{{ route('admin.sumber-hasil.store') }}" class="space-y-6">
                    @csrf

                    <div class="px-6 py-6">
                        @include('admin.sumber-hasil._form')
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4">
                        <a href="{{ route('admin.sumber-hasil.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                            {{ __('sumber_hasil.form.cancel') }}
                        </a>
                        <x-primary-button>{{ __('sumber_hasil.form.save_source') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
