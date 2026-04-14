@php
    $sidebarService = app(\App\Services\SidebarService::class);
    $menus = $sidebarService->getFilteredMenu();
    $initialBadgeCounts = $sidebarService->getBadgeCounts();

    $badgeToneClass = function (string $tone): string {
        return match ($tone) {
            'amber' => 'bg-amber-500/20 text-amber-300',
            'red' => 'bg-red-500/20 text-red-300',
            'green' => 'bg-green-500/20 text-green-300',
            default => 'bg-slate-500/20 text-slate-300',
        };
    };
@endphp

{{-- Initialise Alpine global store with server-rendered badge counts so badges are present before first poll --}}
<script>
    document.addEventListener('alpine:init', function() {
        Alpine.store('sidebarBadges', @js($initialBadgeCounts));
    });
</script>

<div class="flex h-full flex-col bg-gray-900 text-gray-100">
    <div class="flex h-16 items-center border-b border-gray-800 px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-800 text-indigo-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path
                        d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 101.414 1.414L4 10.414V17a1 1 0 001 1h3a1 1 0 001-1v-3h2v3a1 1 0 001 1h3a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                </svg>
            </span>
            <div>
                <p class="text-sm font-semibold text-white">Imam</p>
                <p class="text-xs text-gray-400">Internal Panel</p>
            </div>
        </a>
    </div>

    {{-- Poll badge counts every 30 s using recursive setTimeout (avoids overlap on slow networks) --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-4" x-init="(function poll() {
        setTimeout(function() {
            fetch('{{ route('sidebar.badge-counts') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? ''
                    }
                })
                .then(function(r) { return r.ok ? r.json() : null; })
                .then(function(data) { if (data) Alpine.store('sidebarBadges', data); })
                .catch(function() {})
                .finally(function() { poll(); });
        }, 30000);
    })();">
        {{-- Loop through menu sections from config/sidebar.php --}}
        @forelse ($menus as $section)
            <section x-data="{
                open: @js($section['isOpen']),
                storageKey: @js('sidebar.section.' . $section['key']),
                init() {
                    const saved = window.localStorage.getItem(this.storageKey);
                    if (saved !== null) { this.open = saved === 'true'; }
                },
                toggle() {
                    this.open = !this.open;
                    window.localStorage.setItem(this.storageKey, this.open ? 'true' : 'false');
                }
            }" class="space-y-1">

                {{-- Section header --}}
                @if ($section['collapsible'])
                    <button type="button" @click="toggle()"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-500 hover:bg-gray-800/60 hover:text-gray-300">
                        <span class="flex items-center gap-2">
                            <span>{{ $section['section'] }}</span>
                            @if (!empty($section['badge']))
                                <span
                                    class="inline-flex min-w-6 items-center justify-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badgeToneClass($section['badge']['tone'] ?? 'slate') }}">
                                    {{ number_format((int) $section['badge']['value']) }}
                                </span>
                            @endif
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform"
                            :class="open ? 'rotate-90' : 'rotate-0'" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M6.293 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L10.586 10 6.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                @else
                    <div
                        class="flex items-center gap-2 px-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-500">
                        <span>{{ $section['section'] }}</span>
                        @if (!empty($section['badge']))
                            <span
                                class="inline-flex min-w-6 items-center justify-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badgeToneClass($section['badge']['tone'] ?? 'slate') }}">
                                {{ number_format((int) $section['badge']['value']) }}
                            </span>
                        @endif
                    </div>
                @endif

                {{-- Section items --}}
                <div x-show="{{ $section['collapsible'] ? 'open' : 'true' }}" class="space-y-1">
                    @foreach ($section['items'] as $item)
                        @if (!empty($item['children']))
                            {{-- ── Parent item: expandable group with nested children ── --}}
                            <div x-data="{
                                open: @js($item['isActive']),
                                storageKey: @js('sidebar.parent.' . ($item['key'] ?? str()->slug($item['title'], '_'))),
                                init() {
                                    const saved = window.localStorage.getItem(this.storageKey);
                                    if (saved !== null) { this.open = saved === 'true'; }
                                },
                                toggle() {
                                    this.open = !this.open;
                                    window.localStorage.setItem(this.storageKey, this.open ? 'true' : 'false');
                                }
                            }" class="space-y-0.5">
                                <button type="button" @click="toggle()"
                                    class="group flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ $item['isActive'] ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                    <span
                                        class="{{ $item['isActive'] ? 'text-gray-300' : 'text-gray-400' }} group-hover:text-gray-200">
                                        {!! $sidebarService->iconSvg($item['icon'] ?? 'home') !!}
                                    </span>
                                    <span class="flex-1 text-left">{{ $item['title'] }}</span>
                                    {{-- Expand chevron --}}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 shrink-0 text-gray-500 transition-transform duration-150"
                                        :class="open ? 'rotate-90' : 'rotate-0'" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>

                                {{-- Nested children --}}
                                <div x-show="open" class="ml-3 space-y-0.5 border-l-2 border-gray-800 pl-3 pt-0.5">
                                    @foreach ($item['children'] as $child)
                                        @if (!empty($child['routeName']))
                                            <a href="{{ route($child['routeName']) }}"
                                                class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ $child['isActive'] ? 'bg-gray-800 text-white shadow' : 'text-gray-400 hover:bg-gray-800 hover:text-gray-200' }}">
                                                <span
                                                    class="{{ $child['isActive'] ? 'text-gray-300' : 'text-gray-500' }} group-hover:text-gray-300">
                                                    {!! $sidebarService->iconSvg($child['icon'] ?? 'home') !!}
                                                </span>
                                                <span class="flex-1">{{ $child['title'] }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @elseif (!empty($item['routeName']))
                            {{-- ── Regular item ── --}}
                            <a href="{{ route($item['routeName']) }}"
                                class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ $item['isActive'] ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                                <span
                                    class="{{ $item['isActive'] ? 'text-gray-300' : 'text-gray-400' }} group-hover:text-gray-200">
                                    {!! $sidebarService->iconSvg($item['icon'] ?? 'home') !!}
                                </span>
                                <span class="flex-1">{{ $item['title'] }}</span>
                                {{-- Live badge (hidden when count = 0, updates via Alpine store polling) --}}
                                @if (!empty($item['badge']['type']))
                                    <span x-show="($store.sidebarBadges['{{ $item['badge']['type'] }}'] ?? 0) > 0"
                                        x-text="$store.sidebarBadges['{{ $item['badge']['type'] }}'] ?? 0"
                                        class="inline-flex min-w-6 items-center justify-center rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badgeToneClass($item['badge']['tone'] ?? 'slate') }}">
                                    </span>
                                @endif
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @empty
            {{-- Fallback message if no authorized menu items exist --}}
            <div class="flex items-center justify-center py-8">
                <p class="text-sm text-gray-500">No menu items available</p>
            </div>
        @endforelse
    </nav>
</div>
