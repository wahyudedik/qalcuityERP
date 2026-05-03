<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Shared Report: ') . $sharedReport->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Report Info -->
            <div class="bg-white rounded-xl p-6 shadow mb-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">
                            {{ $sharedReport->name }}
                        </h3>
                        <p class="text-sm text-gray-600">
                            Shared by <strong>{{ $sharedReport->creator->name ?? 'Unknown' }}</strong>
                            on {{ $sharedReport->created_at->format('d M Y H:i') }}
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        @if ($canDownload)
                            <div class="dropdown relative">
                                <button
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                    📥 Download
                                </button>
                                <div
                                    class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden">
                                    <a href="{{ route('analytics.shared.download', ['id' => $sharedReport->report_id, 'format' => 'pdf']) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        📄 PDF
                                    </a>
                                    <a href="{{ route('analytics.shared.download', ['id' => $sharedReport->report_id, 'format' => 'excel']) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        📊 Excel
                                    </a>
                                    <a href="{{ route('analytics.shared.download', ['id' => $sharedReport->report_id, 'format' => 'csv']) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        📋 CSV
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Type:</span>
                        <p class="font-semibold text-gray-900 capitalize">
                            {{ str_replace('_', ' ', $sharedReport->type) }}
                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500">Access Level:</span>
                        <p class="font-semibold text-gray-900 capitalize">
                            {{ $sharedReport->access_level }}
                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500">Expires:</span>
                        <p
                            class="font-semibold {{ $sharedReport->isExpired() ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $sharedReport->expires_at?->format('d M Y') ?? 'Never' }}
                        </p>
                    </div>
                    <div>
                        <span class="text-gray-500">Views:</span>
                        <p class="font-semibold text-gray-900">
                            {{ $sharedReport->access_count }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Report Content -->
            <div class="bg-white rounded-xl p-6 shadow">
                <h4 class="text-lg font-semibold text-gray-900 mb-4">📊 Report Data</h4>

                @if (isset($sharedReport->report_data['financial_kpis']))
                    <!-- Executive Dashboard Data -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                        <div
                            class="p-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg">
                            <p class="text-sm text-gray-600">Revenue</p>
                            <p class="text-2xl font-bold text-gray-900">
                                Rp
                                {{ number_format($sharedReport->report_data['financial_kpis']['revenue']['current'] ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div
                            class="p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg">
                            <p class="text-sm text-gray-600">Profit Margin</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ $sharedReport->report_data['financial_kpis']['profit_margin']['current'] ?? 0 }}%
                            </p>
                        </div>
                        <div
                            class="p-4 bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg">
                            <p class="text-sm text-gray-600">Orders</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($sharedReport->report_data['operational_kpis']['orders']['current'] ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <div
                            class="p-4 bg-gradient-to-br from-orange-50 to-red-50 rounded-lg">
                            <p class="text-sm text-gray-600">Customers</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ number_format($sharedReport->report_data['customer_kpis']['new_customers']['current'] ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @endif

                @if (isset($sharedReport->report_data['data']))
                    <!-- Generic Report Data -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">Metric</th>
                                    <th class="px-4 py-3 text-right">Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($sharedReport->report_data['data'] as $metric => $value)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            {{ ucwords(str_replace('_', ' ', $metric)) }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-600">
                                            @if (is_array($value))
                                                {{ json_encode($value) }}
                                            @elseif(is_numeric($value))
                                                {{ number_format($value, 0, ',', '.') }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>This report was shared with you and will expire on
                    {{ $sharedReport->expires_at?->format('d M Y H:i') ?? 'never' }}.</p>
                <p class="mt-1">Powered by {{ config('app.name') }}</p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Dropdown toggle
            document.querySelectorAll('.dropdown').forEach(dropdown => {
                const button = dropdown.querySelector('button');
                const menu = dropdown.querySelector('.dropdown-menu');

                button.addEventListener('click', (e) => {
                    e.stopPropagation();
                    menu.classList.toggle('hidden');
                });

                document.addEventListener('click', () => {
                    menu.classList.add('hidden');
                });
            });
        </script>
    @endpush
</x-app-layout>
