@php
    $sidebarService = app(\App\Services\SidebarService::class);
    $menus = $sidebarService->getFilteredMenu();
@endphp

<div class="flex h-full flex-col bg-gray-900 text-gray-100">
    <div class="flex h-16 items-center border-b border-gray-800 px-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-800 text-indigo-300">
                {!! $sidebarService->iconSvg('home') !!}
            </span>
            <div>
                <p class="text-sm font-semibold text-white">{{ __('menu.brand') }}</p>
                <p class="text-xs text-gray-400">{{ __('menu.panel') }}</p>
            </div>
        </a>
    </div>

    <nav class="flex-1 space-y-4 overflow-y-auto px-3 py-4">
        @forelse ($menus as $section)
            <section x-data="{ open: @js($section['default_open'] || $section['isActive']) }" class="space-y-1">
                @if ($section['collapsible'])
                    <button type="button" @click="open = !open"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-500 hover:bg-gray-800/60 hover:text-gray-300">
                        <span>{{ $section['section'] }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform"
                            :class="open ? 'rotate-90' : 'rotate-0'" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M6.293 4.293a1 1 0 011.414 0l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414-1.414L10.586 10 6.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                @else
                    <div class="px-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-500">
                        {{ $section['section'] }}
                    </div>
                @endif

                <div x-show="{{ $section['collapsible'] ? 'open' : 'true' }}" class="space-y-1">
                    @foreach ($section['items'] as $menu)
                        <a href="{{ route($menu['routeName']) }}"
                            class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm transition {{ $menu['isActive'] ? 'bg-gray-800 text-white shadow' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
                            <span
                                class="{{ $menu['isActive'] ? 'text-gray-300' : 'text-gray-400' }} group-hover:text-gray-200">
                                {!! $sidebarService->iconSvg($menu['icon']) !!}
                            </span>
                            <span class="flex-1">{{ $menu['title'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="flex items-center justify-center py-8">
                <p class="text-sm text-gray-500">{{ __('menu.no_items') }}</p>
            </div>
        @endforelse
    </nav>
</div>
