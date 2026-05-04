<x-app-layout>
    <x-slot name="header">Klaim Asuransi</x-slot>

    @php $tid = auth()->user()->tenant_id; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-4 mb-6">
        @php
            $totalClaims = \App\Models\InsuranceClaim::where('tenant_id', $tid)->count();
            $pendingClaims = \App\Models\InsuranceClaim::where('tenant_id', $tid)->where('status', 'pending')->count();
            $submittedClaims = \App\Models\InsuranceClaim::where('tenant_id', $tid)
                ->where('status', 'submitted')
                ->count();
            $approvedClaims = \App\Models\InsuranceClaim::where('tenant_id', $tid)
                ->where('status', 'approved')
                ->count();
            $rejectedClaims = \App\Models\InsuranceClaim::where('tenant_id', $tid)
                ->where('status', 'rejected')
                ->count();
        @endphp
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Klaim</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalClaims) }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ $pendingClaims }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Submitted</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $submittedClaims }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Disetujui</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $approvedClaims }}</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Ditolak</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ $rejectedClaims }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari pasien / No. klaim..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="submitted" @selected(request('status') === 'submitted')>Submitted</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                    <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                </select>
                <select name="insurance"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Asuransi</option>
                    <option value="bpjs" @selected(request('insurance') === 'bpjs')>BPJS</option>
                    <option value="manulife" @selected(request('insurance') === 'manulife')>Manulife</option>
                    <option value="prudential" @selected(request('insurance') === 'prudential')>Prudential</option>
                    <option value="allianz" @selected(request('insurance') === 'allianz')>Allianz</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            </form>
        </div>
    </div>

    {{-- Claims Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Klaim</th>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Asuransi</th>
                        <th class="px-4 py-3 text-right hidden sm:table-cell">Jumlah Klaim</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Tanggal Submit</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($claims ?? [] as $claim)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span
                                    class="font-mono text-sm font-bold text-blue-600">{{ $claim->claim_number ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">
                                    {{ $claim->patient ? $claim->patient?->full_name : '-' }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $claim->patient ? $claim->patient?->medical_record_number : '-' }}</p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900">{{ $claim->insurance_provider ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $claim->policy_number ?? '-' }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-right hidden sm:table-cell">
                                <span class="font-semibold text-gray-900">Rp
                                    {{ number_format($claim->claim_amount, 0, ',', '.') }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">
                                {{ $claim->submitted_at ? \Carbon\Carbon::parse($claim->submitted_at)->format('d M Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($claim->status === 'pending')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Pending</span>
                                @elseif($claim->status === 'submitted')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">Submitted</span>
                                @elseif($claim->status === 'approved')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Approved</span>
                                @elseif($claim->status === 'rejected')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Rejected</span>
                                @elseif($claim->status === 'paid')
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700">Paid</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('healthcare.billing.insurance.claims.show', $claim) }}"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                        title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                    @if ($claim->status === 'pending')
                                        <button onclick="submitClaim({{ $claim->id }})"
                                            class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg"
                                            title="Submit">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                <p>Belum ada klaim asuransi</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (isset($claims) && $claims->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $claims->links() }}
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function submitClaim(id) {
                if (confirm('Submit klaim ini ke asuransi?')) {
                    fetch(`/healthcare/billing/insurance/claims/${id}/submit`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(() => location.reload());
                }
            }
        </script>
    @endpush
</x-app-layout>
