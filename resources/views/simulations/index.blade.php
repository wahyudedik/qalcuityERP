<x-app-layout>
    <x-slot name="header">Simulasi Bisnis (What If)</x-slot>
    <x-slot name="pageHeader">
        <a href="{{ route('simulations.create') }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 text-sm font-medium transition">
            + Simulasi Baru
        </a>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($simulations->isEmpty())
            <div class="text-center py-16 text-gray-500">
                <div class="text-5xl mb-4">🔮</div>
                <p class="text-lg font-medium">Belum ada simulasi</p>
                <p class="text-sm mt-1">Buat simulasi "What If" untuk proyeksi dampak keputusan bisnis.</p>
                <a href="{{ route('simulations.create') }}"
                   class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm">
                    Buat Simulasi Pertama
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($simulations as $sim)
                    @php
                        $icons = [
                            'price_increase' => '📈',
                            'new_branch'     => '🏪',
                            'stock_out'      => '📦',
                            'cost_reduction' => '✂️',
                            'demand_change'  => '📊',
                        ];
                        $labels = [
                            'price_increase' => 'Kenaikan Harga',
                            'new_branch'     => 'Cabang Baru',
                            'stock_out'      => 'Stok Habis',
                            'cost_reduction' => 'Efisiensi Biaya',
                            'demand_change'  => 'Perubahan Demand',
                        ];
                    @endphp
                    <div class="bg-white rounded-2xl border border-gray-200 p-5 flex flex-col gap-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-2xl">{{ $icons[$sim->scenario_type] ?? '🔮' }}</span>
                                <h3 class="font-semibold text-gray-900 mt-1">{{ $sim->name }}</h3>
                                <span class="text-xs text-gray-500">
                                    {{ $labels[$sim->scenario_type] ?? $sim->scenario_type }}
                                </span>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full
                                {{ $sim->status === 'calculated' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $sim->status === 'calculated' ? 'Selesai' : 'Draft' }}
                            </span>
                        </div>

                        @if($sim->ai_narrative)
                            <p class="text-sm text-gray-600 line-clamp-2">{{ $sim->ai_narrative }}</p>
                        @endif

                        <div class="flex items-center justify-between mt-auto pt-2 border-t border-gray-100">
                            <span class="text-xs text-gray-400">{{ $sim->created_at->diffForHumans() }}</span>
                            <div class="flex gap-2">
                                <a href="{{ route('simulations.show', $sim) }}"
                                   class="text-xs text-indigo-600 hover:underline">Detail</a>
                                <form method="POST" action="{{ route('simulations.destroy', $sim) }}"
                                      onsubmit="return confirm('Hapus simulasi ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-500 hover:underline">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $simulations->links() }}</div>
        @endif
    </div>
</x-app-layout>
