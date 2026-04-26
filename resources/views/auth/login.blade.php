<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white leading-tight">Masuk ke akun Anda</h2>
        <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">Selamat datang kembali 👋</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm placeholder-gray-400 dark:placeholder-gray-500
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition
                       @error('email') border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-900/20 @enderror"
                placeholder="nama@perusahaan.com">
            @error('email')
            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Password</label>
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">Lupa password?</a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm placeholder-gray-400 dark:placeholder-gray-500
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition
                       @error('password') border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-900/20 @enderror"
                placeholder="••••••••">
            @error('password')
            <p class="mt-1.5 text-xs text-red-600 dark:text-red-400 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-blue-600 dark:text-blue-500 focus:ring-blue-500 dark:bg-gray-800">
            <label for="remember_me" class="text-sm text-gray-600 dark:text-gray-400 select-none cursor-pointer">Ingat saya</label>
        </div>

        <button type="submit"
            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700
                   text-white font-semibold py-2.5 rounded-xl text-sm transition-all shadow-md shadow-blue-200/60 dark:shadow-blue-900/40 active:scale-[.98]">
            Masuk
        </button>

        {{-- Divider --}}
        <div class="relative my-1">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200 dark:border-gray-700"></div></div>
            <div class="relative flex justify-center text-xs"><span class="bg-white dark:bg-gray-900 px-3 text-gray-400 dark:text-gray-500">atau</span></div>
        </div>

        {{-- Google Login --}}
        <a href="{{ route('auth.google') }}"
            class="w-full flex items-center justify-center gap-3 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500 transition active:scale-[.98]">
            <svg class="w-5 h-5" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            Masuk dengan Google
        </a>

        <p class="text-center text-sm text-gray-500 dark:text-gray-400">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-blue-600 dark:text-blue-400 font-semibold hover:underline">Daftar sekarang</a>
        </p>
    </form>
</x-guest-layout>
