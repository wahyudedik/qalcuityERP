<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Langganan Berakhir — Qalcuity ERP</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 dark:bg-[#0f172a] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 shadow-xl p-10">
            @php $status = request('status', 'expired'); @endphp

            <div class="w-16 h-16 rounded-2xl mx-auto mb-6 flex items-center justify-center
                {{ in_array($status, ['trial_expired', 'expired']) ? 'bg-amber-500/20' : 'bg-red-500/20' }}">
                @if($status === 'nonaktif')
                    <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                @else
                    <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @endif
            </div>

            <h1 class="text-xl font-bold text-white mb-2">
                @if($status === 'nonaktif')
                    Akun Dinonaktifkan
                @elseif($status === 'trial_expired')
                    Masa Trial Berakhir
                @else
                    Langganan Berakhir
                @endif
            </h1>

            <p class="text-gray-500 dark:text-slate-400 text-sm mb-8">
                @if($status === 'nonaktif')
                    Akun perusahaan Anda telah dinonaktifkan oleh administrator. Hubungi tim support untuk informasi lebih lanjut.
                @elseif($status === 'trial_expired')
                    Masa trial gratis Anda telah berakhir. Pilih paket berlangganan untuk melanjutkan menggunakan Qalcuity ERP.
                @else
                    Masa berlangganan Anda telah berakhir. Perpanjang sekarang untuk melanjutkan akses ke semua fitur.
                @endif
            </p>

            <div class="space-y-3">
                @if($status !== 'nonaktif')
                <a href="mailto:support@qalcuity.com?subject=Perpanjang Langganan"
                   class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold py-3 px-4 rounded-xl transition">
                    Hubungi untuk Perpanjang
                </a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-sm text-gray-400 dark:text-slate-500 hover:text-gray-600 dark:hover:text-slate-300 py-2 transition">
                        Keluar dari akun
                    </button>
                </form>
            </div>
        </div>

        <p class="mt-6 text-xs text-slate-600">
            Qalcuity ERP &mdash; <a href="mailto:support@qalcuity.com" class="hover:text-gray-500 dark:text-slate-400 transition">support@qalcuity.com</a>
        </p>
    </div>
</body>
</html>

