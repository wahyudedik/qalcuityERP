<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Lupa password?</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Masukkan email Anda dan kami akan mengirimkan link reset.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                       @error('email') border-red-400 bg-red-50 @enderror"
                placeholder="nama@perusahaan.com">
            @error('email')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit"
            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 rounded-xl text-sm transition">
            Kirim Link Reset
        </button>
        <p class="text-center text-sm text-gray-500 dark:text-slate-400">
            <a href="{{ route('login') }}" class="text-blue-600 font-medium hover:underline">← Kembali ke login</a>
        </p>
    </form>
</x-guest-layout>
