<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nama Pelan</label>
        <input type="text" id="name" name="name" value="{{ old('name', $plan?->name) }}" required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror">
        @error('name')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
        <input type="text" id="slug" name="slug" value="{{ old('slug', $plan?->slug) }}" required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('slug') border-red-500 @enderror">
        @error('slug')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="price" class="block text-sm font-medium text-gray-700">Harga (RM)</label>
        <input type="number" step="0.01" id="price" name="price" value="{{ old('price', $plan?->price) }}"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('price') border-red-500 @enderror">
        @error('price')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="billing_cycle" class="block text-sm font-medium text-gray-700">Kitaran Bil</label>
        @php($cycle = old('billing_cycle', $plan?->billing_cycle ?? 'monthly'))
        <select id="billing_cycle" name="billing_cycle" required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('billing_cycle') border-red-500 @enderror">
            <option value="monthly" @selected($cycle === 'monthly')>Bulanan</option>
            <option value="yearly" @selected($cycle === 'yearly')>Tahunan</option>
        </select>
        @error('billing_cycle')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="duration_months" class="block text-sm font-medium text-gray-700">Tempoh (bulan)</label>
        <input type="number" id="duration_months" name="duration_months"
            value="{{ old('duration_months', $plan?->duration_months ?? 1) }}" required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('duration_months') border-red-500 @enderror">
        @error('duration_months')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="sort_order" class="block text-sm font-medium text-gray-700">Turutan</label>
        <input type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('sort_order') border-red-500 @enderror">
        @error('sort_order')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

<div>
    <label for="features_json" class="block text-sm font-medium text-gray-700">Features (JSON)</label>
    <textarea id="features_json" name="features_json" rows="6"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 @error('features_json') border-red-500 @enderror">{{ old('features_json', $plan ? json_encode($plan->features, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
    @error('features_json')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="flex items-center gap-2">
    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $plan?->is_active ?? true))>
    <label for="is_active" class="text-sm text-gray-700">Aktifkan pelan ini</label>
</div>
