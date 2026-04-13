<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                📋 Mix Design Version History - {{ $mixDesign->grade }}
            </h2>
            <a href="{{ route('manufacturing.mix-design') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                ← Back to Mix Design
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Mix Design Info --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-slate-400">Grade</div>
                        <div class="text-xl font-bold text-gray-900 dark:text-white">{{ $mixDesign->grade }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-slate-400">Current Version</div>
                        <div class="text-xl font-bold text-blue-600">v{{ $versions->count() }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-slate-400">Total Changes</div>
                        <div class="text-xl font-bold text-purple-600">{{ $versions->count() }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-slate-400">Last Modified</div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $versions->first()?->created_at?->format('d M Y H:i') ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Version Timeline --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-6">📜 Version Timeline</h3>

                <div class="space-y-6">
                    @foreach ($versions as $index => $version)
                        <div
                            class="relative pl-8 border-l-2 {{ $index === 0 ? 'border-blue-500' : 'border-gray-300 dark:border-gray-600' }}">
                            {{-- Version Badge --}}
                            <div class="absolute -left-3 top-0">
                                <span
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $index === 0 ? 'bg-blue-600' : 'bg-gray-400' }} text-white text-xs font-bold">
                                    {{ $version->version_number }}
                                </span>
                            </div>

                            {{-- Version Card --}}
                            <div class="bg-gray-50 dark:bg-white/5 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h4 class="font-bold text-lg text-gray-900 dark:text-white">
                                            Version {{ $version->version_number }}
                                            @if ($version->isApproved())
                                                <span
                                                    class="ml-2 px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">✓
                                                    Approved</span>
                                            @else
                                                <span
                                                    class="ml-2 px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400">⏳
                                                    Pending</span>
                                            @endif
                                        </h4>
                                        <p class="text-sm text-gray-500 dark:text-slate-400">
                                            {{ $version->created_at->format('d M Y, H:i') }} by
                                            {{ $version->createdBy?->name ?? 'Unknown' }}
                                        </p>
                                    </div>
                                    @if (!$version->isApproved())
                                        <form method="POST"
                                            action="{{ route('manufacturing.mix-design.versions.approve', $version) }}"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm">
                                                ✓ Approve
                                            </button>
                                        </form>
                                    @endif
                                </div>

                                {{-- Change Reason --}}
                                <div
                                    class="mb-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded">
                                    <div class="text-xs text-blue-600 dark:text-blue-400 font-semibold mb-1">CHANGE
                                        REASON:</div>
                                    <p class="text-sm text-gray-700 dark:text-slate-300">{{ $version->change_reason }}
                                    </p>
                                </div>

                                {{-- Changes Detail (for versions > 1) --}}
                                @if ($version->version_number > 1)
                                    <div class="mb-3">
                                        <div class="text-xs text-gray-600 dark:text-slate-400 font-semibold mb-2">
                                            CHANGES FROM PREVIOUS VERSION:</div>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @php
                                                $changes = $version->getChanges();
                                            @endphp
                                            @if (is_array($changes) && !isset($changes['message']))
                                                @foreach ($changes as $field => $change)
                                                    <div
                                                        class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 rounded text-sm">
                                                        <span
                                                            class="text-gray-600 dark:text-slate-400">{{ str_replace('_', ' ', ucfirst($field)) }}</span>
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                class="text-red-600 line-through">{{ $change['old'] }}</span>
                                                            <span class="text-gray-400">→</span>
                                                            <span
                                                                class="text-green-600 font-semibold">{{ $change['new'] }}</span>
                                                            @if ($change['diff'] !== null)
                                                                <span
                                                                    class="text-xs px-2 py-0.5 rounded {{ $change['diff'] > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                                                    {{ $change['diff'] > 0 ? '+' : '' }}{{ $change['diff'] }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="text-sm text-gray-500 italic">
                                                    {{ $changes['message'] ?? 'No changes recorded' }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Material Composition Summary --}}
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm">
                                    <div class="p-2 bg-white dark:bg-gray-800 rounded">
                                        <div class="text-xs text-gray-500">Cement</div>
                                        <div class="font-semibold">{{ $version->cement_kg }} kg</div>
                                    </div>
                                    <div class="p-2 bg-white dark:bg-gray-800 rounded">
                                        <div class="text-xs text-gray-500">Water</div>
                                        <div class="font-semibold">{{ $version->water_liter }} L</div>
                                    </div>
                                    <div class="p-2 bg-white dark:bg-gray-800 rounded">
                                        <div class="text-xs text-gray-500">Fine Agg</div>
                                        <div class="font-semibold">{{ $version->fine_agg_kg }} kg</div>
                                    </div>
                                    <div class="p-2 bg-white dark:bg-gray-800 rounded">
                                        <div class="text-xs text-gray-500">Coarse Agg</div>
                                        <div class="font-semibold">{{ $version->coarse_agg_kg }} kg</div>
                                    </div>
                                    <div class="p-2 bg-white dark:bg-gray-800 rounded">
                                        <div class="text-xs text-gray-500">W/C Ratio</div>
                                        <div class="font-semibold">{{ $version->water_cement_ratio }}</div>
                                    </div>
                                </div>

                                {{-- Approval Info --}}
                                @if ($version->isApproved())
                                    <div
                                        class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-slate-400">
                                        Approved by {{ $version->approvedBy?->name ?? 'Unknown' }} on
                                        {{ $version->approved_at->format('d M Y, H:i') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Version Comparison Tool --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">🔍 Compare Versions</h3>
                <div class="flex gap-4 mb-4">
                    <select id="compareVersion1" class="border rounded px-3 py-2 flex-1">
                        <option value="">Select Version 1</option>
                        @foreach ($versions as $v)
                            <option value="{{ $v->id }}">Version {{ $v->version_number }}</option>
                        @endforeach
                    </select>
                    <select id="compareVersion2" class="border rounded px-3 py-2 flex-1">
                        <option value="">Select Version 2</option>
                        @foreach ($versions as $v)
                            <option value="{{ $v->id }}">Version {{ $v->version_number }}</option>
                        @endforeach
                    </select>
                    <button onclick="compareVersions()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                        Compare
                    </button>
                </div>
                <div id="comparisonResult" class="hidden"></div>
            </div>
        </div>
    </div>

    <script>
        function compareVersions() {
            const v1 = document.getElementById('compareVersion1').value;
            const v2 = document.getElementById('compareVersion2').value;

            if (!v1 || !v2) {
                alert('Please select both versions to compare');
                return;
            }

            if (v1 === v2) {
                alert('Please select different versions');
                return;
            }

            // TODO: Implement AJAX comparison
            alert('Comparison feature - Will show detailed diff between versions ' + v1 + ' and ' + v2);
        }
    </script>
</x-app-layout>
