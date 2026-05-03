<x-app-layout>
    <x-slot name="header">Deteksi Anomali</x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Header row: tabs + action -->
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div class="flex flex-wrap gap-2">
                @foreach(['open' => '🔴 Open', 'acknowledged' => '🟡 Ditinjau', 'resolved' => '🟢 Selesai'] as $s => $label)
                    <a href="{{ request()->fullUrlWithQuery(['status' => $s]) }}"
                       class="px-3 py-1.5 rounded-full text-sm font-medium transition
                           {{ request('status') === $s
                               ? 'bg-gray-800 text-white'
                               : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $label }}
                        @if(isset($counts[$s]))
                            <span class="ml-1 text-xs opacity-70">({{ $counts[$s] }})</span>
                        @endif
                    </a>
                @endforeach
                <a href="{{ route('anomalies.index') }}"
                   class="px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-600 hover:bg-gray-200">
                    Semua
                </a>
            </div>

            <form method="POST" action="{{ route('anomalies.detect') }}">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 text-sm font-medium flex items-center gap-1.5">
                    🔍 Jalankan Deteksi
                </button>
            </form>
        </div>

        @if($anomalies->isEmpty())
            <div class="text-center py-16 text-gray-500">
                <div class="text-5xl mb-4">✅</div>
                <p class="text-lg font-medium">Tidak ada anomali ditemukan</p>
                <p class="text-sm mt-1">Klik "Jalankan Deteksi" untuk memeriksa anomali terbaru.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($anomalies as $anomaly)
                    @php
                        $severityClass = match($anomaly->severity) {
                            'critical' => 'border-red-400 bg-red-50',
                            'warning'  => 'border-yellow-400 bg-yellow-50',
                            default    => 'border-blue-400 bg-blue-50',
                        };
                    @endphp
                    <div class="border-l-4 {{ $severityClass }} rounded-r-xl p-4 flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold text-gray-800 text-sm">{{ $anomaly->title }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full
                                    {{ $anomaly->status === 'open' ? 'bg-red-100 text-red-700' :
                                       ($anomaly->status === 'acknowledged' ? 'bg-yellow-100 text-yellow-700' :
                                       'bg-green-100 text-green-700') }}">
                                    {{ ucfirst($anomaly->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600">{{ $anomaly->description }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $anomaly->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            @if($anomaly->status === 'open')
                                <form method="POST" action="{{ route('anomalies.acknowledge', $anomaly) }}">
                                    @csrf
                                    <button class="text-xs px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200">
                                        Tinjau
                                    </button>
                                </form>
                            @endif
                            @if($anomaly->status !== 'resolved')
                                <form method="POST" action="{{ route('anomalies.resolve', $anomaly) }}">
                                    @csrf
                                    <button class="text-xs px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200">
                                        Selesai
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $anomalies->links() }}</div>
        @endif
    </div>
</x-app-layout>
