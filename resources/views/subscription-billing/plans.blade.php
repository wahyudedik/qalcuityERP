<x-app-layout>
    <x-slot name="header">Plan Langganan</x-slot>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <a href="{{ route('subscription-billing.index') }}" class="px-3 py-2 text-sm text-gray-500">← Subscriptions</a>
        <div class="flex-1"></div>
        @canmodule('subscription_billing', 'create')
        <button onclick="document.getElementById('modal-add-plan').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Plan</button>
        @endcanmodule
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($plans as $p)
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $p->name }}</h3>
                    @if($p->code)<p class="text-xs text-gray-400 font-mono">{{ $p->code }}</p>@endif
                </div>
                @if($p->is_active)<span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700">Aktif</span>
                @else<span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500">Nonaktif</span>@endif
            </div>
            <p class="text-2xl font-bold text-gray-900 mb-1">Rp {{ number_format($p->price, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mb-3">/ {{ $p->cycleLabel() }}{{ $p->trial_days > 0 ? " · {$p->trial_days} hari trial" : '' }}</p>
            @if($p->features)
            <ul class="text-xs text-gray-600 space-y-1 mb-3">
                @foreach($p->features as $f)<li>✓ {{ $f }}</li>@endforeach
            </ul>
            @endif
            <div class="flex items-center justify-between text-xs text-gray-400">
                <span>{{ $p->subscriptions_count }} subscriber</span>
                @canmodule('subscription_billing', 'delete')
                <form method="POST" action="{{ route('subscription-billing.plans.destroy', $p) }}" onsubmit="return confirm('Hapus plan ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-500 hover:underline">Hapus</button>
                </form>
                @endcanmodule
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-400">Belum ada plan.</div>
        @endforelse
    </div>
    @if($plans->hasPages())<div class="mt-4">{{ $plans->links() }}</div>@endif

    {{-- Modal Add Plan --}}
    <div id="modal-add-plan" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Buat Plan</h3>
                <button onclick="document.getElementById('modal-add-plan').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('subscription-billing.plans.store') }}" class="p-6 space-y-4">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Nama *</label><input type="text" name="name" required placeholder="Plan Premium" class="{{ $cls }}"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Kode</label><input type="text" name="code" placeholder="PREM" maxlength="30" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Harga (Rp) *</label><input type="number" name="price" required min="0" step="1000" class="{{ $cls }}"></div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Siklus *</label>
                        <select name="billing_cycle" required class="{{ $cls }}">
                            <option value="monthly">Bulanan</option><option value="quarterly">Triwulan</option><option value="semi_annual">Semester</option><option value="annual">Tahunan</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 mb-1">Trial (hari)</label><input type="number" name="trial_days" min="0" value="0" class="{{ $cls }}"></div>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Fitur (1 per baris)</label><textarea name="features" rows="3" placeholder="Fitur 1&#10;Fitur 2&#10;Fitur 3" class="{{ $cls }}"></textarea></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label><input type="text" name="description" class="{{ $cls }}"></div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-plan').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
