<x-app-layout>
    <x-slot name="header">Onboarding Karyawan</x-slot>

    <div class="flex items-center justify-between mb-5">
        <form method="GET" class="flex gap-2">
            <select name="status" onchange="this.form.submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua</option>
                <option value="in_progress" @selected(request('status')==='in_progress')>Berjalan</option>
                <option value="completed"   @selected(request('status')==='completed')>Selesai</option>
            </select>
        </form>
        <button onclick="document.getElementById('modal-start-onboarding').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Mulai Onboarding</button>
    </div>

    <div class="space-y-3">
        @forelse($onboardings as $ob)
        @php $pct = $ob->progressPercent(); @endphp
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="font-semibold text-gray-900">{{ $ob->employee?->name }}</p>
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $ob->status === 'completed' ? 'bg-green-500/20 text-green-400' : 'bg-blue-500/20 text-blue-400' }}">
                            {{ $ob->status === 'completed' ? 'Selesai' : 'Berjalan' }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500">
                        {{ $ob->employee?->position ?? '-' }} · Mulai: {{ $ob->start_date->format('d M Y') }}
                        @if($ob->completed_at) · Selesai: {{ $ob->completed_at->format('d M Y') }}@endif
                    </p>
                </div>
                <div class="flex items-center gap-4 shrink-0">
                    <div class="text-right">
                        <p class="text-sm font-bold {{ $pct >= 100 ? 'text-green-400' : 'text-blue-400' }}">{{ $pct }}%</p>
                        <div class="w-32 h-2 bg-gray-200 rounded-full mt-1">
                            <div class="h-2 rounded-full {{ $pct >= 100 ? 'bg-green-500' : 'bg-blue-500' }}" style="width:{{ $pct }}%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $ob->tasks->where('is_done', true)->count() }}/{{ $ob->tasks->count() }} tugas
                        </p>
                    </div>
                    <a href="{{ route('hrm.onboarding.detail', $ob) }}"
                       class="px-3 py-1.5 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700">Detail</a>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
            <p class="text-gray-400 text-sm">Belum ada onboarding aktif.</p>
        </div>
        @endforelse
    </div>
    @if($onboardings->hasPages())
    <div class="mt-4">{{ $onboardings->links() }}</div>
    @endif

    {{-- Modal Mulai Onboarding Manual --}}
    <div id="modal-start-onboarding" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Mulai Onboarding</h3>
                <button onclick="document.getElementById('modal-start-onboarding').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('hrm.onboarding.start') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan *</label>
                    <select name="employee_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="">Pilih karyawan...</option>
                        @foreach(\App\Models\Employee::where('tenant_id', auth()->user()->tenant_id)->where('status','active')->orderBy('name')->get() as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} — {{ $emp->position ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Mulai *</label>
                    <input type="date" name="start_date" value="{{ today()->format('Y-m-d') }}" required
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-start-onboarding').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Mulai</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
