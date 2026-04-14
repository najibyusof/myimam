<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">CMS Landing Page</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-2">Landing Page Builder</h3>
                    <p class="text-sm text-gray-600">Ubah kandungan hero, features, dan footer untuk global template atau
                        tenant tertentu.</p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.cms.landing.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        @if ($isSuperAdmin)
                            <div>
                                <label for="target_masjid_id" class="block text-sm font-medium text-gray-700">Skop
                                    Kandungan</label>
                                <select id="target_masjid_id" name="target_masjid_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Global Template (Semua Tenant)</option>
                                    @foreach ($masjids as $masjid)
                                        <option value="{{ $masjid->id }}" @selected((string) $targetMasjidId === (string) $masjid->id)>
                                            {{ $masjid->nama }} ({{ $masjid->code ?? 'no-code' }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-xs text-gray-500">Pilih kosong untuk template global. Pilih tenant
                                    untuk override kandungan tenant tersebut.</p>
                            </div>
                        @else
                            <input type="hidden" name="target_masjid_id" value="{{ $targetMasjidId }}">
                        @endif

                        <div class="border rounded-lg p-4 space-y-4">
                            <h4 class="font-semibold text-gray-800">Hero Section</h4>

                            <div>
                                <label for="hero_title" class="block text-sm font-medium text-gray-700">Title</label>
                                <input type="text" id="hero_title" name="hero_title"
                                    value="{{ old('hero_title', $formData['hero_title']) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('hero_title') border-red-500 @enderror">
                                @error('hero_title')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="hero_subtitle"
                                    class="block text-sm font-medium text-gray-700">Subtitle</label>
                                <textarea id="hero_subtitle" name="hero_subtitle" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('hero_subtitle') border-red-500 @enderror">{{ old('hero_subtitle', $formData['hero_subtitle']) }}</textarea>
                                @error('hero_subtitle')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="hero_cta_text" class="block text-sm font-medium text-gray-700">CTA
                                        Text</label>
                                    <input type="text" id="hero_cta_text" name="hero_cta_text"
                                        value="{{ old('hero_cta_text', $formData['hero_cta_text']) }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('hero_cta_text') border-red-500 @enderror">
                                    @error('hero_cta_text')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="hero_image" class="block text-sm font-medium text-gray-700">Hero Image
                                        URL/Path (optional)</label>
                                    <input type="text" id="hero_image" name="hero_image"
                                        value="{{ old('hero_image', $formData['hero_image']) }}"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('hero_image') border-red-500 @enderror">
                                    @error('hero_image')
                                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-lg p-4 space-y-4">
                            <h4 class="font-semibold text-gray-800">Features Section</h4>
                            <div>
                                <label for="features_items" class="block text-sm font-medium text-gray-700">Features
                                    List (satu baris satu item)</label>
                                <textarea id="features_items" name="features_items" rows="6"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('features_items') border-red-500 @enderror">{{ old('features_items', $formData['features_items']) }}</textarea>
                                @error('features_items')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="border rounded-lg p-4 space-y-4">
                            <h4 class="font-semibold text-gray-800">Footer Section</h4>
                            <div>
                                <label for="footer_text" class="block text-sm font-medium text-gray-700">Footer
                                    Text</label>
                                <textarea id="footer_text" name="footer_text" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('footer_text') border-red-500 @enderror">{{ old('footer_text', $formData['footer_text']) }}</textarea>
                                @error('footer_text')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <input id="is_active" type="checkbox" name="is_active" value="1"
                                @checked(old('is_active', $formData['is_active']))>
                            <label for="is_active" class="text-sm text-gray-700">Aktifkan page ini</label>
                        </div>

                        <div class="pt-4 border-t flex justify-end">
                            <button type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Simpan Kandungan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
