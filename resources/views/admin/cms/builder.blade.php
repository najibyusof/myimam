<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">CMS Builder</h2>
    </x-slot>

    <div class="py-8" x-data="cmsBuilder({
        initialLayout: @js($builderData['content_json']),
        initialTitle: @js($builderData['title']),
        initialSeoTitle: @js($builderData['seo_title']),
        initialSeoMetaDescription: @js($builderData['seo_meta_description']),
        initialIsActive: @js((bool) $builderData['is_active']),
        initialVersions: @js($builderData['versions']),
        uploadUrl: @js(route('admin.cms.builder.media.upload')),
        mediaLibraryUrl: @js(route('admin.cms.builder.media.index')),
        previewRenderUrl: @js(route('admin.cms.builder.preview')),
        generateUrl: @js(route('admin.cms.builder.generate')),
        presetsUrl: @js(route('admin.cms.builder.presets')),
    })" x-init="init()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-5">
            @if (session('status'))
                <div class="rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.cms.builder.update', ['slug' => $slug]) }}" x-ref="builderForm"
                @submit="prepareSubmit" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm space-y-4">
                    <div class="flex flex-wrap items-center gap-3 justify-between">
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach ($editableSlugs as $editableSlug)
                                <a href="{{ route('admin.cms.builder.edit', ['slug' => $editableSlug, 'masjid_id' => $targetMasjidId]) }}"
                                    class="rounded-md px-3 py-1.5 text-sm font-medium {{ $slug === $editableSlug ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                                    {{ strtoupper($editableSlug) }}
                                </a>
                            @endforeach
                        </div>

                        <a href="{{ $slug === 'login' ? route('login') : route('landing') }}" target="_blank"
                            class="rounded-md border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-sm font-medium text-indigo-700 hover:bg-indigo-100">
                            Pratonton {{ strtoupper($slug) }}
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700">Tajuk Halaman</label>
                            <input type="text" x-model="title"
                                class="mt-1 w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($isSuperAdmin)
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Skop</label>
                                <select name="target_masjid_id"
                                    class="mt-1 w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Global Template</option>
                                    @foreach ($masjids as $masjid)
                                        <option value="{{ $masjid->id }}" @selected((string) $targetMasjidId === (string) $masjid->id)>
                                            {{ $masjid->nama }} ({{ $masjid->code ?? 'no-code' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="target_masjid_id" value="{{ $targetMasjidId }}">
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t border-slate-200 pt-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">SEO Title</label>
                            <input type="text" x-model="seoTitle"
                                class="mt-1 w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                                maxlength="200">
                            @error('seo_title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Meta Description</label>
                            <textarea x-model="seoMetaDescription" rows="3" maxlength="320"
                                class="mt-1 w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            @error('seo_meta_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm text-slate-700">Status semasa:</span>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold"
                            :class="isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
                            x-text="isActive ? 'Published' : 'Unpublished'"></span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 border-t border-slate-200 pt-3">
                        <button type="button" @click="undo()" :disabled="!canUndo"
                            class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            Undo
                        </button>
                        <button type="button" @click="redo()" :disabled="!canRedo"
                            class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            Redo
                        </button>
                        <button type="button" @click="togglePreviewMode()"
                            class="rounded-md border px-3 py-1.5 text-xs font-semibold"
                            :class="previewMode ? 'border-indigo-600 bg-indigo-600 text-white' :
                                'border-indigo-300 bg-indigo-50 text-indigo-700'">
                            <span x-text="previewMode ? 'Exit Preview' : 'Preview Mode'"></span>
                        </button>
                        <div class="flex items-center gap-1" x-show="previewMode">
                            <button type="button" @click="previewViewport = 'desktop'"
                                class="rounded-md border px-2.5 py-1.5 text-[11px] font-semibold"
                                :class="previewViewport === 'desktop' ? 'border-slate-900 bg-slate-900 text-white' :
                                    'border-slate-300 bg-white text-slate-700'">
                                Desktop
                            </button>
                            <button type="button" @click="previewViewport = 'tablet'"
                                class="rounded-md border px-2.5 py-1.5 text-[11px] font-semibold"
                                :class="previewViewport === 'tablet' ? 'border-slate-900 bg-slate-900 text-white' :
                                    'border-slate-300 bg-white text-slate-700'">
                                Tablet
                            </button>
                            <button type="button" @click="previewViewport = 'mobile'"
                                class="rounded-md border px-2.5 py-1.5 text-[11px] font-semibold"
                                :class="previewViewport === 'mobile' ? 'border-slate-900 bg-slate-900 text-white' :
                                    'border-slate-300 bg-white text-slate-700'">
                                Mobile
                            </button>
                        </div>
                        <button type="button" @click="openAiModal()"
                            class="rounded-md border border-amber-300 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-100">
                            ✨ Jana Halaman AI
                        </button>
                        <button type="button" @click="submitAction('publish')"
                            class="rounded-md border border-emerald-300 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                            Publish
                        </button>
                        <button type="button" @click="submitAction('unpublish')"
                            class="rounded-md border border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                            Unpublish
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 min-h-[68vh]">
                    <aside class="xl:col-span-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <h3 class="text-sm font-semibold text-slate-900">Section Templates</h3>
                        <p class="mt-1 text-xs text-slate-500">Template siap guna untuk membina landing page lebih
                            pantas.</p>

                        <div class="mt-4 space-y-2">
                            <template x-for="template in sectionTemplates" :key="template.id">
                                <article
                                    class="rounded-xl border border-indigo-200 bg-indigo-50/60 px-3 py-3 shadow-sm">
                                    <p class="text-sm font-semibold text-indigo-900" x-text="template.label"></p>
                                    <p class="mt-1 text-xs text-indigo-700" x-text="template.description"></p>
                                    <div class="mt-2">
                                        <button type="button" @click="insertSectionTemplate(template.id)"
                                            class="rounded-md border border-indigo-300 bg-white px-2.5 py-1 text-xs font-semibold text-indigo-800 hover:bg-indigo-100">
                                            Guna Template
                                        </button>
                                    </div>
                                </article>
                            </template>
                        </div>

                        <div class="mt-6 border-t border-slate-200 pt-4">
                            <h3 class="text-sm font-semibold text-slate-900">Sections Library</h3>
                            <p class="mt-1 text-xs text-slate-500">Tambah seksyen premium ke kanvas menggunakan butang
                                atau
                                drag and drop.</p>

                            <div class="mt-4 space-y-2">
                                <template x-for="section in sectionLibrary" :key="section.variant">
                                    <article
                                        class="rounded-xl border border-cyan-200 bg-cyan-50/60 px-3 py-3 shadow-sm"
                                        draggable="true" @dragstart="startLibraryDrag(section.variant)"
                                        @dragend="endLibraryDrag()">
                                        <p class="text-sm font-semibold text-cyan-900" x-text="section.label"></p>
                                        <p class="mt-1 text-xs text-cyan-700" x-text="section.description"></p>
                                        <div class="mt-2 flex items-center justify-between gap-2">
                                            <button type="button" @click="addSection(section.variant)"
                                                class="rounded-md border border-cyan-300 bg-white px-2.5 py-1 text-xs font-semibold text-cyan-800 hover:bg-cyan-100">
                                                Tambah
                                            </button>
                                            <span
                                                class="text-[10px] font-medium uppercase tracking-wide text-cyan-700">Drag
                                                ke Canvas</span>
                                        </div>
                                    </article>
                                </template>
                            </div>

                            <div class="mt-6 border-t border-slate-200 pt-4">
                                <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-700">Legacy
                                    Components
                                </h4>
                                <p class="mt-1 text-xs text-slate-500">Masih disokong untuk halaman sedia ada.</p>

                                <div class="mt-3 space-y-2">
                                    <template x-for="lib in library" :key="lib.type">
                                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                                            <div class="text-sm font-medium text-slate-700" x-text="lib.label"></div>
                                            <div class="mt-2 flex gap-2">
                                                <button type="button" @click="addRootComponent(lib.type)"
                                                    class="rounded-md border border-slate-300 bg-white px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100">
                                                    Root
                                                </button>
                                                <button type="button" x-show="canAddChildTarget()"
                                                    @click="addChildComponent(lib.type)"
                                                    class="rounded-md border border-indigo-300 bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                                                    Child
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                    </aside>

                    <section class="xl:col-span-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-900">Canvas</h3>
                            <span class="text-xs text-slate-500">Drag & drop untuk susun semula</span>
                        </div>

                        <div x-show="!previewMode" id="cms-canvas" @dragover.prevent
                            @drop.prevent="dropSectionOnCanvas()"
                            class="mt-4 space-y-3 min-h-[420px] rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 p-3">
                            <template x-for="(component, index) in components" :key="component._id">
                                <div class="rounded-lg border bg-white p-3 shadow-sm transition"
                                    :class="[
                                        isSelected(index, null) ? 'border-indigo-500 ring-2 ring-indigo-200' :
                                        'border-slate-200 hover:border-slate-300',
                                        reorderPulseId === component._id ? 'cms-reorder-pulse' : ''
                                    ]"
                                    :data-root-id="component._id">
                                    <div class="flex items-center justify-between gap-2">
                                        <button type="button" @click="selectComponent(index, null)"
                                            class="flex-1 text-left">
                                            <p class="text-sm font-semibold text-slate-800"
                                                x-text="componentLabel(component.type)"></p>
                                            <p class="text-xs text-slate-500" x-text="componentSummary(component)">
                                            </p>
                                        </button>

                                        <div class="flex items-center gap-1">
                                            <button type="button" @click="duplicateComponent(index)"
                                                class="rounded-md border border-sky-200 px-2 py-1 text-xs font-medium text-sky-700 hover:bg-sky-50">
                                                Duplikat
                                            </button>
                                            <button type="button" @click="removeComponent(index)"
                                                class="rounded-md border border-rose-200 px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50">
                                                Buang
                                            </button>
                                        </div>
                                    </div>

                                    <div x-show="componentCanContainChildren(component)"
                                        class="mt-3 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-2">
                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                            Children</p>
                                        <p x-show="childDropzonesActive"
                                            class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-emerald-700">
                                            Drop Here
                                        </p>
                                        <div class="mt-2 space-y-2" data-child-container :data-parent-index="index">
                                            <template x-for="(child, childIndex) in (component.children || [])"
                                                :key="child._id">
                                                <div class="rounded-md border bg-white p-2"
                                                    :class="isSelected(index, childIndex) ?
                                                        'border-indigo-500 ring-1 ring-indigo-200' : 'border-slate-200'"
                                                    :data-child-id="child._id">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <button type="button" class="flex-1 text-left"
                                                            @click="selectComponent(index, childIndex)">
                                                            <p class="text-xs font-semibold text-slate-700"
                                                                x-text="componentLabel(child.type)"></p>
                                                            <p class="text-[11px] text-slate-500"
                                                                x-text="componentSummary(child)"></p>
                                                        </button>
                                                        <button type="button" @click="removeChild(index, childIndex)"
                                                            class="rounded border border-rose-200 px-1.5 py-0.5 text-[10px] font-medium text-rose-600 hover:bg-rose-50">
                                                            Buang
                                                        </button>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div x-show="components.length === 0"
                                class="h-40 grid place-items-center text-center text-sm text-slate-400">
                                Tiada seksyen. Tambah dari Sections Library di panel kiri.
                            </div>
                        </div>

                        <div x-show="previewMode"
                            x-effect="if (previewMode) { componentsSignature; scheduleServerPreview(); }"
                            class="mt-4 min-h-[420px] rounded-xl border border-indigo-200 bg-white p-4 shadow-inner">
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Live Preview
                                </p>
                                <span class="text-[11px] text-indigo-500" x-text="previewViewportLabel"></span>
                            </div>
                            <div class="mx-auto space-y-3 transition-all duration-200" :class="previewViewportClass">
                                <div x-show="previewLoading"
                                    class="rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs text-indigo-700">
                                    Rendering preview...
                                </div>
                                <div x-show="previewError" x-text="previewError"
                                    class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
                                </div>
                                <div :class="previewViewportShellClass">
                                    <div x-html="previewHtml"></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <aside class="xl:col-span-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <h3 class="text-sm font-semibold text-slate-900">Properties</h3>
                        <p class="mt-1 text-xs text-slate-500">Edit komponen yang dipilih.</p>

                        <div x-show="selectedComponent && selectedComponent.type === 'section'"
                            class="mt-3 rounded-lg border border-cyan-200 bg-cyan-50 px-3 py-2 text-xs text-cyan-800">
                            <span class="font-semibold">Section Variant:</span>
                            <span x-text="selectedComponent?.variant || '-' "></span>
                        </div>

                        <div class="mt-4 space-y-3" x-show="selectedComponent">
                            <template x-if="selectedComponent">
                                <div class="space-y-3">
                                    <template x-for="field in editableFields" :key="field.key">
                                        <div>
                                            <label
                                                class="block text-xs font-medium uppercase tracking-wide text-slate-500"
                                                x-text="field.label"></label>
                                            <template x-if="field.type === 'checkbox'">
                                                <label
                                                    class="mt-2 inline-flex items-center gap-2 text-sm text-slate-700">
                                                    <input type="checkbox"
                                                        :checked="String(getEditableFieldValue(field) ?? '1') === '1'"
                                                        @change="updateEditableField(field, $event.target.checked ? '1' : '0')"
                                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                                    <span>Papar akaun demo kepada pengguna awam</span>
                                                </label>
                                            </template>
                                            <template x-if="field.type === 'textarea'">
                                                <textarea rows="4" :placeholder="field.placeholder || ''" :value="getEditableFieldValue(field)"
                                                    @input="updateEditableField(field, $event.target.value)"
                                                    class="mt-1 w-full rounded-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                            </template>
                                            <template x-if="field.type === 'select'">
                                                <select :value="getEditableFieldValue(field) || ''"
                                                    @change="updateEditableField(field, $event.target.value)"
                                                    class="mt-1 w-full rounded-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    <template x-for="option in (field.options || [])"
                                                        :key="`${field.key}-${option.value}`">
                                                        <option :value="option.value" x-text="option.label"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template x-if="field.type === 'text'">
                                                <input type="text" :value="getEditableFieldValue(field) || ''"
                                                    :placeholder="field.placeholder || ''"
                                                    @input="updateEditableField(field, $event.target.value)"
                                                    class="mt-1 w-full rounded-md border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            </template>
                                            <p class="mt-1 text-[11px] text-slate-500" x-show="field.help"
                                                x-text="field.help"></p>
                                        </div>
                                    </template>

                                    <p x-show="propertyError" x-text="propertyError" class="text-xs text-rose-600">
                                    </p>

                                    <div x-show="selectedComponent && selectedComponent.type === 'image'"
                                        class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                        <label
                                            class="block text-xs font-medium uppercase tracking-wide text-slate-500">Upload
                                            Image</label>
                                        <input type="file" accept="image/*" @change="uploadImage($event)"
                                            class="mt-2 block w-full text-xs text-slate-600">
                                        <p x-show="uploading" class="mt-2 text-xs text-indigo-600">Uploading...</p>
                                        <p x-show="uploadError" x-text="uploadError"
                                            class="mt-2 text-xs text-rose-600"></p>

                                        <div class="mt-4 border-t border-slate-200 pt-3">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                                    Media
                                                    Library</p>
                                                <button type="button" @click="fetchMediaLibrary()"
                                                    class="rounded border border-slate-300 bg-white px-2 py-1 text-[10px] font-semibold text-slate-700 hover:bg-slate-100">
                                                    Refresh
                                                </button>
                                            </div>
                                            <div class="mt-2 grid grid-cols-2 gap-2 max-h-64 overflow-y-auto pr-1">
                                                <template x-for="item in mediaItems" :key="item.path">
                                                    <button type="button" @click="selectMedia(item.url)"
                                                        class="rounded-md border p-2 text-left transition"
                                                        :class="selectedComponent?.props?.image_url === item.url ?
                                                            'border-indigo-400 bg-indigo-50' :
                                                            'border-slate-200 bg-white hover:border-slate-300'">
                                                        <img :src="item.url" :alt="item.name"
                                                            class="h-20 w-full rounded object-cover">
                                                        <p class="mt-2 truncate text-[10px] font-medium text-slate-700"
                                                            x-text="item.name"></p>
                                                    </button>
                                                </template>
                                            </div>
                                            <p x-show="!mediaItems.length" class="mt-2 text-xs text-slate-500">Belum
                                                ada
                                                imej dimuat naik.</p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div x-show="!selectedComponent"
                            class="mt-8 rounded-lg border border-slate-200 bg-slate-50 px-3 py-4 text-sm text-slate-500">
                            Pilih komponen pada kanvas untuk mengubah text, warna, URL imej, alignment, padding, dan
                            margin.
                        </div>

                        <div class="mt-6 border-t border-slate-200 pt-4">
                            <h4 class="text-sm font-semibold text-slate-900">Version History</h4>
                            <p class="mt-1 text-xs text-slate-500">Snapshot disimpan setiap
                                Save/Publish/Unpublish/Restore.</p>

                            <div class="mt-3 space-y-2 max-h-64 overflow-y-auto pr-1">
                                @forelse ($builderData['versions'] as $version)
                                    <form method="POST"
                                        action="{{ route('admin.cms.builder.versions.restore', ['slug' => $slug, 'version' => $version['id']]) }}"
                                        class="rounded-lg border border-slate-200 bg-slate-50 p-2.5">
                                        @csrf
                                        @if ($isSuperAdmin)
                                            <input type="hidden" name="target_masjid_id"
                                                value="{{ $targetMasjidId }}">
                                        @endif

                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <p class="text-xs font-semibold text-slate-800">
                                                    V{{ $version['version_no'] }} ·
                                                    {{ strtoupper($version['action']) }}
                                                </p>
                                                <p class="text-[11px] text-slate-500">
                                                    {{ $version['created_at'] ?? '-' }}
                                                    @if (!empty($version['created_by']))
                                                        · {{ $version['created_by'] }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <button type="button"
                                                    @click="selectedVersionId = {{ $version['id'] }}"
                                                    class="rounded border px-2 py-1 text-[10px] font-semibold"
                                                    :class="selectedVersionId === {{ $version['id'] }} ?
                                                        'border-amber-300 bg-amber-50 text-amber-700' :
                                                        'border-slate-300 bg-white text-slate-700 hover:bg-slate-100'">
                                                    Compare
                                                </button>
                                                <button type="submit"
                                                    class="rounded border border-indigo-300 bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-700 hover:bg-indigo-100">
                                                    Restore
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                @empty
                                    <div
                                        class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-3 text-xs text-slate-500">
                                        Tiada versi untuk skop ini.
                                    </div>
                                @endforelse
                            </div>

                            <div class="mt-4 rounded-lg border border-slate-200 bg-white p-3"
                                x-show="selectedVersion">
                                <div class="flex items-center justify-between gap-2">
                                    <h5 class="text-xs font-semibold uppercase tracking-wide text-slate-700">Diff
                                        Viewer</h5>
                                    <span class="text-[11px] text-slate-500" x-text="selectedVersionMeta"></span>
                                </div>

                                <div class="mt-3 grid grid-cols-2 gap-3 text-[11px]">
                                    <div class="rounded-md border border-slate-200 bg-slate-50 p-2">
                                        <p class="font-semibold text-slate-700">Current Draft</p>
                                        <p class="mt-1 text-slate-600"><span class="font-medium">Title:</span> <span
                                                x-text="title"></span></p>
                                        <p class="text-slate-600"><span class="font-medium">Status:</span> <span
                                                x-text="isActive ? 'Published' : 'Unpublished'"></span></p>
                                        <p class="text-slate-600"><span class="font-medium">Components:</span> <span
                                                x-text="currentComponentCount"></span></p>
                                    </div>
                                    <div class="rounded-md border border-amber-200 bg-amber-50 p-2">
                                        <p class="font-semibold text-amber-800">Selected Version</p>
                                        <p class="mt-1 text-amber-700"><span class="font-medium">Title:</span> <span
                                                x-text="selectedVersion?.title || '-' "></span></p>
                                        <p class="text-amber-700"><span class="font-medium">Status:</span> <span
                                                x-text="selectedVersion?.is_active ? 'Published' : 'Unpublished'"></span>
                                        </p>
                                        <p class="text-amber-700"><span class="font-medium">Components:</span> <span
                                                x-text="selectedVersionComponentCount"></span></p>
                                    </div>
                                </div>

                                <div
                                    class="mt-3 rounded-md border border-slate-200 bg-slate-50 p-2 text-[11px] text-slate-700">
                                    <p class="font-semibold">Quick Change Summary</p>
                                    <ul class="mt-2 space-y-1 text-slate-600">
                                        <li
                                            x-text="title !== (selectedVersion?.title || '') ? 'Title changed' : 'Title unchanged'">
                                        </li>
                                        <li
                                            x-text="isActive !== !!selectedVersion?.is_active ? 'Publish state changed' : 'Publish state unchanged'">
                                        </li>
                                        <li
                                            x-text="currentComponentCount !== selectedVersionComponentCount ? 'Component count changed' : 'Component count unchanged'">
                                        </li>
                                        <li
                                            x-text="componentTypeDiffs.some((item) => item.status !== 'unchanged') ? 'Component types changed' : 'Component types unchanged'">
                                        </li>
                                    </ul>
                                </div>

                                <div class="mt-3 text-[11px]">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="font-semibold text-slate-700">Component Type Diff</p>
                                        <p class="text-slate-500">Current vs selected version</p>
                                    </div>

                                    <div class="mt-2 space-y-1" x-show="componentTypeDiffs.length">
                                        <template x-for="item in componentTypeDiffs" :key="item.type">
                                            <div class="flex items-center justify-between rounded border px-2 py-1.5"
                                                :class="item.status === 'added' ?
                                                    'border-emerald-200 bg-emerald-50 text-emerald-700' :
                                                    item.status === 'removed' ?
                                                    'border-rose-200 bg-rose-50 text-rose-700' :
                                                    'border-slate-200 bg-slate-50 text-slate-600'">
                                                <div>
                                                    <p class="font-medium" x-text="item.label"></p>
                                                    <p class="text-[10px] uppercase tracking-wide"
                                                        x-text="item.statusLabel"></p>
                                                </div>
                                                <p class="text-[10px] font-semibold"
                                                    x-text="`${item.currentCount} current / ${item.versionCount} version`">
                                                </p>
                                            </div>
                                        </template>
                                    </div>

                                    <div x-show="!componentTypeDiffs.length"
                                        class="mt-2 rounded border border-slate-200 bg-slate-50 px-2 py-1.5 text-slate-500">
                                        Tiada komponen untuk dibandingkan.
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
                </aside>
        </div>

        <input type="hidden" name="title" x-ref="titleInput">
        <input type="hidden" name="seo_title" x-ref="seoTitleInput">
        <input type="hidden" name="seo_meta_description" x-ref="seoMetaDescriptionInput">
        <input type="hidden" name="content_json" x-ref="contentInput">
        <input type="hidden" name="action" value="save" x-ref="actionInput">
        <input type="hidden" name="is_active" value="0">
        <input type="hidden" name="is_active" :value="isActive ? 1 : 0">

        @error('content_json')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="flex justify-end">
            <button type="button" @click="submitAction('save')"
                class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                Simpan Layout
            </button>
        </div>
        </form>

        <!-- AI Page Generator Modal -->
        <div x-show="aiModalOpen" class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape="closeAiModal()">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="aiModalOpen" @click="closeAiModal()"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-transition></div>

                <div x-show="aiModalOpen"
                    class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                    x-transition>
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">✨ Jana Halaman Menggunakan
                                    AI</h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Jenis
                                            Halaman</label>
                                        <select x-model="aiPageType"
                                            class="mt-1 w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="landing">Landing Page</option>
                                            <option value="login">Login Page</option>
                                            <option value="about">About Masjid</option>
                                            <option value="donation">Kempen Derma</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Penerangan
                                            Halaman</label>
                                        <textarea x-model="aiDescription" rows="4"
                                            placeholder="Contoh: Landing page untuk masjid moden dengan info derma dan program"
                                            class="mt-1 w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                        <p class="mt-1 text-xs text-slate-500">Minimum 10 aksara</p>
                                    </div>

                                    <div>
                                        <p class="text-sm font-medium text-slate-700 mb-2">Atau pilih template:</p>
                                        <div class="space-y-2 max-h-40 overflow-y-auto">
                                            <template x-for="preset in aiPresets" :key="preset.id">
                                                <button type="button" @click="selectPreset(preset)"
                                                    class="w-full text-left rounded-md border border-slate-200 bg-slate-50 p-2.5 text-xs font-medium text-slate-700 hover:bg-slate-100 transition">
                                                    <p x-text="preset.label"></p>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <p x-show="aiError" x-text="aiError" class="text-sm text-rose-600"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="generatePage()" :disabled="aiLoading"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!aiLoading">Jana Rekabentuk</span>
                            <span x-show="aiLoading">Memproses...</span>
                        </button>
                        <button type="button" @click="closeAiModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <style>
            .cms-sortable-ghost {
                opacity: 0.35;
                border: 2px dashed #34d399;
                background: #ecfdf5;
                min-height: 56px;
                transition: all 150ms ease;
            }

            .cms-sortable-chosen {
                transform: scale(1.01);
                transition: transform 150ms ease;
            }

            .cms-sortable-drag {
                opacity: 0.8;
            }

            .cms-reorder-pulse {
                animation: cms-reorder-pulse 420ms ease;
            }

            @keyframes cms-reorder-pulse {
                0% {
                    transform: scale(0.985);
                    box-shadow: 0 0 0 0 rgba(14, 165, 233, 0.32);
                }

                60% {
                    transform: scale(1.01);
                    box-shadow: 0 0 0 8px rgba(14, 165, 233, 0);
                }

                100% {
                    transform: scale(1);
                    box-shadow: 0 0 0 0 rgba(14, 165, 233, 0);
                }
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
        <script>
            function cmsBuilder({
                initialLayout,
                initialTitle,
                initialSeoTitle,
                initialSeoMetaDescription,
                initialIsActive,
                initialVersions,
                uploadUrl,
                mediaLibraryUrl,
                previewRenderUrl,
                generateUrl,
                presetsUrl
            }) {
                return {
                    title: initialTitle || 'Page Builder',
                    seoTitle: initialSeoTitle || initialTitle || 'Page Builder',
                    seoMetaDescription: initialSeoMetaDescription || '',
                    components: [],
                    isActive: initialIsActive,
                    previewMode: false,
                    previewViewport: 'desktop',
                    selectedPath: {
                        parent: null,
                        child: null,
                    },
                    historyStack: [],
                    redoStack: [],
                    versions: initialVersions || [],
                    selectedVersionId: null,
                    commonStyleFields: ['color', 'align', 'padding', 'margin'],
                    uploadUrl,
                    mediaLibraryUrl,
                    uploading: false,
                    uploadError: '',
                    mediaItems: [],
                    childDropzonesActive: false,
                    previewRenderUrl,
                    previewHtml: '',
                    previewLoading: false,
                    previewError: '',
                    previewDebounceTimer: null,
                    aiModalOpen: false,
                    aiPageType: 'landing',
                    aiDescription: '',
                    aiLoading: false,
                    aiError: '',
                    aiPresets: [],
                    propertyError: '',
                    draggingSectionVariant: null,
                    reorderPulseId: null,
                    generateUrl,
                    presetsUrl,
                    sectionTemplates: [{
                            id: 'hero-gradient',
                            label: 'Hero Style: Gradient Spotlight',
                            description: 'Hero dengan latar gradient gelap dan dua CTA.',
                            component: {
                                type: 'section',
                                variant: 'hero-saas',
                                props: {
                                    style: 'gradient',
                                    badge: 'SaaS Kewangan Masjid',
                                    title: 'Platform Kewangan Masjid Berbilang Cawangan',
                                    subtitle: 'Satukan kutipan, belanja, audit, dan pelaporan dalam satu platform.',
                                    primary_cta: {
                                        text: 'Daftar Masjid Anda',
                                        link: '/login',
                                    },
                                    secondary_cta: {
                                        text: 'Lihat Demo',
                                        link: '/login',
                                    },
                                    image: '/cms/defaults/landing-premium.svg',
                                },
                            },
                        },
                        {
                            id: 'hero-soft-light',
                            label: 'Hero Style: Soft Light',
                            description: 'Hero latar cerah dengan aksen cyan untuk mood minimal.',
                            component: {
                                type: 'section',
                                variant: 'hero-saas',
                                props: {
                                    style: 'soft',
                                    badge: 'Platform Premium',
                                    title: 'Urus Kewangan Masjid Dengan Paparan Lebih Bersih',
                                    subtitle: 'Pengalaman moden untuk pentadbir, bendahari, dan auditor.',
                                    primary_cta: {
                                        text: 'Mula Sekarang',
                                        link: '/login',
                                    },
                                    secondary_cta: {
                                        text: 'Lihat Ciri',
                                        link: '#',
                                    },
                                    image: '/cms/defaults/landing-premium.svg',
                                },
                            },
                        },
                        {
                            id: 'hero-split-dark',
                            label: 'Hero Style: Dark Split',
                            description: 'Hero split layout dengan panel visual lebih menonjol.',
                            component: {
                                type: 'section',
                                variant: 'hero-saas',
                                props: {
                                    style: 'split-dark',
                                    badge: 'Enterprise Ready',
                                    title: 'Kawalan Penuh Operasi Kewangan Setiap Cawangan',
                                    subtitle: 'Jejak audit, laporan, dan dashboard masa nyata dalam satu sistem.',
                                    primary_cta: {
                                        text: 'Jadualkan Demo',
                                        link: '/login',
                                    },
                                    secondary_cta: {
                                        text: 'Terokai Modul',
                                        link: '#',
                                    },
                                    image: '/cms/defaults/landing-premium.svg',
                                },
                            },
                        },
                    ],
                    sectionLibrary: [{
                            variant: 'hero-saas',
                            label: 'Hero (SaaS)',
                            description: 'Hero premium dengan dua CTA dan visual utama.',
                        },
                        {
                            variant: 'stats-bar',
                            label: 'Statistik',
                            description: 'Paparan KPI ringkas dalam kad statistik.',
                        },
                        {
                            variant: 'features-grid',
                            label: 'Ciri-Ciri',
                            description: 'Grid ciri produk dengan ikon, tajuk dan deskripsi.',
                        },
                        {
                            variant: 'cta-banner',
                            label: 'CTA Banner',
                            description: 'Banner call-to-action untuk pendaftaran segera.',
                        },
                        {
                            variant: 'footer-simple',
                            label: 'Footer',
                            description: 'Footer ringkas dengan brand dan pautan utama.',
                        },
                    ],
                    sectionDefaults: {
                        'hero-saas': {
                            style: 'gradient',
                            badge: 'SaaS Kewangan Masjid',
                            title: 'Platform Kewangan Masjid Berbilang Cawangan',
                            subtitle: 'Satukan kutipan, belanja, audit, dan pelaporan dalam satu platform.',
                            primary_cta: {
                                text: 'Daftar Masjid Anda',
                                link: '/login',
                            },
                            secondary_cta: {
                                text: 'Lihat Demo',
                                link: '/login',
                            },
                            image: '/cms/defaults/landing-premium.svg',
                        },
                        'stats-bar': {
                            items: [{
                                    value: '100+',
                                    label: 'Masjid'
                                },
                                {
                                    value: '50K+',
                                    label: 'Transaksi'
                                },
                                {
                                    value: '10K+',
                                    label: 'Laporan'
                                },
                                {
                                    value: '99.9%',
                                    label: 'Ketersediaan'
                                },
                            ],
                        },
                        'features-grid': {
                            title: 'Ciri-Ciri Utama Untuk Operasi Kewangan Moden',
                            subtitle: 'Direka khas untuk pentadbiran masjid pelbagai peranan dan cawangan.',
                            items: [{
                                    icon: 'chart-bar',
                                    title: 'Dashboard Masa Nyata',
                                    desc: 'Pantau kutipan, belanja, dan baki secara langsung.'
                                },
                                {
                                    icon: 'shield-check',
                                    title: 'Kawalan Akses Berperanan',
                                    desc: 'Akses selamat untuk setiap peranan organisasi.'
                                },
                                {
                                    icon: 'document-check',
                                    title: 'Jejak Audit Automatik',
                                    desc: 'Setiap perubahan direkod untuk semakan telus.'
                                },
                            ],
                        },
                        'cta-banner': {
                            title: 'Mulakan Pendigitalan Kewangan Masjid Hari Ini',
                            subtitle: 'Onboarding pantas untuk seluruh pasukan pengurusan.',
                            button: {
                                text: 'Daftar Masjid Anda',
                                link: '/login',
                            },
                        },
                        'footer-simple': {
                            brand: 'MyImam',
                            links: [{
                                    text: 'Tentang',
                                    link: '#'
                                },
                                {
                                    text: 'Ciri-Ciri',
                                    link: '#'
                                },
                                {
                                    text: 'Hubungi',
                                    link: '#'
                                },
                            ],
                        },
                    },
                    sectionFieldDefinitions: {
                        'hero-saas': [{
                                key: 'style',
                                label: 'Hero Style',
                                type: 'select',
                                options: [{
                                        label: 'Gradient Spotlight',
                                        value: 'gradient'
                                    },
                                    {
                                        label: 'Soft Light',
                                        value: 'soft'
                                    },
                                    {
                                        label: 'Dark Split',
                                        value: 'split-dark'
                                    },
                                ],
                            },
                            {
                                key: 'badge',
                                label: 'Badge',
                                type: 'text'
                            },
                            {
                                key: 'title',
                                label: 'Title',
                                type: 'text'
                            },
                            {
                                key: 'subtitle',
                                label: 'Subtitle',
                                type: 'textarea'
                            },
                            {
                                key: 'primary_cta.text',
                                label: 'CTA Utama (Teks)',
                                type: 'text'
                            },
                            {
                                key: 'primary_cta.link',
                                label: 'CTA Utama (Link)',
                                type: 'text'
                            },
                            {
                                key: 'secondary_cta.text',
                                label: 'CTA Sekunder (Teks)',
                                type: 'text'
                            },
                            {
                                key: 'secondary_cta.link',
                                label: 'CTA Sekunder (Link)',
                                type: 'text'
                            },
                            {
                                key: 'image',
                                label: 'Image URL',
                                type: 'text'
                            },
                        ],
                        'stats-bar': [{
                            key: 'items_json',
                            label: 'Items (JSON)',
                            type: 'textarea',
                            help: 'Format: [{"value":"100+","label":"Masjid"}]'
                        }, ],
                        'features-grid': [{
                                key: 'title',
                                label: 'Title',
                                type: 'text'
                            },
                            {
                                key: 'subtitle',
                                label: 'Subtitle',
                                type: 'textarea'
                            },
                            {
                                key: 'items_json',
                                label: 'Items (JSON)',
                                type: 'textarea',
                                help: 'Format: [{"icon":"chart-bar","title":"...","desc":"..."}]'
                            },
                        ],
                        'cta-banner': [{
                                key: 'title',
                                label: 'Title',
                                type: 'text'
                            },
                            {
                                key: 'subtitle',
                                label: 'Subtitle',
                                type: 'textarea'
                            },
                            {
                                key: 'button.text',
                                label: 'Button Text',
                                type: 'text'
                            },
                            {
                                key: 'button.link',
                                label: 'Button Link',
                                type: 'text'
                            },
                        ],
                        'footer-simple': [{
                                key: 'brand',
                                label: 'Brand',
                                type: 'text'
                            },
                            {
                                key: 'links_json',
                                label: 'Links (JSON)',
                                type: 'textarea',
                                help: 'Format: [{"text":"Tentang","link":"#"}]'
                            },
                        ],
                    },
                    library: [{
                            type: 'hero',
                            label: 'Hero Section'
                        },
                        {
                            type: 'text',
                            label: 'Text Block'
                        },
                        {
                            type: 'image',
                            label: 'Image'
                        },
                        {
                            type: 'button',
                            label: 'Button'
                        },
                        {
                            type: 'card',
                            label: 'Card'
                        },
                        {
                            type: 'grid',
                            label: 'Grid'
                        },
                        {
                            type: 'form',
                            label: 'Form'
                        },
                        {
                            type: 'login_form',
                            label: 'Login Form'
                        },
                    ],
                    defaults: {
                        hero: {
                            title: 'Selamat Datang',
                            subtitle: 'Sistem Kewangan Masjid',
                            button_text: 'Log Masuk',
                            button_link: '/login',
                            align: 'center',
                            color: '#1e3a8a',
                            padding: '48px',
                            margin: '0px'
                        },
                        text: {
                            text: 'Masukkan teks anda di sini.',
                            align: 'left',
                            color: '#334155',
                            padding: '24px',
                            margin: '0px'
                        },
                        image: {
                            image_url: 'https://images.unsplash.com/photo-1542810634-71277d95dcbb?w=1200',
                            alt: 'Imej',
                            align: 'center',
                            padding: '0px',
                            margin: '0px'
                        },
                        button: {
                            button_text: 'Klik Sini',
                            button_link: '/login',
                            align: 'left',
                            color: '#2563eb',
                            padding: '8px',
                            margin: '0px'
                        },
                        card: {
                            title: 'Kad Maklumat',
                            text: 'Ringkasan kandungan.',
                            align: 'left',
                            color: '#0f172a',
                            padding: '24px',
                            margin: '0px'
                        },
                        grid: {
                            columns: '3',
                            items: 'Item Pertama\nItem Kedua\nItem Ketiga',
                            align: 'left',
                            padding: '12px',
                            margin: '0px'
                        },
                        form: {
                            title: 'Hubungi Kami',
                            text: 'Hantar maklum balas anda.',
                            button_text: 'Hantar',
                            align: 'left',
                            padding: '24px',
                            margin: '0px'
                        },
                        login_form: {
                            title: 'Log Masuk Sistem',
                            subtitle: 'Masukkan emel dan kata laluan.',
                            show_demo_accounts: '1',
                            align: 'left',
                            padding: '24px',
                            margin: '0px'
                        },
                    },
                    init() {
                        const comps = (initialLayout && Array.isArray(initialLayout.components)) ? initialLayout.components :
                    [];
                        this.components = comps.map((component) => this.hydrateComponent(component));
                        this.selectedVersionId = this.versions.length ? this.versions[0].id : null;
                        this.captureHistory(true);
                        this.fetchMediaLibrary();
                        this.fetchPresets();
                        this.$nextTick(() => this.initSortable());
                    },
                    togglePreviewMode() {
                        this.previewMode = !this.previewMode;

                        if (this.previewMode) {
                            this.scheduleServerPreview();
                        }
                    },
                    scheduleServerPreview() {
                        if (!this.previewMode) {
                            return;
                        }

                        if (this.previewDebounceTimer) {
                            clearTimeout(this.previewDebounceTimer);
                        }

                        this.previewDebounceTimer = setTimeout(() => {
                            this.renderServerPreview();
                        }, 120);
                    },
                    getTargetMasjidId() {
                        const selectField = document.querySelector('select[name="target_masjid_id"]');
                        if (selectField) {
                            return selectField.value || '';
                        }

                        const hiddenField = document.querySelector('input[name="target_masjid_id"]');
                        return hiddenField ? (hiddenField.value || '') : '';
                    },
                    async renderServerPreview() {
                        if (!this.previewMode) {
                            return;
                        }

                        this.previewLoading = true;
                        this.previewError = '';

                        try {
                            const response = await fetch(this.previewRenderUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                        'content'),
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    target_masjid_id: this.getTargetMasjidId(),
                                    content_json: {
                                        components: this.components.map((component) => ({
                                            type: component.type,
                                            ...(component.type === 'section' && component
                                                .variant ? {
                                                    variant: component.variant
                                                } : {}),
                                            props: component.props || {},
                                            children: component.children || [],
                                        })),
                                    },
                                }),
                            });

                            if (!response.ok) {
                                const error = await response.json();
                                throw new Error(error.message || 'Preview render failed.');
                            }

                            const result = await response.json();
                            this.previewHtml = result.html || '';
                        } catch (error) {
                            this.previewError = error.message || 'Failed to render preview.';
                        } finally {
                            this.previewLoading = false;
                        }
                    },
                    deepClone(value) {
                        return JSON.parse(JSON.stringify(value));
                    },
                    buildState() {
                        return {
                            title: this.title,
                            seoTitle: this.seoTitle,
                            seoMetaDescription: this.seoMetaDescription,
                            components: this.deepClone(this.components),
                            isActive: this.isActive,
                        };
                    },
                    captureHistory(resetRedo = true) {
                        const state = this.buildState();
                        const serialized = JSON.stringify(state);
                        const last = this.historyStack.length ?
                            JSON.stringify(this.historyStack[this.historyStack.length - 1]) :
                            null;

                        if (serialized !== last) {
                            this.historyStack.push(state);
                            if (this.historyStack.length > 100) {
                                this.historyStack.shift();
                            }
                        }

                        if (resetRedo) {
                            this.redoStack = [];
                        }
                    },
                    applyState(state) {
                        this.title = state.title || '';
                        this.seoTitle = state.seoTitle || state.title || '';
                        this.seoMetaDescription = state.seoMetaDescription || '';
                        this.isActive = !!state.isActive;
                        this.components = (state.components || []).map((component) => this.hydrateComponent(component));
                        this.selectedPath = {
                            parent: null,
                            child: null,
                        };
                        this.$nextTick(() => this.initSortable());
                    },
                    undo() {
                        if (this.historyStack.length <= 1) {
                            return;
                        }

                        const current = this.historyStack.pop();
                        if (current) {
                            this.redoStack.push(current);
                        }

                        const previous = this.historyStack[this.historyStack.length - 1];
                        if (previous) {
                            this.applyState(this.deepClone(previous));
                        }
                    },
                    redo() {
                        if (this.redoStack.length === 0) {
                            return;
                        }

                        const next = this.redoStack.pop();
                        if (!next) {
                            return;
                        }

                        this.applyState(this.deepClone(next));
                        this.historyStack.push(this.deepClone(next));
                    },
                    hydrateComponent(component) {
                        const type = component.type || 'text';

                        if (type === 'section') {
                            const variant = component.variant || component?.props?.variant || 'hero-saas';
                            return {
                                _id: component._id || this.uuid(),
                                type,
                                variant,
                                props: {
                                    ...(this.sectionDefaults[variant] || {}),
                                    ...(component.props || {})
                                },
                                children: [],
                            };
                        }

                        return {
                            _id: component._id || this.uuid(),
                            type,
                            props: {
                                ...(this.defaults[type] || {}),
                                ...(component.props || {})
                            },
                            children: Array.isArray(component.children) ? component.children.map((child) => this
                                .hydrateComponent(child)) : [],
                        };
                    },
                    uuid() {
                        return 'cmp-' + Math.random().toString(36).slice(2, 10);
                    },
                    componentLabel(type) {
                        if (type === 'section') {
                            return 'Section';
                        }

                        const item = this.library.find((entry) => entry.type === type);
                        return item ? item.label : type;
                    },
                    componentSummary(component) {
                        if (component.type === 'section') {
                            const section = this.sectionLibrary.find((entry) => entry.variant === component.variant);
                            return section ? section.label : (component.variant || 'Section');
                        }

                        return component.props.title || component.props.text || component.props.button_text || component.props
                            .image_url || 'Tiada ringkasan';
                    },
                    addSection(variant) {
                        this.components.push(this.hydrateComponent({
                            type: 'section',
                            variant,
                            props: this.sectionDefaults[variant] || {},
                        }));
                        this.selectedPath = {
                            parent: this.components.length - 1,
                            child: null,
                        };
                        this.captureHistory();
                        this.$nextTick(() => this.initSortable());
                    },
                    insertSectionTemplate(templateId) {
                        const template = this.sectionTemplates.find((item) => item.id === templateId);
                        if (!template || !template.component) {
                            return;
                        }

                        this.components.push(this.hydrateComponent(this.deepClone(template.component)));
                        this.selectedPath = {
                            parent: this.components.length - 1,
                            child: null,
                        };
                        this.captureHistory();
                        this.$nextTick(() => this.initSortable());
                    },
                    stripComponentIds(component) {
                        if (!component || typeof component !== 'object') {
                            return;
                        }

                        delete component._id;

                        if (Array.isArray(component.children)) {
                            component.children.forEach((child) => this.stripComponentIds(child));
                        }
                    },
                    duplicateComponent(index) {
                        const current = this.components[index];
                        if (!current) {
                            return;
                        }

                        const clone = this.deepClone(current);
                        this.stripComponentIds(clone);

                        this.components.splice(index + 1, 0, this.hydrateComponent(clone));
                        this.selectedPath = {
                            parent: index + 1,
                            child: null,
                        };
                        this.captureHistory();
                        this.$nextTick(() => this.initSortable());
                    },
                    flashReorderPulse(componentId) {
                        if (!componentId) {
                            return;
                        }

                        this.reorderPulseId = componentId;
                        setTimeout(() => {
                            if (this.reorderPulseId === componentId) {
                                this.reorderPulseId = null;
                            }
                        }, 420);
                    },
                    startLibraryDrag(variant) {
                        this.draggingSectionVariant = variant;
                    },
                    endLibraryDrag() {
                        this.draggingSectionVariant = null;
                    },
                    dropSectionOnCanvas() {
                        if (!this.draggingSectionVariant) {
                            return;
                        }

                        this.addSection(this.draggingSectionVariant);
                        this.draggingSectionVariant = null;
                    },
                    addRootComponent(type) {
                        this.components.push(this.hydrateComponent({
                            type,
                            props: this.defaults[type] || {}
                        }));
                        this.selectedPath = {
                            parent: this.components.length - 1,
                            child: null
                        };
                        this.captureHistory();
                        this.$nextTick(() => this.initSortable());
                    },
                    addChildComponent(type) {
                        if (!this.canAddChildTarget()) {
                            return;
                        }

                        const parentIndex = this.selectedPath.parent;
                        const parent = this.components[parentIndex];
                        parent.children = Array.isArray(parent.children) ? parent.children : [];
                        parent.children.push(this.hydrateComponent({
                            type,
                            props: this.defaults[type] || {}
                        }));
                        this.selectedPath = {
                            parent: parentIndex,
                            child: parent.children.length - 1
                        };
                        this.captureHistory();
                        this.$nextTick(() => this.initSortable());
                    },
                    removeComponent(index) {
                        this.components.splice(index, 1);
                        if (this.selectedPath.parent === index) {
                            this.selectedPath = {
                                parent: null,
                                child: null
                            };
                        } else if (this.selectedPath.parent !== null && this.selectedPath.parent > index) {
                            this.selectedPath.parent -= 1;
                        }
                        this.captureHistory();
                    },
                    removeChild(parentIndex, childIndex) {
                        const parent = this.components[parentIndex];
                        if (!parent || !Array.isArray(parent.children)) {
                            return;
                        }

                        parent.children.splice(childIndex, 1);

                        if (this.selectedPath.parent === parentIndex && this.selectedPath.child === childIndex) {
                            this.selectedPath = {
                                parent: parentIndex,
                                child: null
                            };
                        }

                        this.captureHistory();
                    },
                    selectComponent(parentIndex, childIndex = null) {
                        this.selectedPath = {
                            parent: parentIndex,
                            child: childIndex
                        };
                    },
                    isSelected(parentIndex, childIndex) {
                        return this.selectedPath.parent === parentIndex && this.selectedPath.child === childIndex;
                    },
                    componentCanContainChildren(component) {
                        return ['card', 'grid'].includes(component.type);
                    },
                    canAddChildTarget() {
                        if (this.selectedPath.parent === null) {
                            return false;
                        }

                        const parent = this.components[this.selectedPath.parent];
                        return !!parent && this.componentCanContainChildren(parent);
                    },
                    getByPath(object, path) {
                        return path.split('.').reduce((cursor, segment) => {
                            if (cursor === null || cursor === undefined) {
                                return undefined;
                            }
                            return cursor[segment];
                        }, object);
                    },
                    setByPath(object, path, value) {
                        const parts = path.split('.');
                        let cursor = object;

                        for (let index = 0; index < parts.length - 1; index += 1) {
                            const key = parts[index];
                            if (typeof cursor[key] !== 'object' || cursor[key] === null) {
                                cursor[key] = {};
                            }
                            cursor = cursor[key];
                        }

                        cursor[parts[parts.length - 1]] = value;
                    },
                    getEditableFieldValue(field) {
                        if (!this.selectedComponent) {
                            return '';
                        }

                        if (field.key === 'items_json') {
                            return JSON.stringify(this.selectedComponent.props.items || [], null, 2);
                        }

                        if (field.key === 'links_json') {
                            return JSON.stringify(this.selectedComponent.props.links || [], null, 2);
                        }

                        return this.getByPath(this.selectedComponent.props || {}, field.key) ?? '';
                    },
                    updateEditableField(field, value) {
                        if (!this.selectedComponent) {
                            return;
                        }

                        this.propertyError = '';

                        if (field.key === 'items_json') {
                            try {
                                const parsed = JSON.parse(value || '[]');
                                if (!Array.isArray(parsed)) {
                                    throw new Error('Items mesti dalam bentuk array JSON.');
                                }
                                this.selectedComponent.props.items = parsed;
                                this.captureHistory();
                            } catch (error) {
                                this.propertyError = error.message || 'Format JSON items tidak sah.';
                            }
                            return;
                        }

                        if (field.key === 'links_json') {
                            try {
                                const parsed = JSON.parse(value || '[]');
                                if (!Array.isArray(parsed)) {
                                    throw new Error('Links mesti dalam bentuk array JSON.');
                                }
                                this.selectedComponent.props.links = parsed;
                                this.captureHistory();
                            } catch (error) {
                                this.propertyError = error.message || 'Format JSON links tidak sah.';
                            }
                            return;
                        }

                        this.setByPath(this.selectedComponent.props, field.key, value);
                        this.captureHistory();
                    },
                    inferLegacyFieldDef(key) {
                        if (key === 'show_demo_accounts') {
                            return {
                                key,
                                label: key,
                                type: 'checkbox'
                            };
                        }

                        const textareaKeys = ['text', 'items', 'subtitle'];
                        return {
                            key,
                            label: key,
                            type: textareaKeys.includes(key) ? 'textarea' : 'text',
                        };
                    },
                    updateProp(field, value) {
                        if (!this.selectedComponent) return;
                        this.selectedComponent.props[field] = value;
                        this.captureHistory();
                    },
                    async uploadImage(event) {
                        const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;
                        if (!file || !this.selectedComponent) {
                            return;
                        }

                        this.uploading = true;
                        this.uploadError = '';

                        const payload = new FormData();
                        payload.append('image', file);

                        try {
                            const response = await fetch(this.uploadUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                        'content'),
                                    'Accept': 'application/json',
                                },
                                body: payload,
                            });

                            if (!response.ok) {
                                throw new Error('Gagal upload imej.');
                            }

                            const result = await response.json();
                            this.updateProp('image_url', result.url || '');
                            await this.fetchMediaLibrary();
                        } catch (error) {
                            this.uploadError = error.message || 'Ralat semasa memuat naik imej.';
                        } finally {
                            this.uploading = false;
                            event.target.value = '';
                        }
                    },
                    async fetchMediaLibrary() {
                        try {
                            const response = await fetch(this.mediaLibraryUrl, {
                                headers: {
                                    'Accept': 'application/json',
                                },
                            });

                            if (!response.ok) {
                                throw new Error('Gagal memuatkan media library.');
                            }

                            const result = await response.json();
                            this.mediaItems = Array.isArray(result.items) ? result.items : [];
                        } catch (error) {
                            this.uploadError = error.message || 'Ralat semasa memuatkan media library.';
                        }
                    },
                    selectMedia(url) {
                        if (!this.selectedComponent) {
                            return;
                        }

                        this.updateProp('image_url', url);
                    },
                    openAiModal() {
                        this.aiModalOpen = true;
                        this.aiDescription = '';
                        this.aiError = '';
                        this.aiPageType = 'landing';
                    },
                    closeAiModal() {
                        this.aiModalOpen = false;
                        this.aiDescription = '';
                        this.aiError = '';
                    },
                    selectPreset(preset) {
                        this.aiPageType = preset.type;
                        this.aiDescription = preset.description;
                    },
                    async generatePage() {
                        if (!this.aiDescription || this.aiDescription.length < 10) {
                            this.aiError = 'Penerangan mestilah sekurang-kurangnya 10 aksara.';
                            return;
                        }

                        this.aiLoading = true;
                        this.aiError = '';

                        try {
                            const response = await fetch(this.generateUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                        'content'),
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify({
                                    type: this.aiPageType,
                                    description: this.aiDescription,
                                }),
                            });

                            if (!response.ok) {
                                const error = await response.json();
                                throw new Error(error.message || 'Gagal menjana halaman');
                            }

                            const result = await response.json();
                            if (!result.success || !result.layout) {
                                throw new Error(result.message || 'Rekabentuk tidak sah');
                            }

                            // Load generated layout into builder
                            const layout = result.layout;
                            if (layout.components && Array.isArray(layout.components)) {
                                this.components = layout.components.map((component) => this.hydrateComponent(component));
                                this.$nextTick(() => {
                                    this.captureHistory();
                                    this.initSortable();
                                });
                            }

                            this.closeAiModal();

                            // Show a notice if template fallback was used
                            if (result.fallback) {
                                this.aiError = '';
                                // Brief notification via console; user sees components on canvas
                                console.info(
                                    '[CMS AI] Template fallback used — configure OPENAI_API_KEY for AI generation.');
                            }
                        } catch (error) {
                            this.aiError = error.message || 'Ralat semasa menjana halaman.';
                        } finally {
                            this.aiLoading = false;
                        }
                    },
                    async fetchPresets() {
                        try {
                            const response = await fetch(this.presetsUrl, {
                                headers: {
                                    'Accept': 'application/json',
                                },
                            });

                            if (!response.ok) {
                                return;
                            }

                            const result = await response.json();
                            this.aiPresets = Object.values(result.presets || {});
                        } catch (error) {
                            console.error('Failed to fetch AI presets:', error);
                        }
                    },
                    initSortable() {
                        const canvas = document.getElementById('cms-canvas');
                        if (!canvas) return;

                        if (!canvas._sortable) {
                            canvas._sortable = new Sortable(canvas, {
                                animation: 220,
                                ghostClass: 'cms-sortable-ghost',
                                chosenClass: 'cms-sortable-chosen',
                                dragClass: 'cms-sortable-drag',
                                onEnd: (event) => {
                                    const order = Array.from(canvas.querySelectorAll('[data-root-id]')).map((el) =>
                                        el
                                        .dataset.rootId);
                                    this.components = order
                                        .map((id) => this.components.find((component) => component._id === id))
                                        .filter(Boolean);
                                    this.captureHistory();

                                    const movedId = event?.item?.dataset?.rootId || null;
                                    this.flashReorderPulse(movedId);
                                }
                            });
                        }

                        const childContainers = canvas.querySelectorAll('[data-child-container]');
                        childContainers.forEach((container) => {
                            if (container._sortable) {
                                return;
                            }

                            container._sortable = new Sortable(container, {
                                group: {
                                    name: 'cms-builder-children',
                                    pull: true,
                                    put: true,
                                },
                                animation: 220,
                                ghostClass: 'cms-sortable-ghost',
                                chosenClass: 'cms-sortable-chosen',
                                dragClass: 'cms-sortable-drag',
                                onStart: () => {
                                    this.toggleChildDropzones(canvas, true);
                                },
                                onAdd: () => {
                                    this.syncChildrenFromDom(canvas);
                                },
                                onUpdate: () => {
                                    this.syncChildrenFromDom(canvas);
                                },
                                onRemove: () => {
                                    this.syncChildrenFromDom(canvas);
                                },
                                onEnd: () => {
                                    this.syncChildrenFromDom(canvas);
                                    this.toggleChildDropzones(canvas, false);
                                    this.captureHistory();
                                    this.$nextTick(() => this.initSortable());
                                }
                            });
                        });
                    },
                    buildChildLookup() {
                        const lookup = {};

                        this.components.forEach((parent) => {
                            (parent.children || []).forEach((child) => {
                                lookup[child._id] = child;
                            });
                        });

                        return lookup;
                    },
                    syncChildrenFromDom(canvas) {
                        const lookup = this.buildChildLookup();

                        const childContainers = canvas.querySelectorAll('[data-child-container]');
                        childContainers.forEach((container) => {
                            const parentIndex = Number(container.dataset.parentIndex);
                            const parent = this.components[parentIndex];

                            if (!parent) {
                                return;
                            }

                            const orderedChildren = Array.from(container.querySelectorAll('[data-child-id]'))
                                .map((el) => lookup[el.dataset.childId])
                                .filter(Boolean);

                            parent.children = orderedChildren;
                        });
                    },
                    toggleChildDropzones(canvas, active) {
                        this.childDropzonesActive = active;

                        const childContainers = canvas.querySelectorAll('[data-child-container]');
                        childContainers.forEach((container) => {
                            container.classList.toggle('ring-2', active);
                            container.classList.toggle('ring-emerald-300', active);
                            container.classList.toggle('bg-emerald-50', active);
                        });
                    },
                    prepareSubmit() {
                        this.$refs.titleInput.value = this.title;
                        this.$refs.seoTitleInput.value = this.seoTitle;
                        this.$refs.seoMetaDescriptionInput.value = this.seoMetaDescription;
                        if (!this.$refs.actionInput.value) {
                            this.$refs.actionInput.value = 'save';
                        }
                        this.$refs.contentInput.value = JSON.stringify({
                            components: this.components.map((component) => ({
                                type: component.type,
                                ...(component.type === 'section' && component.variant ? {
                                    variant: component.variant
                                } : {}),
                                props: component.props,
                                children: component.children || [],
                            }))
                        });
                    },
                    submitAction(action) {
                        this.$refs.actionInput.value = action;

                        if (action === 'publish') {
                            this.isActive = true;
                        }

                        if (action === 'unpublish') {
                            this.isActive = false;
                        }

                        this.prepareSubmit();
                        this.$refs.builderForm.submit();
                    },
                    esc(value) {
                        return String(value ?? '')
                            .replaceAll('&', '&amp;')
                            .replaceAll('<', '&lt;')
                            .replaceAll('>', '&gt;')
                            .replaceAll('"', '&quot;')
                            .replaceAll("'", '&#039;');
                    },
                    renderPreviewChildren(children) {
                        if (!Array.isArray(children) || children.length === 0) {
                            return '';
                        }

                        return children.map((child) => this.renderPreviewComponent(child)).join('');
                    },
                    flattenComponentSummaries(components, prefix = '') {
                        if (!Array.isArray(components)) {
                            return [];
                        }

                        return components.flatMap((component, index) => {
                            const label = this.componentLabel(component.type || 'unknown');
                            const summary = this.componentSummary(component);
                            const current = `${prefix}${index + 1}. ${label} - ${summary}`;
                            const children = this.flattenComponentSummaries(component.children || [],
                                `${prefix}${index + 1}.`);
                            return [current, ...children];
                        });
                    },
                    flattenComponentTypes(components) {
                        if (!Array.isArray(components)) {
                            return [];
                        }

                        return components.flatMap((component) => [
                            component.type || 'unknown',
                            ...this.flattenComponentTypes(component.children || []),
                        ]);
                    },
                    countComponentTypes(components) {
                        return this.flattenComponentTypes(components).reduce((counts, type) => {
                            counts[type] = (counts[type] || 0) + 1;
                            return counts;
                        }, {});
                    },
                    renderPreviewComponent(component) {
                        const props = component.props || {};
                        const childrenHtml = this.renderPreviewChildren(component.children || []);

                        if (component.type === 'section') {
                            switch (component.variant) {
                                case 'hero-saas':
                                    return `<section class="rounded-xl bg-gradient-to-br from-slate-900 via-blue-900 to-cyan-800 p-6 text-white"><p class="text-xs uppercase tracking-wide text-cyan-200">${this.esc(props.badge || '')}</p><h2 class="mt-2 text-2xl font-bold">${this.esc(props.title || 'Hero SaaS')}</h2><p class="mt-2 text-sm text-cyan-100">${this.esc(props.subtitle || '')}</p></section>`;
                                case 'stats-bar': {
                                    const items = Array.isArray(props.items) ? props.items : [];
                                    const html = items.map((item) =>
                                        `<div class="rounded-lg border border-slate-200 bg-white p-3 text-center"><p class="text-lg font-bold text-slate-900">${this.esc(item.value || '-')}</p><p class="text-xs text-slate-500">${this.esc(item.label || '')}</p></div>`
                                    ).join('');
                                    return `<section class="rounded-xl border border-slate-200 bg-slate-50 p-3"><div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">${html}</div></section>`;
                                }
                                case 'features-grid': {
                                    const items = Array.isArray(props.items) ? props.items : [];
                                    const html = items.map((item) =>
                                        `<article class="rounded-lg border border-slate-200 bg-white p-3"><h3 class="text-sm font-semibold text-slate-800">${this.esc(item.title || 'Feature')}</h3><p class="mt-1 text-xs text-slate-600">${this.esc(item.desc || '')}</p></article>`
                                    ).join('');
                                    return `<section class="rounded-xl border border-slate-200 bg-slate-50 p-4"><h3 class="text-base font-bold text-slate-900">${this.esc(props.title || '')}</h3><p class="mt-1 text-xs text-slate-600">${this.esc(props.subtitle || '')}</p><div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">${html}</div></section>`;
                                }
                                case 'cta-banner':
                                    return `<section class="rounded-xl bg-slate-900 p-5 text-white"><h3 class="text-lg font-bold">${this.esc(props.title || '')}</h3><p class="mt-1 text-sm text-slate-200">${this.esc(props.subtitle || '')}</p></section>`;
                                case 'footer-simple':
                                    return `<footer class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-600">${this.esc(props.brand || 'Footer')}</footer>`;
                                default:
                                    return `<section class="rounded-xl border border-slate-200 bg-white p-3 text-xs text-slate-500">Unknown section variant: ${this.esc(component.variant || 'unknown')}</section>`;
                            }
                        }

                        switch (component.type) {
                            case 'hero':
                                return `<section class="rounded-xl bg-gradient-to-br from-sky-900 via-cyan-800 to-emerald-700 p-6 text-white"><h2 class="text-2xl font-bold">${this.esc(props.title || 'Hero Title')}</h2><p class="mt-2 text-sm text-cyan-100">${this.esc(props.subtitle || '')}</p></section>`;
                            case 'text':
                                return `<section class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-sm text-slate-700">${this.esc(props.text || '')}</p></section>`;
                            case 'image':
                                return `<section class="rounded-xl border border-slate-200 bg-white p-3">${props.image_url ? `<img class="w-full rounded-lg" src="${this.esc(props.image_url)}" alt="${this.esc(props.alt || 'image')}"/>` : '<p class="text-xs text-slate-400">No image</p>'}</section>`;
                            case 'button':
                                return `<section class="rounded-xl border border-slate-200 bg-white p-4"><span class="inline-flex rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white">${this.esc(props.button_text || 'Button')}</span></section>`;
                            case 'card':
                                return `<section class="rounded-xl border border-slate-200 bg-white p-4"><h3 class="text-sm font-bold text-slate-800">${this.esc(props.title || 'Card')}</h3><p class="mt-1 text-xs text-slate-600">${this.esc(props.text || '')}</p><div class="mt-3 space-y-2">${childrenHtml}</div></section>`;
                            case 'grid': {
                                const items = String(props.items || '').split(/\r\n|\r|\n/).filter(Boolean).map((i) =>
                                    `<div class="rounded-lg border border-slate-200 bg-white p-2 text-xs text-slate-700">${this.esc(i)}</div>`
                                ).join('');
                                return `<section class="rounded-xl border border-slate-200 bg-slate-50 p-3"><div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">${items}</div><div class="mt-3 space-y-2">${childrenHtml}</div></section>`;
                            }
                            case 'form':
                                return `<section class="rounded-xl border border-slate-200 bg-white p-4"><h3 class="text-sm font-bold text-slate-800">${this.esc(props.title || 'Form')}</h3><p class="mt-1 text-xs text-slate-600">${this.esc(props.text || '')}</p></section>`;
                            case 'login_form':
                                return `<section class="rounded-xl border border-slate-200 bg-white p-4"><h3 class="text-sm font-bold text-slate-800">${this.esc(props.title || 'Login Form')}</h3><p class="mt-1 text-xs text-slate-600">${this.esc(props.subtitle || '')}</p></section>`;
                            default:
                                return `<section class="rounded-xl border border-slate-200 bg-white p-3 text-xs text-slate-500">Unknown component: ${this.esc(component.type || 'unknown')}</section>`;
                        }
                    },
                    get selectedComponent() {
                        if (this.selectedPath.parent === null) {
                            return null;
                        }

                        const parent = this.components[this.selectedPath.parent] || null;
                        if (!parent) {
                            return null;
                        }

                        if (this.selectedPath.child === null) {
                            return parent;
                        }

                        return (parent.children || [])[this.selectedPath.child] || null;
                    },
                    get editableFields() {
                        if (!this.selectedComponent) {
                            return [];
                        }

                        if (this.selectedComponent.type === 'section') {
                            const variant = this.selectedComponent.variant || 'hero-saas';
                            return this.sectionFieldDefinitions[variant] || [];
                        }

                        const propKeys = Object.keys(this.selectedComponent.props || {});
                        const merged = [...new Set([...propKeys, ...this.commonStyleFields])];
                        return merged
                            .filter((field) => field !== '_children_html')
                            .map((field) => this.inferLegacyFieldDef(field));
                    },
                    get selectedVersion() {
                        return this.versions.find((version) => version.id === this.selectedVersionId) || null;
                    },
                    get selectedVersionMeta() {
                        if (!this.selectedVersion) {
                            return 'No version selected';
                        }

                        return `V${this.selectedVersion.version_no} · ${String(this.selectedVersion.action || '').toUpperCase()}`;
                    },
                    get previewViewportClass() {
                        return {
                            'max-w-full': this.previewViewport === 'desktop',
                            'max-w-3xl': this.previewViewport === 'tablet',
                            'max-w-sm': this.previewViewport === 'mobile',
                        };
                    },
                    get previewViewportLabel() {
                        if (this.previewViewport === 'mobile') {
                            return 'Mobile 390px';
                        }

                        if (this.previewViewport === 'tablet') {
                            return 'Tablet 768px';
                        }

                        return 'Desktop fluid';
                    },
                    get previewViewportShellClass() {
                        if (this.previewViewport === 'mobile') {
                            return 'mx-auto rounded-[1.75rem] border border-slate-300 bg-white p-3 shadow-xl';
                        }

                        if (this.previewViewport === 'tablet') {
                            return 'mx-auto rounded-[1.25rem] border border-slate-300 bg-white p-3 shadow-lg';
                        }

                        return 'rounded-xl border border-slate-200 bg-white p-3';
                    },
                    get componentsSignature() {
                        return JSON.stringify(this.components.map((component) => ({
                            type: component.type,
                            ...(component.type === 'section' && component.variant ? {
                                variant: component.variant
                            } : {}),
                            props: component.props || {},
                            children: component.children || [],
                        })));
                    },
                    get currentComponentCount() {
                        return this.flattenComponentSummaries(this.components).length;
                    },
                    get selectedVersionComponentCount() {
                        const versionComponents = this.selectedVersion?.content_json?.components || [];
                        return this.flattenComponentSummaries(versionComponents).length;
                    },
                    get componentTypeDiffs() {
                        const versionComponents = this.selectedVersion?.content_json?.components || [];
                        const currentCounts = this.countComponentTypes(this.components);
                        const versionCounts = this.countComponentTypes(versionComponents);
                        const allTypes = [...new Set([...Object.keys(currentCounts), ...Object.keys(versionCounts)])];

                        return allTypes.sort().map((type) => {
                            const currentCount = currentCounts[type] || 0;
                            const versionCount = versionCounts[type] || 0;
                            let status = 'unchanged';

                            if (currentCount > 0 && versionCount === 0) {
                                status = 'added';
                            } else if (currentCount === 0 && versionCount > 0) {
                                status = 'removed';
                            } else if (currentCount !== versionCount) {
                                status = currentCount > versionCount ? 'added' : 'removed';
                            }

                            return {
                                type,
                                label: this.componentLabel(type),
                                currentCount,
                                versionCount,
                                status,
                                statusLabel: status === 'added' ?
                                    'Added in current draft' : status === 'removed' ?
                                    'Removed from current draft' : 'Unchanged',
                            };
                        });
                    },
                    get canUndo() {
                        return this.historyStack.length > 1;
                    },
                    get canRedo() {
                        return this.redoStack.length > 0;
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>
