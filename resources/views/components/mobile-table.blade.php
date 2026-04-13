@props([
    'data' => [],
    'fields' => [],
    'actions' => null,
    'titleField' => 'name',
    'subtitleField' => null,
    'statusField' => null,
    'emptyMessage' => 'Tidak ada data',
    'clickUrl' => null,
    'imageField' => null,
    'badgeField' => null,
])

{{-- 
    Mobile Table Component
    Advanced mobile card view with flexible field display
    
    Usage:
    <x-mobile-table 
        :data="$invoices"
        :fields="[
            ['label' => 'No', 'key' => 'number', 'link' => 'invoices.show'],
            ['label' => 'Customer', 'key' => 'customer.name'],
            ['label' => 'Total', 'key' => 'total_amount', 'type' => 'currency'],
            ['label' => 'Jatuh Tempo', 'key' => 'due_date', 'type' => 'date'],
        ]"
        titleField="number"
        subtitleField="customer.name"
        statusField="status"
        imageField="customer.logo"
        :actions="fn($item) => view('invoices._mobile-actions', ['invoice' => $item])"
    />
--}}

<div class="md:hidden space-y-3" role="list" aria-label="Mobile card view">
    @forelse($data as $index => $item)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm transition-all duration-200 hover:shadow-md touch-ripple"
            role="listitem" aria-label="Item {{ $index + 1 }}">
            {{-- Card Header --}}
            <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        {{-- Optional Image/Avatar --}}
                        @if ($imageField && data_get($item, $imageField))
                            <img src="{{ data_get($item, $imageField) }}" alt=""
                                class="w-10 h-10 rounded-xl object-cover shrink-0 border border-gray-200 dark:border-white/10"
                                loading="lazy">
                        @endif

                        <div class="flex-1 min-w-0">
                            @if ($clickUrl)
                                <a href="{{ is_callable($clickUrl) ? $clickUrl($item, $index) : (str_contains($clickUrl, ':id') ? str_replace(':id', $item->id ?? ($item['id'] ?? ''), $clickUrl) : route($clickUrl, $item->id ?? ($item['id'] ?? ''))) }}"
                                    class="block" aria-label="View details">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white truncate">
                                        {{ data_get($item, $titleField, '-') }}
                                    </h3>
                                </a>
                            @else
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white truncate">
                                    {{ data_get($item, $titleField, '-') }}
                                </h3>
                            @endif

                            @if ($subtitleField && data_get($item, $subtitleField))
                                <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5 truncate">
                                    {{ data_get($item, $subtitleField) }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    @if ($statusField && data_get($item, $statusField))
                        @php
                            $status = data_get($item, $statusField);
                            $statusClass = match (strtolower($status)) {
                                'active',
                                'aktif',
                                'published',
                                'completed',
                                'paid',
                                'lunas',
                                'success'
                                    => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                                'inactive',
                                'nonaktif',
                                'draft',
                                'pending',
                                'menunggu'
                                    => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                                'cancelled',
                                'rejected',
                                'failed',
                                'gagal',
                                'expired',
                                'kadaluarsa'
                                    => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                                'processing',
                                'diproses',
                                'in_progress'
                                    => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                                'sent',
                                'terkirim',
                                'shipped'
                                    => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                                default => 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-slate-400',
                            };
                        @endphp
                        <span
                            class="px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap {{ $statusClass }}">
                            {{ is_string($status) ? ucfirst($status) : $status }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Card Body - Fields --}}
            <div class="px-4 py-3 space-y-2.5">
                @foreach ($fields as $field)
                    @php
                        $value = data_get($item, $field['key']);
                        $displayValue = $value ?? '-';
                        $type = $field['type'] ?? 'text';

                        // Format currency
                        if ($type === 'currency' && is_numeric($value)) {
                            $displayValue = 'Rp ' . number_format($value, 0, ',', '.');
                        }

                        // Format date
                        if ($type === 'date' && $value) {
                            $displayValue = is_string($value)
                                ? $value
                                : (method_exists($value, 'format')
                                    ? $value->format('d/m/Y')
                                    : $value);
                        }

                        // Format datetime
                        if ($type === 'datetime' && $value) {
                            $displayValue = is_string($value)
                                ? $value
                                : (method_exists($value, 'format')
                                    ? $value->format('d/m/Y H:i')
                                    : $value);
                        }

                        // Format number
                        if ($type === 'number' && is_numeric($value)) {
                            $displayValue = number_format($value, 0, ',', '.');
                        }

                        // Format percentage
                        if ($type === 'percentage' && is_numeric($value)) {
                            $displayValue = number_format($value, 1) . '%';
                        }

                        // Determine display class
                        $valueClass = $field['class'] ?? '';
                        if ($type === 'currency' && $value > 0) {
                            $valueClass .= ' font-semibold text-green-600 dark:text-green-400';
                        }
                        if ($type === 'currency' && $value < 0) {
                            $valueClass .= ' font-semibold text-red-600 dark:text-red-400';
                        }
                    @endphp

                    <div class="flex items-start justify-between gap-3">
                        <span class="text-sm text-gray-500 dark:text-slate-400 shrink-0">
                            {{ $field['label'] }}
                        </span>

                        @if ($type === 'tel' && $value)
                            <a href="tel:{{ $value }}"
                                class="text-sm text-blue-600 dark:text-blue-400 text-right break-words hover:underline">
                                {{ $displayValue }}
                            </a>
                        @elseif($type === 'email' && $value)
                            <a href="mailto:{{ $value }}"
                                class="text-sm text-blue-600 dark:text-blue-400 text-right break-words hover:underline">
                                {{ $displayValue }}
                            </a>
                        @elseif($type === 'link' && $value && isset($field['url']))
                            <a href="{{ is_callable($field['url']) ? $field['url']($item) : $field['url'] }}"
                                class="text-sm text-blue-600 dark:text-blue-400 text-right break-words hover:underline">
                                {{ $displayValue }}
                            </a>
                        @elseif($type === 'badge' && $value)
                            <span
                                class="px-2 py-0.5 rounded-full text-xs {{ $field['badgeClass'] ?? 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-slate-400' }}">
                                {{ $displayValue }}
                            </span>
                        @elseif($type === 'boolean')
                            <span
                                class="text-sm {{ $value ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $value ? 'Ya' : 'Tidak' }}
                            </span>
                        @elseif($type === 'progress' && is_numeric($value))
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $displayValue }}</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-white/10 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                        style="width: {{ min(100, max(0, $value)) }}%"></div>
                                </div>
                            </div>
                        @else
                            <span
                                class="text-sm text-gray-900 dark:text-white text-right break-words {{ $valueClass }}">
                                {{ $displayValue }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Card Actions --}}
            @if ($actions)
                <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5">
                    <div class="flex items-center justify-end gap-2">
                        @if (is_callable($actions))
                            {!! $actions($item, $index) !!}
                        @else
                            {!! $actions !!}
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @empty
        {{-- Empty State --}}
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="mt-4 text-sm text-gray-500 dark:text-slate-400">{{ $emptyMessage }}</p>
        </div>
    @endforelse
</div>
