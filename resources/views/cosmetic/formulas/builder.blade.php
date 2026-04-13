<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    <i class="fas fa-flask mr-2 text-purple-600"></i>{{ $formula ? 'Edit Formula' : 'Formula Builder' }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ $formula ? 'Edit formula: ' . $formula->formula_name : 'Create new cosmetic formula with advanced builder' }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('cosmetic.formulas.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Formulas
                </a>
                @if ($formula)
                    <a href="{{ route('cosmetic.formulas.show', $formula->id) }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        <i class="fas fa-eye mr-2"></i>View Details
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="formulaBuilder()" x-init="init()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if (session('success'))
                <div
                    class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div
                    class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                </div>
            @endif

            <form method="POST"
                action="{{ $formula ? route('cosmetic.formulas.store') : route('cosmetic.formulas.store') }}">
                @csrf
                @if ($formula)
                    <input type="hidden" name="formula_id" value="{{ $formula->id }}">
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Formula Info & Ingredients -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                <i class="fas fa-info-circle mr-2 text-blue-600"></i>Basic Information
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Formula Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="formula_name" x-model="formula.formula_name" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('formula_name') border-red-500 @enderror"
                                        placeholder="e.g., Hydrating Face Cream">
                                    @error('formula_name')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400"><i
                                                class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Product Type <span class="text-red-500">*</span>
                                    </label>
                                    <select name="product_type" x-model="formula.product_type" required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">Select Type</option>
                                        <option value="cream">Cream</option>
                                        <option value="lotion">Lotion</option>
                                        <option value="serum">Serum</option>
                                        <option value="toner">Toner</option>
                                        <option value="cleanser">Cleanser</option>
                                        <option value="mask">Mask</option>
                                        <option value="lipstick">Lipstick</option>
                                        <option value="foundation">Foundation</option>
                                        <option value="shampoo">Shampoo</option>
                                        <option value="conditioner">Conditioner</option>
                                        <option value="soap">Soap</option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Brand</label>
                                    <input type="text" name="brand" x-model="formula.brand"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="e.g., SkinCare Pro">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Target pH
                                    </label>
                                    <input type="number" name="target_ph" x-model="formula.target_ph" step="0.1"
                                        min="0" max="14"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="e.g., 5.5">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Shelf Life (months)
                                    </label>
                                    <input type="number" name="shelf_life_months" x-model="formula.shelf_life_months"
                                        min="1"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                        placeholder="e.g., 24">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Batch Size <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex gap-2">
                                        <input type="number" name="batch_size" x-model="formula.batch_size"
                                            step="0.01" min="0" required
                                            class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                            placeholder="1000">
                                        <select name="batch_unit" x-model="formula.batch_unit" required
                                            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                            <option value="grams">Grams</option>
                                            <option value="ml">Milliliters</option>
                                            <option value="units">Units</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                                <textarea name="notes" x-model="formula.notes" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                    placeholder="Additional notes about the formula..."></textarea>
                            </div>
                        </div>

                        <!-- Ingredients Builder -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    <i class="fas fa-flask mr-2 text-purple-600"></i>Ingredients
                                </h3>
                                <button type="button" @click="addIngredient()"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                                    <i class="fas fa-plus mr-2"></i>Add Ingredient
                                </button>
                            </div>

                            <!-- Ingredient List -->
                            <div class="space-y-3">
                                <template x-for="(ingredient, index) in ingredients" :key="index">
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50"
                                        :class="{ 'border-red-500': ingredient.errors && ingredient.errors.length > 0 }">
                                        <!-- Header -->
                                        <div class="flex justify-between items-center mb-3">
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white text-sm font-bold rounded-full"
                                                    x-text="index + 1"></span>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300"
                                                    x-text="ingredient.inci_name || 'New Ingredient'"></span>
                                            </div>
                                            <button type="button" @click="removeIngredient(index)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 text-sm">
                                                <i class="fas fa-trash mr-1"></i>Remove
                                            </button>
                                        </div>

                                        <!-- Search Ingredient -->
                                        <div class="mb-3 relative">
                                            <label
                                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Search Raw Material
                                            </label>
                                            <input type="text" x-model="ingredient.searchQuery"
                                                @input="searchRawMaterial(index)" @focus="showSearchResults = index"
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm"
                                                placeholder="Type to search...">

                                            <!-- Search Results Dropdown -->
                                            <div x-show="showSearchResults === index && ingredient.searchResults.length > 0"
                                                @click.away="showSearchResults = null"
                                                class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                                <template x-for="material in ingredient.searchResults"
                                                    :key="material.id">
                                                    <button type="button" @click="selectRawMaterial(index, material)"
                                                        class="w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-white"
                                                            x-text="material.name"></div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                            SKU: <span x-text="material.sku"></span> |
                                                            Cost: Rp <span
                                                                x-text="formatNumber(material.average_cost)"></span>/kg
                                                            |
                                                            Stock: <span x-text="material.stock_quantity"></span> <span
                                                                x-text="material.unit"></span>
                                                        </div>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Ingredient Details Grid -->
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    INCI Name <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" :name="`ingredients[${index}][inci_name]`"
                                                    x-model="ingredient.inci_name" @blur="validateIngredient(index)"
                                                    required
                                                    class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Common
                                                    Name</label>
                                                <input type="text" :name="`ingredients[${index}][common_name]`"
                                                    x-model="ingredient.common_name"
                                                    class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">CAS
                                                    Number</label>
                                                <input type="text" :name="`ingredients[${index}][cas_number]`"
                                                    x-model="ingredient.cas_number"
                                                    class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Quantity <span class="text-red-500">*</span>
                                                </label>
                                                <input type="number" :name="`ingredients[${index}][quantity]`"
                                                    x-model.number="ingredient.quantity" @input="calculateTotals()"
                                                    step="0.001" min="0" required
                                                    class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Unit <span class="text-red-500">*</span>
                                                </label>
                                                <select :name="`ingredients[${index}][unit]`" x-model="ingredient.unit"
                                                    required
                                                    class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                                    <option value="g">Grams (g)</option>
                                                    <option value="ml">Milliliters (ml)</option>
                                                    <option value="%">Percentage (%)</option>
                                                    <option value="drops">Drops</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Percentage</label>
                                                <input type="number" :name="`ingredients[${index}][percentage]`"
                                                    x-model.number="ingredient.percentage" @input="calculateTotals()"
                                                    step="0.01" min="0" max="100"
                                                    class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                                    :class="{ 'border-yellow-500': ingredient.warnings && ingredient.warnings
                                                            .length > 0 }">
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Function</label>
                                                <select :name="`ingredients[${index}][function]`"
                                                    x-model="ingredient.function"
                                                    class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                                    <option value="">Select Function</option>
                                                    @foreach ($ingredientFunctions as $key => $label)
                                                        <option value="{{ $key }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Phase</label>
                                                <select :name="`ingredients[${index}][phase]`"
                                                    x-model="ingredient.phase"
                                                    class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                                    <option value="">Select Phase</option>
                                                    @foreach ($phases as $key => $label)
                                                        <option value="{{ $key }}">{{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label
                                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Mixing
                                                    Order</label>
                                                <input type="number" :name="`ingredients[${index}][sort_order]`"
                                                    x-model.number="ingredient.sort_order" min="1"
                                                    class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                            </div>
                                        </div>

                                        <!-- Validation Messages -->
                                        <div x-show="ingredient.errors && ingredient.errors.length > 0"
                                            class="mt-2">
                                            <template x-for="error in ingredient.errors" :key="error.message">
                                                <div class="text-xs text-red-600 dark:text-red-400 mt-1">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    <span x-text="error.message"></span>
                                                </div>
                                            </template>
                                        </div>

                                        <div x-show="ingredient.warnings && ingredient.warnings.length > 0"
                                            class="mt-2">
                                            <template x-for="warning in ingredient.warnings" :key="warning.message">
                                                <div class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                                    <span x-text="warning.message"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Empty State -->
                            <div x-show="ingredients.length === 0" class="text-center py-8 text-gray-400">
                                <i class="fas fa-flask text-4xl mb-2"></i>
                                <p>No ingredients added yet. Click "Add Ingredient" to start building your formula.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Live Preview & Calculations -->
                    <div class="space-y-6">
                        <!-- Formula Summary -->
                        <div
                            class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 sticky top-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                <i class="fas fa-chart-pie mr-2 text-green-600"></i>Formula Summary
                            </h3>

                            <div class="space-y-4">
                                <!-- Total Quantity -->
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <div class="text-sm text-blue-600 dark:text-blue-400">Total Quantity</div>
                                    <div class="text-2xl font-bold text-blue-900 dark:text-blue-300">
                                        <span x-text="formatNumber(totals.total_quantity)"></span>
                                        <span class="text-sm" x-text="formula.batch_unit"></span>
                                    </div>
                                </div>

                                <!-- Total Percentage -->
                                <div class="p-3 rounded-lg"
                                    :class="totals.percentage_valid ? 'bg-green-50 dark:bg-green-900/20' :
                                        'bg-red-50 dark:bg-red-900/20'">
                                    <div class="text-sm"
                                        :class="totals.percentage_valid ? 'text-green-600 dark:text-green-400' :
                                            'text-red-600 dark:text-red-400'">
                                        Total Percentage
                                    </div>
                                    <div class="text-2xl font-bold"
                                        :class="totals.percentage_valid ? 'text-green-900 dark:text-green-300' :
                                            'text-red-900 dark:text-red-300'">
                                        <span x-text="formatNumber(totals.total_percentage)"></span>%
                                    </div>
                                    <div x-show="!totals.percentage_valid"
                                        class="text-xs text-red-600 dark:text-red-400 mt-1">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                        Should be 100%
                                    </div>
                                </div>

                                <!-- Total Cost -->
                                <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                    <div class="text-sm text-green-600 dark:text-green-400">Total Cost</div>
                                    <div class="text-2xl font-bold text-green-900 dark:text-green-300">
                                        Rp <span x-text="formatNumber(totals.total_cost)"></span>
                                    </div>
                                </div>

                                <!-- Cost Per Unit -->
                                <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                    <div class="text-sm text-purple-600 dark:text-purple-400">Cost per kg</div>
                                    <div class="text-2xl font-bold text-purple-900 dark:text-purple-300">
                                        Rp <span x-text="formatNumber(totals.cost_per_unit)"></span>
                                    </div>
                                </div>

                                <!-- Ingredient Count -->
                                <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Ingredients</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                        <span x-text="ingredients.length"></span> items
                                    </div>
                                </div>
                            </div>

                            <!-- Validation Status -->
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Validation
                                    Status</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center text-sm"
                                        :class="formula.formula_name ? 'text-green-600 dark:text-green-400' : 'text-gray-400'">
                                        <i class="fas"
                                            :class="formula.formula_name ? 'fa-check-circle' : 'fa-circle'"></i>
                                        <span class="ml-2">Formula name</span>
                                    </div>
                                    <div class="flex items-center text-sm"
                                        :class="formula.product_type ? 'text-green-600 dark:text-green-400' : 'text-gray-400'">
                                        <i class="fas"
                                            :class="formula.product_type ? 'fa-check-circle' : 'fa-circle'"></i>
                                        <span class="ml-2">Product type</span>
                                    </div>
                                    <div class="flex items-center text-sm"
                                        :class="ingredients.length > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400'">
                                        <i class="fas"
                                            :class="ingredients.length > 0 ? 'fa-check-circle' : 'fa-circle'"></i>
                                        <span class="ml-2">At least 1 ingredient</span>
                                    </div>
                                    <div class="flex items-center text-sm"
                                        :class="totals.percentage_valid ? 'text-green-600 dark:text-green-400' : 'text-gray-400'">
                                        <i class="fas"
                                            :class="totals.percentage_valid ? 'fa-check-circle' : 'fa-circle'"></i>
                                        <span class="ml-2">Percentage = 100%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-6 space-y-3">
                                <button type="submit" :disabled="!canSubmit()"
                                    class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium rounded-lg transition">
                                    <i class="fas fa-save mr-2"></i>
                                    <span x-text="$wire.formula ? 'Update Formula' : 'Create Formula'"></span>
                                </button>
                                <a href="{{ route('cosmetic.formulas.index') }}"
                                    class="block w-full px-6 py-3 bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg text-center transition">
                                    <i class="fas fa-times mr-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function formulaBuilder() {
            return {
                formula: {
                    formula_name: '{{ $formula ? $formula->formula_name : '' }}',
                    product_type: '{{ $formula ? $formula->product_type : '' }}',
                    brand: '{{ $formula ? $formula->brand : '' }}',
                    target_ph: {{ $formula ? $formula->target_ph : 'null' }},
                    shelf_life_months: {{ $formula ? $formula->shelf_life_months : 'null' }},
                    batch_size: {{ $formula ? $formula->batch_size : 1000 }},
                    batch_unit: '{{ $formula ? $formula->batch_unit : 'grams' }}',
                    notes: '{{ $formula ? $formula->notes : '' }}'
                },
                ingredients: [],
                totals: {
                    total_quantity: 0,
                    total_percentage: 0,
                    total_cost: 0,
                    cost_per_unit: 0,
                    percentage_valid: false
                },
                showSearchResults: null,
                searchTimeout: null,

                init() {
                    // Load existing ingredients if editing
                    @if ($formula && $ingredients->count() > 0)
                        @foreach ($ingredients as $ingredient)
                            this.ingredients.push({
                                product_id: {{ $ingredient->product_id ?? 'null' }},
                                inci_name: '{{ $ingredient->inci_name }}',
                                common_name: '{{ $ingredient->common_name ?? '' }}',
                                cas_number: '{{ $ingredient->cas_number ?? '' }}',
                                quantity: {{ $ingredient->quantity }},
                                unit: '{{ $ingredient->unit }}',
                                percentage: {{ $ingredient->percentage ?? 'null' }},
                                function: '{{ $ingredient->function ?? '' }}',
                                phase: '{{ $ingredient->phase ?? '' }}',
                                sort_order: {{ $ingredient->sort_order }},
                                searchQuery: '',
                                searchResults: [],
                                errors: [],
                                warnings: []
                            });
                        @endforeach
                    @else
                        // Add first empty ingredient
                        this.addIngredient();
                    @endif

                    this.calculateTotals();
                },

                addIngredient() {
                    this.ingredients.push({
                        product_id: null,
                        inci_name: '',
                        common_name: '',
                        cas_number: '',
                        quantity: 0,
                        unit: 'g',
                        percentage: null,
                        function: '',
                        phase: '',
                        sort_order: this.ingredients.length + 1,
                        searchQuery: '',
                        searchResults: [],
                        errors: [],
                        warnings: []
                    });
                },

                removeIngredient(index) {
                    if (this.ingredients.length > 1) {
                        this.ingredients.splice(index, 1);
                        this.ingredients.forEach((ing, i) => ing.sort_order = i + 1);
                        this.calculateTotals();
                    }
                },

                async searchRawMaterial(index) {
                    const query = this.ingredients[index].searchQuery;
                    if (query.length < 2) {
                        this.ingredients[index].searchResults = [];
                        return;
                    }

                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(async () => {
                        try {
                            const response = await fetch(
                            '{{ route('cosmetic.formulas.builder.search') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    q: query
                                })
                            });
                            const data = await response.json();
                            this.ingredients[index].searchResults = data;
                        } catch (error) {
                            console.error('Search error:', error);
                        }
                    }, 300);
                },

                selectRawMaterial(index, material) {
                    const ingredient = this.ingredients[index];
                    ingredient.product_id = material.id;
                    ingredient.inci_name = material.name;
                    ingredient.searchQuery = material.name;
                    ingredient.searchResults = [];
                    this.showSearchResults = null;
                },

                async validateIngredient(index) {
                    const ingredient = this.ingredients[index];
                    if (!ingredient.inci_name || !ingredient.percentage) {
                        ingredient.errors = [];
                        ingredient.warnings = [];
                        return;
                    }

                    try {
                        const response = await fetch('{{ route('cosmetic.formulas.builder.validate') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                inci_name: ingredient.inci_name,
                                percentage: ingredient.percentage,
                                function: ingredient.function
                            })
                        });
                        const data = await response.json();
                        ingredient.errors = data.errors || [];
                        ingredient.warnings = data.warnings || [];
                    } catch (error) {
                        console.error('Validation error:', error);
                    }
                },

                async calculateTotals() {
                    try {
                        const response = await fetch('{{ route('cosmetic.formulas.builder.calculate') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                ingredients: this.ingredients.map(ing => ({
                                    product_id: ing.product_id,
                                    quantity: ing.quantity,
                                    percentage: ing.percentage
                                })),
                                batch_size: this.formula.batch_size
                            })
                        });
                        const data = await response.json();
                        this.totals = data;
                    } catch (error) {
                        console.error('Calculation error:', error);
                    }
                },

                canSubmit() {
                    return this.formula.formula_name &&
                        this.formula.product_type &&
                        this.ingredients.length > 0 &&
                        this.totals.percentage_valid;
                },

                formatNumber(number) {
                    return new Intl.NumberFormat('id-ID').format(number || 0);
                }
            }
        }
    </script>
</x-app-layout>
