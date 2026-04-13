<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ $formula->formula_code }}
                    </h2>
                    <span
                        class="px-3 py-1 text-sm font-medium rounded-full
                        @if ($formula->status == 'draft') bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                        @elseif($formula->status == 'testing') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300
                        @elseif($formula->status == 'approved') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                        @elseif($formula->status == 'production') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300
                        @else bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 @endif">
                        <i class="fas fa-circle text-xs mr-1"></i>{{ $formula->status_label }}
                    </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $formula->formula_name }}</p>
                @if ($formula->brand)
                    <p class="text-xs text-gray-500 dark:text-gray-500">Brand: {{ $formula->brand }}</p>
                @endif
            </div>
            <a href="{{ route('cosmetic.formulas.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Formulas
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Update -->
            <div class="mb-4 flex justify-end">
                @if ($formula->isDraft() || $formula->isTesting())
                    <form method="POST" action="{{ route('cosmetic.formulas.update-status', $formula) }}"
                        class="inline-block">
                        @csrf
                        <select name="status" onchange="this.form.submit()"
                            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm dark:bg-gray-700 dark:text-white">
                            <option value="draft" {{ $formula->status == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="testing" {{ $formula->status == 'testing' ? 'selected' : '' }}>In Testing
                            </option>
                            <option value="approved" {{ $formula->status == 'approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="production" {{ $formula->status == 'production' ? 'selected' : '' }}>
                                Production</option>
                            <option value="discontinued">Discontinued</option>
                        </select>
                    </form>
                @endif
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-weight mr-1"></i>Batch Size
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($formula->batch_size, 2) }} {{ $formula->batch_unit }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-flask mr-1"></i>Total Ingredients
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $ingredients->count() }}
                        items</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-money-bill mr-1"></i>Total Cost
                    </div>
                    <div class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">Rp
                        {{ number_format($totalCost, 0, ',', '.') }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        <i class="fas fa-calculator mr-1"></i>Cost per Unit
                    </div>
                    <div class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">Rp
                        {{ number_format($formula->cost_per_unit ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>

            <!-- Tabs -->
            <div x-data="{ activeTab: 'ingredients' }" class="space-y-6">
                <!-- Tab Navigation -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex -mb-px">
                            <button @click="activeTab = 'ingredients'"
                                :class="activeTab === 'ingredients' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                    'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                                class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                                <i class="fas fa-flask mr-2"></i>Ingredients
                            </button>
                            <button @click="activeTab = 'versions'"
                                :class="activeTab === 'versions' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                    'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                                class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                                <i class="fas fa-code-branch mr-2"></i>Versions ({{ $versions->count() }})
                            </button>
                            <button @click="activeTab = 'stability'"
                                :class="activeTab === 'stability' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                    'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                                class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                                <i class="fas fa-vial mr-2"></i>Stability Tests
                            </button>
                            <button @click="activeTab = 'info'"
                                :class="activeTab === 'info' ? 'border-blue-500 text-blue-600 dark:text-blue-400' :
                                    'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300'"
                                class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                                <i class="fas fa-info-circle mr-2"></i>Information
                            </button>
                        </nav>
                    </div>

                    <!-- Ingredients Tab -->
                    <div x-show="activeTab === 'ingredients'" class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            <i class="fas fa-flask mr-2 text-purple-600"></i>Formula Ingredients
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            #</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            INCI Name</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Common Name</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Quantity</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            %</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Function</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">
                                            Phase</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($ingredients as $index => $ingredient)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $ingredient->sort_order }}</td>
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $ingredient->inci_name }}</div>
                                                @if ($ingredient->cas_number)
                                                    <a href="{{ $ingredient->cas_number_link }}" target="_blank"
                                                        class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                                        CAS: {{ $ingredient->cas_number }}
                                                    </a>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                                {{ $ingredient->common_name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                                {{ number_format($ingredient->quantity, 3) }} {{ $ingredient->unit }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                                {{ $ingredient->percentage ? number_format($ingredient->percentage, 2) . '%' : '-' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span
                                                    class="px-2 py-1 text-xs font-medium rounded-full
                                                    @if ($ingredient->function == 'active') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300
                                                    @elseif($ingredient->function == 'preservative') bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300
                                                    @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300 @endif">
                                                    {{ $ingredient->function_label }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                                {{ $ingredient->phase_label }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <div class="text-sm text-blue-900 dark:text-blue-300">
                                <i class="fas fa-weight-hanging mr-2"></i><strong>Total Quantity:</strong>
                                {{ number_format($totalQuantity, 3) }} {{ $formula->batch_unit }}
                            </div>
                        </div>
                    </div>

                    <!-- Versions Tab -->
                    <div x-show="activeTab === 'versions'" class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            <i class="fas fa-code-branch mr-2 text-blue-600"></i>Formula Versions
                        </h3>
                        @if ($versions->count() > 0)
                            <div class="space-y-4">
                                @foreach ($versions as $version)
                                    <div
                                        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 dark:bg-gray-700/50">
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                                {{ $version->version_formatted }}</h4>
                                            <span
                                                class="text-xs text-gray-500 dark:text-gray-400">{{ $version->created_at->format('d M Y H:i') }}</span>
                                        </div>
                                        @if ($version->changes_summary)
                                            <div class="mb-2">
                                                <strong
                                                    class="text-sm text-gray-700 dark:text-gray-300">Changes:</strong>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $version->changes_summary }}</p>
                                            </div>
                                        @endif
                                        @if ($version->reason_for_change)
                                            <div class="mb-2">
                                                <strong
                                                    class="text-sm text-gray-700 dark:text-gray-300">Reason:</strong>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $version->reason_for_change }}</p>
                                            </div>
                                        @endif
                                        @if ($version->changer)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                <i class="fas fa-user mr-1"></i>Changed by:
                                                {{ $version->changer->name }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-400">
                                <i class="fas fa-code-branch text-4xl mb-2"></i>
                                <p>No versions yet. Versions are created when formula is approved.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Stability Tests Tab -->
                    <div x-show="activeTab === 'stability'" class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                <i class="fas fa-vial mr-2 text-green-600"></i>Stability Tests
                            </h3>
                            @if ($formula->isTesting() || $formula->isApproved())
                                <button onclick="document.getElementById('add-test-modal').classList.remove('hidden')"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                    <i class="fas fa-plus mr-1"></i>Add Test
                                </button>
                            @endif
                        </div>

                        @if ($stabilityTests->count() > 0)
                            <div class="space-y-4">
                                @foreach ($stabilityTests as $test)
                                    <div
                                        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 dark:bg-gray-700/50">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <span
                                                    class="text-sm font-medium text-gray-900 dark:text-white">{{ $test->test_code }}</span>
                                                <span
                                                    class="ml-2 px-2 py-1 text-xs font-medium rounded-full
                                                    @if ($test->test_type == 'accelerated') bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300
                                                    @elseif($test->test_type == 'real_time') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300
                                                    @else bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 @endif">
                                                    {{ $test->test_type_label }}
                                                </span>
                                            </div>
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full
                                                @if ($test->status == 'in_progress') bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300
                                                @elseif($test->status == 'completed') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                                                @else bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 @endif">
                                                {{ $test->status_label }}
                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm mt-3">
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400">Start:</span>
                                                <span
                                                    class="text-gray-900 dark:text-white">{{ $test->start_date->format('d M Y') }}</span>
                                            </div>
                                            @if ($test->expected_end_date)
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">Expected End:</span>
                                                    <span
                                                        class="text-gray-900 dark:text-white">{{ $test->expected_end_date->format('d M Y') }}</span>
                                                </div>
                                            @endif
                                            @if ($test->initial_ph)
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">Initial pH:</span>
                                                    <span
                                                        class="text-gray-900 dark:text-white">{{ $test->initial_ph }}</span>
                                                </div>
                                            @endif
                                            @if ($test->overall_result)
                                                <div>
                                                    <span class="text-gray-500 dark:text-gray-400">Result:</span>
                                                    <span
                                                        class="font-medium
                                                        @if ($test->overall_result == 'Pass') text-green-600 dark:text-green-400
                                                        @else text-red-600 dark:text-red-400 @endif">
                                                        {{ $test->overall_result }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-400">
                                <i class="fas fa-vial text-4xl mb-2"></i>
                                <p>No stability tests yet. Add a test to begin stability monitoring.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Information Tab -->
                    <div x-show="activeTab === 'info'" class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>Formula Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <strong class="text-sm text-gray-700 dark:text-gray-300">Product Type:</strong>
                                <p class="text-gray-900 dark:text-white">{{ ucfirst($formula->product_type) }}</p>
                            </div>
                            @if ($formula->target_ph)
                                <div>
                                    <strong class="text-sm text-gray-700 dark:text-gray-300">Target pH:</strong>
                                    <p class="text-gray-900 dark:text-white">{{ $formula->target_ph }}</p>
                                </div>
                            @endif
                            @if ($formula->actual_ph)
                                <div>
                                    <strong class="text-sm text-gray-700 dark:text-gray-300">Actual pH:</strong>
                                    <p
                                        class="text-gray-900 dark:text-white {{ $formula->isPhWithinRange() ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ $formula->actual_ph }}
                                        @if ($formula->isPhWithinRange())
                                            <span class="text-xs text-green-600 dark:text-green-400"><i
                                                    class="fas fa-check ml-1"></i>Within range</span>
                                        @endif
                                    </p>
                                </div>
                            @endif
                            @if ($formula->shelf_life_months)
                                <div>
                                    <strong class="text-sm text-gray-700 dark:text-gray-300">Shelf Life:</strong>
                                    <p class="text-gray-900 dark:text-white">{{ $formula->shelf_life_months }} months
                                    </p>
                                </div>
                            @endif
                            <div>
                                <strong class="text-sm text-gray-700 dark:text-gray-300">Created By:</strong>
                                <p class="text-gray-900 dark:text-white">{{ $formula->creator->name ?? 'Unknown' }}
                                </p>
                            </div>
                            @if ($formula->approved_by)
                                <div>
                                    <strong class="text-sm text-gray-700 dark:text-gray-300">Approved By:</strong>
                                    <p class="text-gray-900 dark:text-white">
                                        {{ $formula->approver->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $formula->approved_at->format('d M Y H:i') }}</p>
                                </div>
                            @endif
                        </div>
                        @if ($formula->notes)
                            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <strong class="text-sm text-gray-700 dark:text-gray-300">Notes:</strong>
                                <p class="text-gray-900 dark:text-white mt-1">{{ $formula->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Add Test Modal -->
            <div id="add-test-modal"
                class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">
                        <i class="fas fa-vial mr-2 text-green-600"></i>Add Stability Test
                    </h3>
                    <form method="POST" action="{{ route('cosmetic.formulas.stability-test.add', $formula) }}">
                        @csrf
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Test
                                    Type</label>
                                <select name="test_type" required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                                    <option value="accelerated">Accelerated Stability</option>
                                    <option value="real_time">Real-Time Stability</option>
                                    <option value="freeze_thaw">Freeze-Thaw Cycle</option>
                                    <option value="photostability">Photostability</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start
                                    Date</label>
                                <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Storage
                                    Conditions</label>
                                <input type="text" name="storage_conditions"
                                    placeholder="e.g., 40°C ± 2°C / 75% RH" required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white">
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Create Test
                            </button>
                            <button type="button"
                                onclick="document.getElementById('add-test-modal').classList.add('hidden')"
                                class="flex-1 px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-600">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
