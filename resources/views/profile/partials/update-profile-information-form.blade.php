<form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

<form method="post" action="{{ route('profile.update') }}" class="space-y-4">
    @csrf @method('patch')

    <div>
        <label for="name" class="block text-sm font-medium text-gray-500 dark:text-slate-400 mb-1.5">Nama</label>
        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                   @error('name') border-red-500/50 @enderror">
        @error('name')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-500 dark:text-slate-400 mb-1.5">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition
                   @error('email') border-red-500/50 @enderror">
        @error('email')<p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="mt-2 flex items-center gap-2">
            <p class="text-xs text-amber-400">Email belum diverifikasi.</p>
            <button form="send-verification" class="text-xs text-blue-400 hover:underline">Kirim ulang verifikasi</button>
        </div>
        @if (session('status') === 'verification-link-sent')
        <p class="mt-1 text-xs text-green-400">Link verifikasi baru telah dikirim.</p>
        @endif
        @endif
    </div>

    <div class="flex items-center gap-3 pt-1">
        <button type="submit"
            class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition">
            Simpan
        </button>
        @if (session('status') === 'profile-updated')
        <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
           class="text-sm text-green-400">Tersimpan.</p>
        @endif
    </div>
</form>

