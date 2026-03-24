<x-app-layout>
    <x-slot name="header">Aturan Bisnis</x-slot>

    <div class="max-w-3xl mx-auto space-y-4">

        @if(session('success'))
        <div class="px-4 py-3 rounded-xl bg-green-500/10 border border-green-500/20 text-green-400 text-sm">
            {{ session('success') }}
        </div>
        @endif

        <form method="POST" action="{{ route('constraints.bulk') }}">
            @csrf

            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Aturan & Batasan Bisnis</p>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Konfigurasi aturan operasional yang diterapkan di seluruh sistem.</p>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($constraints as $i => $c)
                    <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                        <input type="hidden" name="constraints[{{ $i }}][id]" value="{{ $c->id }}">

                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $c->label }}</p>
                            <p class="text-xs text-gray-400 dark:text-slate-500 font-mono mt-0.5">{{ $c->key }}</p>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            @if($c->value_type === 'boolean')
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden"   name="constraints[{{ $i }}][value]"  value="false">
                                    <input type="hidden"   name="constraints[{{ $i }}][active]" value="0">
                                    <input type="checkbox" name="constraints[{{ $i }}][value]"  value="true"
                                           {{ $c->value === 'true' ? 'checked' : '' }}
                                           onchange="this.previousElementSibling.previousElementSibling.value = this.checked ? 'true' : 'false'"
                                           class="sr-only peer">
                                    <div class="w-10 h-5 bg-gray-200 dark:bg-white/10 peer-checked:bg-blue-600 rounded-full transition peer-focus:ring-2 peer-focus:ring-blue-500/30 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition peer-checked:after:translate-x-5"></div>
                                </label>
                            @elseif($c->value_type === 'percentage')
                                <div class="flex items-center gap-1.5">
                                    <input type="number" name="constraints[{{ $i }}][value]" value="{{ $c->value }}"
                                           min="0" max="100" step="0.1"
                                           class="w-24 px-3 py-1.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="text-sm text-gray-400 dark:text-slate-500">%</span>
                                </div>
                            @elseif($c->value_type === 'amount')
                                <div class="flex items-center gap-1.5">
                                    <span class="text-sm text-gray-400 dark:text-slate-500">Rp</span>
                                    <input type="number" name="constraints[{{ $i }}][value]" value="{{ $c->value }}"
                                           min="0" step="1000"
                                           class="w-40 px-3 py-1.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            @else
                                <input type="text" name="constraints[{{ $i }}][value]" value="{{ $c->value }}"
                                       class="w-40 px-3 py-1.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @endif

                            {{-- Active toggle (non-boolean types) --}}
                            @if($c->value_type !== 'boolean')
                            <label class="relative inline-flex items-center cursor-pointer" title="Aktifkan/nonaktifkan">
                                <input type="hidden"   name="constraints[{{ $i }}][active]" value="0">
                                <input type="checkbox" name="constraints[{{ $i }}][active]" value="1"
                                       {{ $c->is_active ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-8 h-4 bg-gray-200 dark:bg-white/10 peer-checked:bg-green-500 rounded-full transition after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-3 after:w-3 after:transition peer-checked:after:translate-x-4"></div>
                            </label>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-12 text-center text-gray-400 dark:text-slate-500 text-sm">
                        Belum ada aturan bisnis.
                    </div>
                    @endforelse
                </div>

                <div class="px-5 py-4 border-t border-gray-100 dark:border-white/10 flex justify-end">
                    <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium transition">
                        Simpan Semua
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
