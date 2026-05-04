<x-app-layout>
    <x-slot name="header">Affiliate Dashboard</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Referral</p>
            <p class="text-2xl font-bold text-blue-500">{{ $referrals->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Earned</p>
            <p class="text-lg font-bold text-green-500">Rp {{ number_format($affiliate->total_earned, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Saldo Tersedia</p>
            <p class="text-lg font-bold text-amber-500">Rp {{ number_format(max(0, $affiliate->balance - $pendingWithdraw), 0, ',', '.') }}</p>
            @if($pendingWithdraw > 0)<p class="text-xs text-red-400">Pending: Rp {{ number_format($pendingWithdraw, 0, ',', '.') }}</p>@endif
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Dicairkan</p>
            <p class="text-lg font-bold text-gray-900">Rp {{ number_format($affiliate->total_paid, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Komisi Rate</p>
            <p class="text-2xl font-bold text-gray-900">{{ $affiliate->commission_rate }}%</p>
        </div>
    </div>

    {{-- Demo Account Info --}}
    @if($affiliate->demoTenant)
    <div class="bg-purple-50 border border-purple-200 rounded-2xl p-5 mb-6">
        <h3 class="font-semibold text-purple-700 mb-2">🎮 Akun Demo ERP Anda</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
            <div><span class="text-purple-600">Tenant:</span> <span class="text-gray-900 font-medium">{{ $affiliate->demoTenant?->name }}</span></div>
            <div><span class="text-purple-600">Email:</span> <span class="text-gray-900 font-mono text-xs">demo-{{ $affiliate->demoTenant?->slug }}@qalcuity.com</span></div>
            <div><span class="text-purple-600">Password:</span> <span class="text-gray-900 font-mono text-xs">demo123456</span></div>
        </div>
        <p class="text-xs text-purple-500 mt-2">Gunakan akun demo ini untuk menunjukkan fitur ERP ke calon referral Anda. Plan: Professional (tidak expired).</p>
    </div>
    @endif

    {{-- Referral Link --}}
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 mb-6">
        <h3 class="font-semibold text-blue-700 mb-2">🔗 Link Referral Anda</h3>
        <div class="flex items-center gap-2">
            <input type="text" id="ref-link" readonly value="{{ $affiliate->referralUrl() }}"
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-blue-200 bg-white text-gray-900 font-mono">
            <button onclick="copyRefLink()" id="btn-copy-ref"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Copy</button>
        </div>
        <p class="text-xs text-blue-600 mt-2">Kode: <span class="font-mono font-bold">{{ $affiliate->code }}</span> · Bagikan link ini ke calon pengguna Qalcuity ERP</p>
    </div>

    @push('scripts')
    <script>
    function copyRefLink() {
        const input = document.getElementById('ref-link');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(input.value).then(() => {
                document.getElementById('btn-copy-ref').textContent = '✓ Copied';
                setTimeout(() => document.getElementById('btn-copy-ref').textContent = 'Copy', 2000);
            });
        } else {
            input.select();
            input.setSelectionRange(0, 99999);
            document.execCommand('copy');
            document.getElementById('btn-copy-ref').textContent = '✓ Copied';
            setTimeout(() => document.getElementById('btn-copy-ref').textContent = 'Copy', 2000);
        }
    }
    </script>
    @endpush

    {{-- Withdraw Request --}}
    @php $available = max(0, $affiliate->balance - $pendingWithdraw); @endphp
    @if($available >= 50000)
    <div class="bg-green-50 border border-green-200 rounded-2xl p-5 mb-6">
        <h3 class="font-semibold text-green-700 mb-3">💸 Tarik Saldo</h3>
        @if(!$affiliate->bank_name || !$affiliate->bank_account)
        <p class="text-sm text-red-500">⚠ Lengkapi data rekening bank di bawah sebelum withdraw.</p>
        @else
        <form method="POST" action="{{ route('affiliate.withdraw') }}" class="flex flex-wrap items-end gap-3" onsubmit="return confirm('Ajukan withdraw Rp ' + document.getElementById('wd-amount').value + '?')">
            @csrf
            <div>
                <label class="block text-xs text-green-600 mb-1">Jumlah (min Rp 50.000)</label>
                <input type="number" name="amount" id="wd-amount" required min="50000" max="{{ $available }}" step="1000" value="{{ $available }}"
                    class="px-3 py-2 text-sm rounded-xl border border-green-200 bg-white text-gray-900 w-48">
            </div>
            <div class="text-xs text-green-600">
                Ke: {{ $affiliate->bank_name }} {{ $affiliate->bank_account }} a/n {{ $affiliate->bank_holder }}
            </div>
            <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Ajukan Withdraw</button>
        </form>
        @endif
    </div>
    @endif

    {{-- Pending Withdrawals --}}
    @if($payouts->where('status', 'pending')->isNotEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6">
        <p class="text-sm text-amber-700">⏳ Anda memiliki pengajuan withdraw yang sedang diproses:</p>
        @foreach($payouts->where('status', 'pending') as $pw)
        <p class="text-sm font-medium text-amber-800 mt-1">Rp {{ number_format($pw->amount, 0, ',', '.') }} — diajukan {{ $pw->requested_at?->format('d/m/Y H:i') ?? $pw->created_at->format('d/m/Y H:i') }}</p>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Referrals --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Referral Saya</h3>
            </div>
            @if($referrals->isNotEmpty())
            <div class="divide-y divide-gray-100">
                @foreach($referrals ?? [] as $ref)
                <div class="px-5 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-900">{{ $ref->tenant?->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $ref->referred_at->format('d/m/Y') }} · {{ $ref->tenant?->plan ?? 'trial' }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ ($ref->tenant?->is_active ?? false) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ ($ref->tenant?->is_active ?? false) ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                @endforeach
            </div>
            @else
            <div class="px-5 py-8 text-center text-gray-400 text-sm">Belum ada referral. Bagikan link Anda!</div>
            @endif
        </div>

        {{-- Monthly Earnings Chart --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-4">Pendapatan Bulanan</h3>
            @if($monthlyEarnings->isNotEmpty())
            <div class="space-y-2">
                @foreach($monthlyEarnings ?? [] as $me)
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500 w-16 shrink-0">{{ $me->month }}</span>
                    <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden">
                        @php $maxEarning = $monthlyEarnings->max('total') ?: 1; $pct = round($me->total / $maxEarning * 100); @endphp
                        <div class="h-full bg-green-500 rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="text-xs text-gray-900 font-medium w-28 text-right shrink-0">Rp {{ number_format($me->total, 0, ',', '.') }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-center text-gray-400 text-sm py-4">Belum ada data.</p>
            @endif
        </div>
    </div>

    {{-- Commission History --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Riwayat Komisi</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr><th class="px-4 py-3 text-left">Tenant</th><th class="px-4 py-3 text-left">Plan</th><th class="px-4 py-3 text-right">Pembayaran</th><th class="px-4 py-3 text-right">Komisi</th><th class="px-4 py-3 text-center">Status</th><th class="px-4 py-3 text-center">Tanggal</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($commissions as $c)
                    @php $sc = ['pending'=>'amber','approved'=>'blue','paid'=>'green','rejected'=>'red'][$c->status] ?? 'gray'; @endphp
                    <tr>
                        <td class="px-4 py-3 text-gray-900">{{ $c->tenant?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $c->plan_name }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format($c->payment_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-green-500 font-medium">Rp {{ number_format($c->commission_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc  }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">{{ ucfirst($c->status) }}</span></td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $c->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada komisi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($commissions->hasPages())<div class="px-4 py-3 border-t border-gray-100">{{ $commissions->links() }}</div>@endif
    </div>

    {{-- Profile Update --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Profil & Rekening</h3>
        <form method="POST" action="{{ route('affiliate.profile') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @csrf @method('PUT')
            @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; @endphp
            <div><label class="block text-xs text-gray-600 mb-1">Perusahaan</label><input type="text" name="company_name" value="{{ $affiliate->company_name }}" class="{{ $cls }}"></div>
            <div><label class="block text-xs text-gray-600 mb-1">Telepon</label><input type="text" name="phone" value="{{ $affiliate->phone }}" class="{{ $cls }}"></div>
            <div><label class="block text-xs text-gray-600 mb-1">Bank</label><input type="text" name="bank_name" value="{{ $affiliate->bank_name }}" class="{{ $cls }}"></div>
            <div><label class="block text-xs text-gray-600 mb-1">No. Rekening</label><input type="text" name="bank_account" value="{{ $affiliate->bank_account }}" class="{{ $cls }}"></div>
            <div><label class="block text-xs text-gray-600 mb-1">Atas Nama</label><input type="text" name="bank_holder" value="{{ $affiliate->bank_holder }}" class="{{ $cls }}"></div>
            <div class="flex items-end"><button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button></div>
        </form>
    </div>
</x-app-layout>
