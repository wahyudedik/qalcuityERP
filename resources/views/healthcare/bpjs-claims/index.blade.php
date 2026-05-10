<x-app-layout>
    <x-slot name="header">Claim BPJS</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Claim BPJS'],
    ]" />

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Stats Cards - Using Component (data from controller, no queries in Blade) --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <x-healthcare.stats-card label="Total Claim" :value="$statistics['total']" color="blue" icon="clipboard" />
                <x-healthcare.stats-card label="Pending" :value="$statistics['pending']" color="amber" icon="clock" />
                <x-healthcare.stats-card label="Disetujui" :value="$statistics['approved']" color="green" icon="check" />
                <x-healthcare.stats-card label="Ditolak" :value="$statistics['rejected']" color="red" icon="x" />
            </div>

            <div class="flex justify-end mb-4">
                <a href="{{ route('healthcare.bpjs-claims.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Claim Baru
                </a>
            </div>

            {{-- Claims Table - Desktop & Mobile --}}
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">No. Claim</th>
                                <th class="px-4 py-3 text-left">Pasien</th>
                                <th class="px-4 py-3 text-left hidden lg:table-cell">No. BPJS</th>
                                <th class="px-4 py-3 text-right">Jumlah Claim</th>
                                <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($claims as $claim)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span
                                            class="font-mono text-sm font-bold text-blue-600">{{ $claim->claim_number }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900">
                                            {{ $claim->patient?->name ?? 'N/A' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">
                                        {{ $claim->bpjs_number }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp
                                        {{ number_format($claim->claim_amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 hidden sm:table-cell">
                                        {{ $claim->submission_date ? $claim->submission_date->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg {{ $claim->status === 'approved' ? 'bg-green-100 text-green-700' : ($claim->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                            {{ ucfirst($claim->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('healthcare.bpjs-claims.show', $claim) }}"
                                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Detail">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                            </a>
                                            <a href="{{ route('healthcare.bpjs-claims.edit', $claim) }}"
                                                class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </a>
                                            <form action="{{ route('healthcare.bpjs-claims.destroy', $claim) }}"
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Yakin ingin menghapus?')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                    class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg"
                                                    title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data claim BPJS</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card View --}}
                <div class="md:hidden divide-y divide-gray-100">
                    @forelse ($claims as $claim)
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-mono text-sm font-bold text-blue-600">
                                        {{ $claim->claim_number }}</p>
                                    <p class="font-semibold text-gray-900 truncate mt-0.5">
                                        {{ $claim->patient?->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">BPJS:
                                        {{ $claim->bpjs_number }}</p>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg {{ $claim->status === 'approved' ? 'bg-green-100 text-green-700' : ($claim->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                        {{ ucfirst($claim->status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                                <div>
                                    <p class="text-gray-500">Jumlah Claim</p>
                                    <p class="font-bold text-gray-900">Rp
                                        {{ number_format($claim->claim_amount, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-gray-500">Tanggal</p>
                                    <p class="font-medium text-gray-900">
                                        {{ $claim->submission_date ? $claim->submission_date->format('d M Y') : '-' }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                                <a href="{{ route('healthcare.bpjs-claims.show', $claim) }}"
                                    class="flex-1 px-3 py-2 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg text-center hover:bg-blue-100">Detail</a>
                                <a href="{{ route('healthcare.bpjs-claims.edit', $claim) }}"
                                    class="flex-1 px-3 py-2 text-xs font-medium text-amber-600 bg-amber-50 rounded-lg text-center hover:bg-amber-100">Edit</a>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <p>Tidak ada data claim BPJS</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if ($claims->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $claims->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
