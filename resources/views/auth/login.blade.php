<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 leading-tight">Masuk ke akun Anda</h2>
        <p class="mt-1.5 text-sm text-gray-500">Selamat datang kembali 👋</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-900 text-sm placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition
                       @error('email') border-red-400 bg-red-50 @enderror"
                placeholder="nama@perusahaan.com">
            @error('email')
            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs text-blue-600 hover:text-blue-700 font-medium">Lupa password?</a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-300 bg-white text-gray-900 text-sm placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition
                       @error('password') border-red-400 bg-red-50 @enderror"
                placeholder="••••••••">
            @error('password')
            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="remember_me" class="text-sm text-gray-600 select-none cursor-pointer">Ingat saya</label>
        </div>

        <button type="submit"
            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700
                   text-white font-semibold py-2.5 rounded-xl text-sm transition-all shadow-md shadow-blue-200/60 active:scale-[.98]">
            Masuk
        </button>

        <p class="text-center text-sm text-gray-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">Daftar sekarang</a>
        </p>
    </form>
</x-guest-layout>
