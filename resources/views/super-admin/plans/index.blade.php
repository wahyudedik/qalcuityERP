<x-app-layout>
    <x-slot name="title">Kelola Paket — Qalcuity ERP</x-slot>
    <x-slot name="header">Kelola Paket Langganan</x-slot>
    <x-slot name="pageHeader">
        <form method="POST" action="{{ route('super-admin.plans.seed') }}" class="inline">
            @csrf
            <button type="submit"
                class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 px-3 py-2 rounded-xl hover:bg-gray-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Sync Default
            </button>
        </form>
        <a href="{{ route('super-admin.plans.create') }}"
           class="flex items-center gap-2 text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah
        </a>
    </x-slot>

    {{-- Mobile action buttons --}}
    <div class="flex sm:hidden gap-2 mb-4">
        <form method="POST" action="{{ route('super-admin.plans.seed') }}" class="inline">
            @csrf
            <button type="submit" class="text-xs px-3 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Sync Default</button>
        </form>
        <a href="{{ route('super-admin.plans.create') }}" class="text-xs px-3 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tambah Paket</a>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        @forelse($plans as $plan)
        <div class="bg-white rounded-2xl border border-gray-200 p-6 flex flex-col {{ !$plan->is_active ? 'opacity-60' : '' }}">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">{{ $plan->name }}</h3>
                    <p class="text-xs text-gray-400 font-mono">{{ $plan->slug }}</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium
                    {{ $plan->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $plan->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                    {{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            <div class="mb-4">
                <p class="text-2xl font-bold text-gray-900">
                    Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}
                    <span class="text-sm font-normal text-gray-400">/bln</span>
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                    Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}/tahun
                </p>
            </div>

            <div class="space-y-2 text-sm text-gray-600 mb-5 flex-1">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>{{ $plan->max_users === -1 ? 'User tak terbatas' : 'Maks. ' . $plan->max_users . ' user' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    <span>{{ $plan->max_ai_messages === -1 ? 'AI tak terbatas' : 'Maks. ' . $plan->max_ai_messages . ' pesan AI/bln' }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Trial {{ $plan->trial_days }} hari</span>
                </div>
                @if($plan->features)
                    @foreach(array_slice($plan->features, 0, 5) as $feature)
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-xs">{{ $feature }}</span>
                    </div>
                    @endforeach
                    @if(count($plan->features) > 5)
                    <p class="text-xs text-gray-400 pl-6">+{{ count($plan->features) - 5 }} fitur lainnya</p>
                    @endif
                @endif
            </div>

            <div class="flex items-center gap-2 pt-4 border-t border-gray-200">
                <span class="text-xs text-gray-400">{{ $plan->tenants_count ?? $plan->tenants()->count() }} tenant</span>
                <div class="flex-1"></div>
                <form method="POST" action="{{ route('super-admin.plans.toggle', $plan) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                        class="text-xs font-medium px-3 py-1.5 rounded-lg transition
                        {{ $plan->is_active
                            ? 'text-amber-600 hover:bg-amber-50'
                            : 'text-green-600 hover:bg-green-50' }}">
                        {{ $plan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </form>
                <a href="{{ route('super-admin.plans.edit', $plan) }}"
                   class="text-xs text-blue-600 hover:text-blue-700 font-medium px-3 py-1.5 rounded-lg hover:bg-blue-50 transition">
                    Edit
                </a>
                <form method="POST" action="{{ route('super-admin.plans.destroy', $plan) }}"
                      onsubmit="return confirm('Hapus paket {{ $plan->name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="text-xs text-red-600 hover:text-red-700 font-medium px-3 py-1.5 rounded-lg hover:bg-red-50 transition">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-2xl border border-gray-200 p-12 text-center">
            <p class="text-gray-400 text-sm mb-4">Belum ada paket langganan.</p>
            <form method="POST" action="{{ route('super-admin.plans.seed') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition">
                    Buat Paket Default
                </button>
            </form>
        </div>
        @endforelse
    </div>
</x-app-layout>
