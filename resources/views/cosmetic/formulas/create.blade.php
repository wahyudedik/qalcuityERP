<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Create New Formula') }}
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Add a new cosmetic formula with ingredients</p>
            </div>
            <a href="{{ route('cosmetic.formulas.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Formulas
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
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

            <form method="POST" action="{{ route('cosmetic.formulas.store') }}" x-data="{
                ingredients: [{
                    inci_name: '',
                    common_name: '',
                    cas_number: '',
                    product_id: '',
                    quantity: 0,
                    unit: 'g',
                    percentage: null,
                    function: '',
                    phase: '',
                    sort_order: 1
                }],
                addIngredient() {
                    this.ingredients.push({
                        inci_name: '',
                        common_name: '',
                        cas_number: '',
                        product_id: '',
                        quantity: 0,
                        unit: 'g',
                        percentage: null,
                        function: '',
                        phase: '',
                        sort_order: this.ingredients.length + 1
                    });
                },
                removeIngredient(index) {
                    if (this.ingredients.length > 1) {
                        this.ingredients.splice(index, 1);
                        this.ingredients.forEach((ing, i) => ing.sort_order = i + 1);
                    }
                }
            }">
                @csrf

                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>Basic Information
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Formula
                                    Name <span class="text-red-500">*</span></label>
                                <input type="text" name="formula_name" value="{{ old('formula_name') }}" required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('formula_name') border-red-500 @enderror">
                                @error('formula_name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400"><i
                                            class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product
                                    Type <span class="text-red-500">*</span></label>
                                <select name="product_type" required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white @error('product_type') border-red-500 @enderror">
                                    <option value="">Select Type</option>
                                    <option value="cream" {{ old('product_type') == 'cream' ? 'selected' : '' }}>Cream
                                    </option>
                                    <option value="lotion" {{ old('product_type') == 'lotion' ? 'selected' : '' }}>
                                        Lotion
                                    </option>
                                    <option value="serum" {{ old('product_type') == 'serum' ? 'selected' : '' }}>Serum
                                    </option>
                                    <option value="toner" {{ old('product_type') == 'toner' ? 'selected' : '' }}>Toner
                                    </option>
                                    <option value="cleanser" {{ old('product_type') == 'cleanser' ? 'selected' : '' }}>
                                        Cleanser
                                    </option>
                                    <option value="mask" {{ old('product_type') == 'mask' ? 'selected' : '' }}>Mask
                                    </option>
                                    <option value="lipstick" {{ old('product_type') == 'lipstick' ? 'selected' : '' }}>
                                        Lipstick
                                    </option>
                                    <option value="foundation"
                                        {{ old('product_type') == 'foundation' ? 'selected' : '' }}>
                                        Foundation</option>
                                    <option value="shampoo" {{ old('product_type') == 'shampoo' ? 'selected' : '' }}>
                                        Shampoo
                                    </option>
                                    <option value="conditioner"
                                        {{ old('product_type') == 'conditioner' ? 'selected' : '' }}>
                                        Conditioner</option>
                                    <option value="soap" {{ old('product_type') == 'soap' ? 'selected' : '' }}>Soap
                                    </option>
                                    <option value="other" {{ old('product_type') == 'other' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                                @error('product_type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                                <input type="text" name="brand" value="{{ old('brand') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target pH</label>
                                <input type="number" name="target_ph" value="{{ old('target_ph') }}" step="0.01"
                                    min="0" max="14"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Shelf Life (Months)</label>
                                <input type="number" name="shelf_life_months" value="{{ old('shelf_life_months') }}"
                                    min="1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Batch Size *</label>
                                <div class="flex gap-2">
                                    <input type="number" name="batch_size" value="{{ old('batch_size') }}"
                                        step="0.01" min="0" required
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('batch_size') border-red-500 @enderror">
                                    <select name="batch_unit" required
                                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <option value="grams" {{ old('batch_unit') == 'grams' ? 'selected' : '' }}>
                                            Grams
                                        </option>
                                        <option value="ml" {{ old('batch_unit') == 'ml' ? 'selected' : '' }}>
                                            Milliliters
                                        </option>
                                        <option value="units" {{ old('batch_unit') == 'units' ? 'selected' : '' }}>
                                            Units
                                        </option>
                                    </select>
                                </div>
                                @error('batch_size')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                            <textarea name="notes" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <!-- Ingredients -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                <i class="fas fa-flask mr-2 text-purple-600"></i>Ingredients
                            </h2>
                            <button type="button" @click="addIngredient()"
                                class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                                <i class="fas fa-plus mr-1"></i>Add Ingredient
                            </button>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(ingredient, index) in ingredients" :key="index">
                                <div
                                    class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/50">
                                    <div class="flex justify-between items-center mb-3">
                                        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Ingredient
                                            #<span x-text="index + 1"></span></h3>
                                        <button type="button" @click="removeIngredient(index)"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 text-sm"
                                            x-show="ingredients.length > 1">
                                            <i class="fas fa-trash mr-1"></i>Remove
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">INCI
                                                Name <span class="text-red-500">*</span></label>
                                            <input type="text" :name="`ingredients[${index}][inci_name]`"
                                                x-model="ingredient.inci_name" required
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
                                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity
                                                <span class="text-red-500">*</span></label>
                                            <input type="number" :name="`ingredients[${index}][quantity]`"
                                                x-model="ingredient.quantity" step="0.001" min="0" required
                                                class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Unit
                                                <span class="text-red-500">*</span></label>
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
                                                x-model="ingredient.percentage" step="0.01" min="0"
                                                max="100"
                                                class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Function</label>
                                            <select :name="`ingredients[${index}][function]`"
                                                x-model="ingredient.function"
                                                class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                                <option value="">Select Function</option>
                                                <option value="emollient">Emollient</option>
                                                <option value="preservative">Preservative</option>
                                                <option value="active">Active Ingredient</option>
                                                <option value="fragrance">Fragrance</option>
                                                <option value="emulsifier">Emulsifier</option>
                                                <option value="thickener">Thickener</option>
                                                <option value="humectant">Humectant</option>
                                                <option value="surfactant">Surfactant</option>
                                                <option value="colorant">Colorant</option>
                                                <option value="solvent">Solvent</option>
                                                <option value="ph_adjuster">pH Adjuster</option>
                                                <option value="antioxidant">Antioxidant</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Phase</label>
                                            <select :name="`ingredients[${index}][phase]`" x-model="ingredient.phase"
                                                class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                                <option value="">Select Phase</option>
                                                <option value="oil_phase">Oil Phase</option>
                                                <option value="water_phase">Water Phase</option>
                                                <option value="cool_down_phase">Cool Down Phase</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Mixing
                                                Order</label>
                                            <input type="number" :name="`ingredients[${index}][sort_order]`"
                                                x-model="ingredient.sort_order" min="1"
                                                class="w-full px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-4">
                        <button type="submit"
                            class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                            <i class="fas fa-save mr-2"></i>Create Formula
                        </button>
                        <a href="{{ route('cosmetic.formulas.index') }}"
                            class="px-6 py-3 bg-gray-300 dark:bg-gray-700 hover:bg-gray-400 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg text-center transition">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
</x-app-layout>
