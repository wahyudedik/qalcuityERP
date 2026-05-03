@php
    $openAnomalies = $data['openAnomalies'] ?? [];

    // Convert incomplete Collection objects to array to avoid unserialize errors
    if (is_object($openAnomalies)) {
        try {
            // Try to convert to array if it's a Collection
        if (method_exists($openAnomalies, 'toArray')) {
                $openAnomalies = $openAnomalies->toArray();
            } else {
                $openAnomalies = (array) $openAnomalies;
            }
        } catch (\Error $e) {
            // If object is incomplete, default to empty array
            $openAnomalies = [];
        }
    }

    // Now safely check if not empty
    $hasAnomalies =
        (!is_null($openAnomalies) && (is_array($openAnomalies) && !empty($openAnomalies))) ||
        (is_countable($openAnomalies) && count($openAnomalies) > 0);
    $anomalyCount = is_array($openAnomalies) ? count($openAnomalies) : 0;
@endphp
@if ($hasAnomalies)
    <div class="h-full" id="anomaly-section">
        <div class="flex items-center justify-between gap-2 mb-3">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-red-500/20 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900">Anomali Terdeteksi</p>
                <span
                    class="text-xs bg-red-500/20 text-red-400 font-medium px-2 py-0.5 rounded-full">{{ $anomalyCount }}
                    open</span>
            </div>
            <a href="{{ route('anomalies.index') }}" class="text-xs text-red-400 hover:text-red-300 font-medium">Lihat
                semua →</a>
        </div>
        <div class="space-y-2" id="anomaly-list">
            @foreach ($openAnomalies as $anomaly)
                @php
                    $aBorder = match ($anomaly->severity) {
                        'critical' => 'border-red-500/40 bg-red-500/5',
                        'warning' => 'border-yellow-500/40 bg-yellow-500/5',
                        default => 'border-orange-500/20 bg-orange-500/5',
                    };
                    $aBadge = match ($anomaly->severity) {
                        'critical' => 'bg-red-500/20 text-red-400',
                        'warning' => 'bg-yellow-500/20 text-yellow-400',
                        default => 'bg-orange-500/20 text-orange-400',
                    };
                    $aIcon = match ($anomaly->severity) {
                        'critical' => 'text-red-400',
                        'warning' => 'text-yellow-400',
                        default => 'text-orange-400',
                    };
                @endphp
                <div class="rounded-xl border {{ $aBorder }} p-3.5 flex items-start gap-3"
                    id="anomaly-{{ $anomaly->id ?? 'unknown' }}">
                    <svg class="w-4 h-4 {{ $aIcon }} shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <p class="text-sm font-semibold text-gray-900 truncate">
                                {{ $anomaly->title ?? 'Unknown Anomaly' }}</p>
                            <span class="text-xs font-medium px-1.5 py-0.5 rounded-full shrink-0 {{ $aBadge }}">
                                {{ match ($anomaly->severity ?? 'info') {'critical' => 'Kritis','warning' => 'Perhatian',default => 'Info'} }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            {{ $anomaly->description ?? '' }}</p>
                        <p class="text-xs text-gray-400 mt-1">
                            {{ isset($anomaly->created_at) ? $anomaly->created_at->diffForHumans() : 'Unknown time' }}
                        </p>
                    </div>
                    <button onclick="acknowledgeAnomaly({{ $anomaly->id ?? 0 }}, this)"
                        class="text-xs text-gray-400 hover:text-green-400 transition shrink-0 font-medium"
                        title="Tandai sudah ditinjau">
                        ✓ Tinjau
                    </button>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div
        class="bg-white rounded-2xl border border-gray-200 p-5 h-full flex items-center justify-center">
        <div class="text-center text-gray-400">
            <svg class="w-8 h-8 mx-auto mb-2 text-green-500/30" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm">Tidak ada anomali terdeteksi</p>
        </div>
    </div>
@endif
