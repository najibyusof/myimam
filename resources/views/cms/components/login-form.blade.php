@php
    $label = $props['label'] ?? 'AKSES DEMO';
    $leftTitle = $props['left_title'] ?? 'Sistem Pengurusan Kewangan Masjid';
    $leftSubtitle = $props['left_subtitle'] ?? 'Pengurusan kewangan yang telus, cekap, dan moden.';
    $feature1Title = $props['feature_1_title'] ?? 'Transparent Operations';
    $feature1Text = $props['feature_1_text'] ?? 'Audit-ready records for income, expenses and approvals.';
    $feature2Title = $props['feature_2_title'] ?? 'Role-based Access';
    $feature2Text = $props['feature_2_text'] ?? 'Secure module access for Admin, Bendahari, AJK and Auditor.';
    $formTitle = $props['title'] ?? __('auth.login_heading');
    $formSubtitle = $props['subtitle'] ?? __('auth.login_subheading');
    $showDemoAccountsValue = $props['show_demo_accounts'] ?? true;
    $showDemoAccountsParsed = filter_var($showDemoAccountsValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $showDemoAccounts =
        $showDemoAccountsParsed ??
        !in_array(strtolower(trim((string) $showDemoAccountsValue)), ['0', 'false', 'no', 'off', 'hidden'], true);
    $demoAccounts = $context['demoAccounts'] ?? [];
    $demoCopyPayload = $context['demoCopyPayload'] ?? '';
@endphp

{{-- Break out of the CMS page container so this fills the full viewport --}}
<div class="-mx-3 -my-5 sm:-mx-6 sm:-my-6 lg:-mx-8 lg:-my-10 min-h-screen bg-gray-100">
    <div class="mx-auto grid min-h-screen w-full max-w-7xl lg:grid-cols-2">

        {{-- Left panel: dark gradient with hero content + feature cards --}}
        <aside
            class="hidden lg:flex lg:flex-col lg:justify-between bg-gradient-to-br from-slate-900 via-indigo-900 to-slate-800 px-12 py-10 text-white">
            <div>
                <p class="text-xs uppercase tracking-[0.2em] text-indigo-200">{{ $label }}</p>
                <h1 class="mt-3 text-4xl font-extrabold leading-tight">{{ $leftTitle }}</h1>
                <p class="mt-4 max-w-md text-indigo-100">{{ $leftSubtitle }}</p>
            </div>

            <div class="space-y-3">
                @if (!empty($feature1Title))
                    <div class="rounded-xl bg-white/10 p-4 backdrop-blur">
                        <p class="text-sm font-semibold">{{ $feature1Title }}</p>
                        <p class="mt-1 text-xs text-indigo-100">{{ $feature1Text }}</p>
                    </div>
                @endif
                @if (!empty($feature2Title))
                    <div class="rounded-xl bg-white/10 p-4 backdrop-blur">
                        <p class="text-sm font-semibold">{{ $feature2Title }}</p>
                        <p class="mt-1 text-xs text-indigo-100">{{ $feature2Text }}</p>
                    </div>
                @endif
            </div>
        </aside>

        {{-- Right panel: login form --}}
        <section class="flex items-center justify-center px-4 py-10 sm:px-6 lg:px-10">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-lg sm:p-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $formTitle }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ $formSubtitle }}</p>
                </div>

                <x-auth-session-status class="mt-4 mb-2" :status="session('status')" />

                @if ($showDemoAccounts)
                    <div class="mt-5 mb-4 rounded-xl border border-sky-200 bg-sky-50 p-4" x-data="{ open: false }">
                        <div class="flex items-center justify-between gap-3">
                            <button type="button" @click="open = !open"
                                class="inline-flex items-center gap-2 text-sm font-semibold text-sky-800">
                                <span>{{ __('auth.demo_accounts') }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform"
                                    :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <button type="button"
                                class="rounded-md border border-sky-300 px-2.5 py-1 text-xs font-semibold text-sky-700 transition hover:bg-sky-100"
                                @click="navigator.clipboard.writeText(@js($demoCopyPayload))">
                                {{ __('form.copy_all') }}
                            </button>
                        </div>
                        <div x-show="open" x-cloak class="mt-3 space-y-2 text-xs text-sky-900">
                            @forelse ($demoAccounts as $account)
                                <p>
                                    <span class="font-semibold">{{ $account['label'] ?? '' }}:</span>
                                    {{ $account['email'] ?? '' }} /
                                    {{ $account['password_hint'] ?? __('auth.password') }}
                                </p>
                            @empty
                                <p class="text-sky-700">Tiada akaun demo tersedia.</p>
                            @endforelse
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4" x-data="{ showPassword: false }">
                    @csrf

                    <x-input label="{{ __('auth.email') }}" name="email" type="email" :value="old('email')"
                        placeholder="{{ __('form.placeholder_email') }}" required autofocus autocomplete="username" />

                    <div>
                        <label for="password"
                            class="mb-1 block text-sm font-medium text-gray-700">{{ __('auth.password') }}</label>
                        <div class="relative">
                            <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                                autocomplete="current-password"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 pr-20 text-sm text-gray-900 shadow-sm transition placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-2 my-1 rounded-md px-2 text-xs font-semibold text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                                x-text="showPassword ? @js(__('form.hide')) : @js(__('form.show'))"></button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-gray-600">
                            <input id="remember_me" type="checkbox" name="remember"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <span>{{ __('auth.remember_me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                                {{ __('auth.forgot_password') }}
                            </a>
                        @endif
                    </div>

                    <x-button type="primary" size="md" class="w-full" button-type="submit">
                        {{ __('auth.log_in') }}
                    </x-button>
                </form>

                <p class="mt-5 text-center text-sm text-gray-400">
                    <a href="{{ route('landing') }}" class="hover:text-gray-600">{{ __('form.back_to_landing') }}</a>
                </p>
            </div>
        </section>
    </div>
</div>
