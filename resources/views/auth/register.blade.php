<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Buat akun perusahaan</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">Coba gratis 14 hari, tanpa kartu kredit</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="company_name" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Nama Perusahaan</label>
            <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" required autofocus
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                       @error('company_name') border-red-400 bg-red-50 @enderror"
                placeholder="PT. Contoh Jaya">
            @error('company_name')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="business_type" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Jenis Bisnis</label>
            <select id="business_type" name="business_type"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                <option value="">-- Pilih jenis bisnis (opsional) --</option>
                <option value="warung_makan"  {{ old('business_type') === 'warung_makan'  ? 'selected' : '' }}>Warung Makan / Rumah Makan</option>
                <option value="kafe"          {{ old('business_type') === 'kafe'          ? 'selected' : '' }}>Kafe / Coffee Shop</option>
                <option value="toko_retail"   {{ old('business_type') === 'toko_retail'   ? 'selected' : '' }}>Toko Retail / Minimarket</option>
                <option value="konveksi"      {{ old('business_type') === 'konveksi'      ? 'selected' : '' }}>Konveksi / Garmen</option>
                <option value="distributor"   {{ old('business_type') === 'distributor'   ? 'selected' : '' }}>Distributor / Grosir</option>
                <option value="jasa"          {{ old('business_type') === 'jasa'          ? 'selected' : '' }}>Usaha Jasa</option>
                <option value="lainnya"       {{ old('business_type') === 'lainnya'       ? 'selected' : '' }}>Lainnya</option>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Nama Admin</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                           @error('name') border-red-400 bg-red-50 @enderror"
                    placeholder="Nama lengkap">
                @error('name')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Telepon <span class="text-gray-400">(opsional)</span></label>
                <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    placeholder="+62...">
            </div>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                       @error('email') border-red-400 bg-red-50 @enderror"
                placeholder="admin@perusahaan.com">
            @error('email')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                           @error('password') border-red-400 bg-red-50 @enderror"
                    placeholder="••••••••">
                @error('password')
                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1.5">Konfirmasi</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    placeholder="••••••••">
            </div>
        </div>

        <button type="submit"
            class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-2.5 rounded-xl text-sm transition shadow-sm shadow-blue-200 mt-2">
            Buat Akun Gratis
        </button>

        <p class="text-center text-sm text-gray-500 dark:text-slate-400">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-blue-600 font-medium hover:underline">Masuk</a>
        </p>
    </form>
</x-guest-layout>
