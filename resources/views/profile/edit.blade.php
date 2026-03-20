<x-app-layout>
    <x-slot name="title">Profil — Qalcuity ERP</x-slot>
    <x-slot name="header">Profil Saya</x-slot>

    <div class="max-w-2xl space-y-4">

        {{-- Update Profile --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="mb-5">
                <p class="font-semibold text-gray-900 dark:text-white">Informasi Profil</p>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">Perbarui nama dan alamat email akun Anda.</p>
            </div>
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- Update Password --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="mb-5">
                <p class="font-semibold text-gray-900 dark:text-white">Ubah Password</p>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">Gunakan password yang panjang dan acak agar akun tetap aman.</p>
            </div>
            @include('profile.partials.update-password-form')
        </div>

        {{-- Delete Account --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-red-500/20 p-6">
            <div class="mb-5">
                <p class="font-semibold text-red-400">Hapus Akun</p>
                <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">Setelah dihapus, semua data akan hilang permanen.</p>
            </div>
            @include('profile.partials.delete-user-form')
        </div>

    </div>
</x-app-layout>
