<x-guest-layout>
    <x-slot name="title">Login</x-slot>
    <div class="mb-8 text-center lg:text-left">
        <h2 class="text-3xl font-bold text-navy-900">Login</h2>
        <p class="text-gray-500 mt-2">Silakan masuk ke akun Anda</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" class="text-navy-700 font-semibold" />
            <x-text-input id="email" class="block mt-1 w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@sekolah.id" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('Password')" class="text-navy-700 font-semibold" />
                @if (Route::has('password.request'))
                    <a class="text-xs text-orange-600 hover:text-orange-700 font-medium" href="{{ route('password.request') }}">
                        {{ __('Lupa password?') }}
                    </a>
                @endif
            </div>

            <x-text-input id="password" class="block mt-1 w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-orange-600 shadow-sm focus:ring-orange-500" name="remember">
            <label for="remember_me" class="ms-2 text-sm text-gray-600">{{ __('Ingat saya') }}</label>
        </div>

        <div class="pt-2">
            <button type="submit" class="w-full flex justify-center items-center px-6 py-4 bg-navy-900 border border-transparent rounded-xl font-bold text-white uppercase tracking-widest hover:bg-navy-800 focus:bg-navy-800 active:bg-navy-950 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg">
                {{ __('Masuk Sekarang') }}
            </button>
        </div>
        
        <div class="text-center mt-6">
            <a href="{{ route('welcome') }}" class="text-sm text-gray-500 hover:text-navy-900 transition-colors">
                <span class="material-icons text-xs align-middle">arrow_back</span> Kembali ke Beranda
            </a>
        </div>
    </form>
</x-guest-layout>
