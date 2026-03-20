<x-app-layout>
    <x-slot name="title">{{ $tenant->name }} — Qalcuity ERP</x-slot>
    <x-slot name="header">Detail Tenant</x-slot>
    <x-slot name="topbarActions">
        <a href="{{ route('super-admin.tenants.index') }}"
           class="flex items-center gap-2 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-900 dark:text-white px-3 py-2 rounded-xl hover:bg-[#f8f8f8] dark:bg-white/10 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-500/20 border border-green-500/30 text-green-400 text-sm rounded-xl">{{ session('success') }}</div>
    @endif

    <div class="max-w-3xl space-y-4">

        {{-- Tenant Info --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $tenant->name }}</h2>
                    <p class="text-sm text-gray-400 dark:text-slate-500">{{ $tenant->slug }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $planColors = [
                            'trial'      => 'bg-amber-500/20 text-amber-400',
                            'basic'      => 'bg-blue-500/20 text-blue-400',
                            'pro'        => 'bg-purple-500/20 text-purple-400',
                            'enterprise' => 'bg-indigo-500/20 text-indigo-400',
                        ];
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium {{ $planColors[$tenant->plan] ?? 'bg-[#f8f8f8] dark:bg-white/10 text-gray-500 dark:text-slate-400' }}">
                        {{ ucfirst($tenant->plan) }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium
                        {{ $tenant->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $tenant->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        {{ $tenant->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    @if($tenant->isTrialExpired())
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-red-500/20 text-red-400">
                            Trial Expired
                        </span>
                    @elseif($tenant->isPlanExpired())
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-red-500/20 text-red-400">
                            Plan Expired
                        </span>
                    @endif
                </div>
            </div>

            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-0.5">Email</dt>
                    <dd class="text-gray-900 dark:text-white">{{ $tenant->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-0.5">Telepon</dt>
                    <dd class="text-gray-900 dark:text-white">{{ $tenant->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-0.5">Trial Berakhir</dt>
                    <dd class="{{ $tenant->isTrialExpired() ? 'text-red-400 font-medium' : 'text-gray-900 dark:text-white' }}">
                        {{ $tenant->trial_ends_at?->format('d M Y') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-0.5">Langganan Berakhir</dt>
                    <dd class="{{ $tenant->isPlanExpired() ? 'text-red-400 font-medium' : 'text-gray-900 dark:text-white' }}">
                        {{ $tenant->plan_expires_at?->format('d M Y') ?? '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-0.5">Paket Aktif</dt>
                    <dd class="text-gray-900 dark:text-white">{{ $tenant->subscriptionPlan?->name ?? ucfirst($tenant->plan) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-0.5">Terdaftar</dt>
                    <dd class="text-gray-900 dark:text-white">{{ $tenant->created_at->format('d M Y, H:i') }}</dd>
                </div>
            </dl>

            <div class="flex items-center gap-2 mt-5 pt-5 border-t border-gray-200 dark:border-white/10">
                <form method="POST" action="{{ route('super-admin.tenants.toggle', $tenant) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                        class="text-sm px-4 py-2 rounded-xl border transition font-medium
                            {{ $tenant->is_active
                                ? 'border-red-500/30 text-red-400 hover:bg-red-500/10'
                                : 'border-green-500/30 text-green-400 hover:bg-green-500/10' }}">
                        {{ $tenant->is_active ? 'Nonaktifkan Tenant' : 'Aktifkan Tenant' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('super-admin.tenants.destroy', $tenant) }}"
                      onsubmit="return confirm('Hapus tenant {{ $tenant->name }} beserta semua datanya?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="text-sm px-4 py-2 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500/10 transition font-medium">
                        Hapus Tenant
                    </button>
                </form>
            </div>
        </div>

        {{-- Update Plan --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <p class="font-semibold text-gray-900 dark:text-white">Atur Paket Langganan</p>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Ubah paket, pilih definisi paket, dan atur tanggal kedaluwarsa</p>
            </div>
            <form method="POST" action="{{ route('super-admin.tenants.update-plan', $tenant) }}" class="p-6 space-y-4">
                @csrf @method('PATCH')

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Tipe Plan</label>
                        <select name="plan" required
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            @foreach(['trial', 'basic', 'pro', 'enterprise'] as $p)
                                <option value="{{ $p }}" {{ $tenant->plan === $p ? 'selected' : '' }}>
                                    {{ ucfirst($p) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Definisi Paket</label>
                        <select name="subscription_plan_id"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="">— Tidak terikat definisi —</option>
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}" {{ $tenant->subscription_plan_id == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }} — Rp {{ number_format($p->price_monthly, 0, ',', '.') }}/bln
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Trial Berakhir</label>
                        <input type="date" name="trial_ends_at"
                            value="{{ old('trial_ends_at', $tenant->trial_ends_at?->format('Y-m-d')) }}"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Hanya berlaku jika plan = Trial</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Langganan Berakhir</label>
                        <input type="date" name="plan_expires_at"
                            value="{{ old('plan_expires_at', $tenant->plan_expires_at?->format('Y-m-d')) }}"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Berlaku untuk plan berbayar</p>
                    </div>
                </div>

                {{-- Quick set buttons --}}
                <div class="flex flex-wrap gap-2 pt-1">
                    <p class="text-xs text-gray-400 dark:text-slate-500 w-full">Perpanjang cepat (dari hari ini):</p>
                    @foreach([
                        ['label' => '+1 Bulan', 'days' => 30],
                        ['label' => '+3 Bulan', 'days' => 90],
                        ['label' => '+6 Bulan', 'days' => 180],
                        ['label' => '+1 Tahun', 'days' => 365],
                    ] as $opt)
                    <button type="button"
                        onclick="setExpiry({{ $opt['days'] }})"
                        class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:border-white/20 transition">
                        {{ $opt['label'] }}
                    </button>
                    @endforeach
                </div>

                <div class="flex justify-end pt-2 border-t border-gray-200 dark:border-white/10">
                    <button type="submit"
                        class="text-sm bg-blue-600 hover:bg-blue-700 text-gray-900 dark:text-white font-semibold px-5 py-2.5 rounded-xl transition">
                        Simpan Paket
                    </button>
                </div>
            </form>
        </div>

        {{-- Users --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <p class="font-semibold text-gray-900 dark:text-white">Pengguna</p>
                <span class="text-xs bg-[#f8f8f8] dark:bg-white/10 text-gray-500 dark:text-slate-400 font-medium px-2.5 py-1 rounded-lg">{{ $tenant->users->count() }}</span>
            </div>
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach($tenant->users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-gray-900 dark:text-white text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-sm text-gray-500 dark:text-slate-400">{{ $user->email }}</td>
                        <td class="px-6 py-3.5">
                            <span class="text-xs font-medium text-gray-500 dark:text-slate-400 capitalize">{{ $user->role }}</span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $user->is_active ? 'text-green-400' : 'text-red-400' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function setExpiry(days) {
        const d = new Date();
        d.setDate(d.getDate() + days);
        const val = d.toISOString().split('T')[0];
        document.querySelector('input[name="plan_expires_at"]').value = val;
    }
    </script>
</x-app-layout>
