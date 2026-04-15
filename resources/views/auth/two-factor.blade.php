<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('auth.two_factor_title') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="p-6 text-gray-900 space-y-6 sm:p-8">
                    @if (session('status'))
                        <div
                            class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (!$enabled)
                        <div class="space-y-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('auth.enable_2fa') }}</h3>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                {{ __('auth.scan_otp_url') }}
                            </p>
                            <div
                                class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-xs break-all text-gray-700">
                                {{ $otpAuthUrl }}
                            </div>
                            <div class="text-sm rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-2">
                                <span class="font-semibold">{{ __('auth.secret') }}:</span> {{ $secret }}
                            </div>
                        </div>

                        <form method="POST" action="{{ route('two-factor.enable') }}" class="space-y-3">
                            @csrf
                            <div>
                                <x-input-label for="code" :value="__('auth.authenticator_6_digit')" />
                                <x-text-input id="code" class="block mt-1 w-full" type="text" name="code"
                                    maxlength="6" required />
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>
                            <x-button type="primary" button-type="submit">{{ __('auth.enable_2fa_button') }}</x-button>
                        </form>
                    @else
                        <div class="space-y-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('auth.two_factor_enabled') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('auth.save_recovery_codes') }}
                            </p>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                @foreach ($recoveryCodes as $recoveryCode)
                                    <li
                                        class="rounded-lg border border-gray-200 bg-gray-50 p-2 font-mono text-xs sm:text-sm">
                                        {{ $recoveryCode }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf
                            @method('DELETE')
                            <x-danger-button>{{ __('auth.disable_2fa') }}</x-danger-button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
