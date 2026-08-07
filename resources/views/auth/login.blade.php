<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="!text-slate-50 !text-base tracking-wide" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" class="!text-slate-50 !text-base tracking-wide" />

            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pr-10" type="password" name="password" required
                    autocomplete="current-password" />

                <button type="button" id="toggle-password"
                    onclick="
                        const input = document.getElementById('password');
                        const isHidden = input.type === 'password';
                        input.type = isHidden ? 'text' : 'password';
                        document.getElementById('eye-icon').innerHTML = isHidden
                            ? '<path d=\'M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24\'/><line x1=\'1\' y1=\'1\' x2=\'23\' y2=\'23\'/>'
                            : '<path d=\'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z\'/><circle cx=\'12\' cy=\'12\' r=\'3\'/>';
                    "
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 hover:text-emerald-400 transition-colors focus:outline-none"
                    aria-label="Toggle password visibility">
                    <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'/>
                        <circle cx='12' cy='12' r='3'/>
                    </svg>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded bg-slate-700 border-slate-600 text-emerald-500 shadow-sm focus:ring-emerald-500 focus:ring-offset-slate-800"
                    name="remember">
                <span class="ms-2 text-sm text-slate-300">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            @if (Route::has('register'))
                <a class="underline text-sm text-slate-200 hover:text-white drop-shadow-sm transition-colors rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 focus:ring-offset-slate-800"
                    href="{{ route('register') }}">
                    {{ __("Don't have an account?") }}
                </a>
            @endif

            <div class="flex items-center">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-slate-200 hover:text-white drop-shadow-sm transition-colors rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 focus:ring-offset-slate-800"
                        href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-primary-button class="ms-3">
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </div>
    </form>
</x-guest-layout>