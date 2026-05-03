@props([
    'data' => [],
    'fields' => [],
    'actions' => null,
    'titleField' => 'name',
    'subtitleField' => null,
    'statusField' => null,
    'emptyMessage' => 'Tidak ada data',
    'clickUrl' => null,
])

{{-- 
    Mobile Card View Component
    Usage:
    <x-mobile-card 
        :data="$items" 
        :fields="[
            ['label' => 'Email', 'key' => 'email'],
            ['label' => 'Telepon', 'key' => 'phone', 'type' => 'tel'],
            ['label' => 'Alamat', 'key' => 'address'],
        ]"
        titleField="name"
        subtitleField="company"
        :actions="function($item) { ... }"
    />
--}}

<div class="md:hidden space-y-3">
    @forelse($data as $index => $item)
        <div
            class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
            {{-- Card Header --}}
            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        @if ($clickUrl)
                            <a href="{{ is_callable($clickUrl) ? $clickUrl($item, $index) : str_replace(':id', $item->id ?? $item['id'], $clickUrl) }}"
                                class="block">
                                <h3 class="text-base font-semibold text-gray-900 truncate">
                                    {{ data_get($item, $titleField, '-') }}
                                </h3>
                            </a>
                        @else
                            <h3 class="text-base font-semibold text-gray-900 truncate">
                                {{ data_get($item, $titleField, '-') }}
                            </h3>
                        @endif

                        @if ($subtitleField && data_get($item, $subtitleField))
                            <p class="text-sm text-gray-500 mt-0.5 truncate">
                                {{ data_get($item, $subtitleField) }}
                            </p>
                        @endif
                    </div>

                    @if ($statusField && data_get($item, $statusField))
                        @php
                            $status = data_get($item, $statusField);
                            $statusClass = match (strtolower($status)) {
                                'active',
                                'aktif',
                                'published',
                                'completed',
                                'paid'
                                    => 'bg-green-100 text-green-700',
                                'inactive',
                                'nonaktif',
                                'draft',
                                'pending'
                                    => 'bg-amber-100 text-amber-700',
                                'cancelled',
                                'rejected',
                                'failed'
                                    => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <span
                            class="px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap {{ $statusClass }}">
                            {{ is_string($status) ? ucfirst($status) : $status }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Card Body --}}
            <div class="px-4 py-3 space-y-2.5">
                @foreach ($fields as $field)
                    @php
                        $value = data_get($item, $field['key']);
                        $displayValue = $value ?? '-';

                        // Format currency
                        if (($field['type'] ?? '') === 'currency' && is_numeric($value)) {
                            $displayValue = 'Rp ' . number_format($value, 0, ',', '.');
                        }

                        // Format date
                        if (($field['type'] ?? '') === 'date' && $value) {
                            $displayValue = is_string($value) ? $value : $value->format('d/m/Y');
                        }
                    @endphp

                    <div class="flex items-start justify-between gap-3">
                        <span class="text-sm text-gray-500 shrink-0">
                            {{ $field['label'] }}
                        </span>

                        @if (($field['type'] ?? '') === 'tel' && $value)
                            <a href="tel:{{ $value }}"
                                class="text-sm text-gray-900 text-right break-words hover:text-blue-600">
                                {{ $displayValue }}
                            </a>
                        @elseif(($field['type'] ?? '') === 'email' && $value)
                            <a href="mailto:{{ $value }}"
                                class="text-sm text-gray-900 text-right break-words hover:text-blue-600">
                                {{ $displayValue }}
                            </a>
                        @elseif(($field['type'] ?? '') === 'badge' && $value)
                            <span
                                class="px-2 py-0.5 rounded-full text-xs {{ $field['badgeClass'] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $displayValue }}
                            </span>
                        @else
                            <span
                                class="text-sm text-gray-900 text-right break-words {{ $field['class'] ?? '' }}">
                                {{ $displayValue }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Card Actions --}}
            @if ($actions)
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
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
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="mt-4 text-sm text-gray-500">{{ $emptyMessage }}</p>
        </div>
    @endforelse
</div>
