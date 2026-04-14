<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Two-Factor Authentication') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="p-6 text-gray-900 space-y-6 sm:p-8">
                    @if (session('status'))
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (!$enabled)
                        <div class="space-y-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Enable 2FA (Google Authenticator)') }}</h3>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                {{ __('Scan this OTP Auth URL in Google Authenticator (or enter the secret manually):') }}
                            </p>
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-xs break-all text-gray-700">
                                {{ $otpAuthUrl }}
                            </div>
                            <div class="text-sm rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-2">
                                <span class="font-semibold">{{ __('Secret:') }}</span> {{ $secret }}
                            </div>
                        </div>

                        <form method="POST" action="{{ route('two-factor.enable') }}" class="space-y-3">
                            @csrf
                            <div>
                                <x-input-label for="code" :value="__('6-digit code from your authenticator app')" />
                                <x-text-input id="code" class="block mt-1 w-full" type="text" name="code"
                                    maxlength="6" required />
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>
                            <x-button type="primary" button-type="submit">{{ __('Enable 2FA') }}</x-button>
                        </form>
                    @else
                        <div class="space-y-3">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('2FA is enabled') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('Save these recovery codes in a secure place:') }}
                            </p>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                                @foreach ($recoveryCodes as $recoveryCode)
                                    <li class="rounded-lg border border-gray-200 bg-gray-50 p-2 font-mono text-xs sm:text-sm">{{ $recoveryCode }}</li>
                                @endforeach
                            </ul>
                        </div>

                        <form method="POST" action="{{ route('two-factor.disable') }}">
                            @csrf
                            @method('DELETE')
                            <x-danger-button>{{ __('Disable 2FA') }}</x-danger-button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
