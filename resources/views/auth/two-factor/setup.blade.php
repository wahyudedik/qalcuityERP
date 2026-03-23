<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Aktifkan Two-Factor Authentication</h2>
        <p class="mt-1 text-sm text-gray-500">Scan QR code dengan aplikasi authenticator (Google Authenticator, Authy, dll).</p>
    </div>

    @if(session('warning'))
        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl text-sm">
            ⚠️ {{ session('warning') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="space-y-6">
        <!-- QR Code -->
        <div class="flex flex-col items-center gap-3 p-5 bg-gray-50 rounded-xl border border-gray-200">
            @if($qrSvg)
                <div class="bg-white p-3 rounded-lg shadow-sm">{!! $qrSvg !!}</div>
            @else
                <p class="text-xs text-gray-500 break-all text-center">
                    Buka aplikasi authenticator dan masukkan kode manual:<br>
                    <span class="font-mono font-bold text-gray-800">{{ $secret }}</span>
                </p>
            @endif
            <p class="text-xs text-gray-500">Atau masukkan kode manual:</p>
            <code class="text-sm font-mono bg-white px-3 py-1.5 rounded-lg border border-gray-200 tracking-widest select-all">
                {{ $secret }}
            </code>
        </div>

        <!-- Konfirmasi OTP -->
        <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Masukkan kode 6 digit dari aplikasi authenticator
                </label>
                <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                       autofocus autocomplete="one-time-code"
                       class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-center text-2xl font-mono tracking-widest
                              focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="000000">
            </div>
            <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700
                           text-white font-semibold py-2.5 rounded-xl text-sm transition-all shadow-md">
                Aktifkan 2FA
            </button>
        </form>

        @auth
            <a href="{{ route('dashboard') }}" class="block text-center text-sm text-gray-500 hover:text-gray-700">
                Lewati untuk sekarang
            </a>
        @endauth
    </div>
</x-guest-layout>
