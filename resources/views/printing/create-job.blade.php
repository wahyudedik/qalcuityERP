<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('printing.dashboard') }}"
                    class="text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-white transition text-sm">
                    ← Back
                </a>
                <span class="text-gray-300 dark:text-slate-600">|</span>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Create New Print Job</h1>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('printing.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Basic Information --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Job Name
                            *</label>
                        <input type="text" name="job_name" required
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., Business Cards - ABC Company">
                        @error('job_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Customer</label>
                        <select name="customer_id"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select Customer (Optional)</option>
                            @foreach (\App\Models\Customer::where('tenant_id', auth()->user()->tenant_id)->get() as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Product Type
                            *</label>
                        <select name="product_type" required
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select Product Type</option>
                            <option value="business_cards">Business Cards</option>
                            <option value="flyers">Flyers</option>
                            <option value="brochures">Brochures</option>
                            <option value="posters">Posters</option>
                            <option value="banners">Banners</option>
                            <option value="catalogs">Catalogs</option>
                            <option value="magazines">Magazines</option>
                            <option value="books">Books</option>
                            <option value="packaging">Packaging</option>
                            <option value="labels">Labels</option>
                            <option value="other">Other</option>
                        </select>
                        @error('product_type')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Quantity
                            *</label>
                        <input type="number" name="quantity" required min="1"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="e.g., 1000">
                        @error('quantity')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Priority</label>
                        <select name="priority"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="normal" selected>Normal</option>
                            <option value="low">Low</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Due Date</label>
                        <input type="date" name="due_date"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>
            </div>

            {{-- Specifications --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Specifications</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Paper
                            Type</label>
                        <select name="paper_type"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select Paper Type</option>
                            <option value="art_paper_120gsm">Art Paper 120 gsm</option>
                            <option value="art_paper_150gsm">Art Paper 150 gsm</option>
                            <option value="art_paper_210gsm">Art Paper 210 gsm</option>
                            <option value="art_carton_260gsm">Art Carton 260 gsm</option>
                            <option value="art_carton_310gsm">Art Carton 310 gsm</option>
                            <option value="hvs_70gsm">HVS 70 gsm</option>
                            <option value="hvs_80gsm">HVS 80 gsm</option>
                            <option value="bookpaper_52gsm">Bookpaper 52 gsm</option>
                            <option value="bookpaper_57gsm">Bookpaper 57 gsm</option>
                            <option value="ivory_210gsm">Ivory 210 gsm</option>
                            <option value="ivory_230gsm">Ivory 230 gsm</option>
                            <option value="ivory_250gsm">Ivory 250 gsm</option>
                            <option value="sticker_paper">Sticker Paper</option>
                            <option value="synthetic_paper">Synthetic Paper</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Paper
                            Size</label>
                        <select name="paper_size"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select Size</option>
                            <option value="A4">A4 (210 x 297 mm)</option>
                            <option value="A3">A3 (297 x 420 mm)</option>
                            <option value="A5">A5 (148 x 210 mm)</option>
                            <option value="F4">F4 (215 x 330 mm)</option>
                            <option value="Letter">Letter (216 x 279 mm)</option>
                            <option value="Legal">Legal (216 x 356 mm)</option>
                            <option value="Custom">Custom Size</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Colors
                            (Front)</label>
                        <select name="colors_front"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="0">0 (Black & White)</option>
                            <option value="1">1 Color</option>
                            <option value="2">2 Colors</option>
                            <option value="4" selected>4 Colors (CMYK)</option>
                            <option value="5">5 Colors (CMYK + Spot)</option>
                            <option value="6">6 Colors</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Colors
                            (Back)</label>
                        <select name="colors_back"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="0" selected>0 (No Print)</option>
                            <option value="1">1 Color</option>
                            <option value="2">2 Colors</option>
                            <option value="4">4 Colors (CMYK)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Finishing
                            Options</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" name="finishing[]" value="lamination"
                                    class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700 dark:text-slate-300">Lamination</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="finishing[]" value="binding"
                                    class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700 dark:text-slate-300">Binding</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="finishing[]" value="cutting"
                                    class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700 dark:text-slate-300">Cutting</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="finishing[]" value="folding"
                                    class="mr-2 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700 dark:text-slate-300">Folding</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Special
                            Instructions</label>
                        <textarea name="special_instructions" rows="4"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Any special requirements or notes..."></textarea>
                    </div>
                </div>
            </div>

            {{-- Pricing (Optional) --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pricing (Optional)</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Quoted
                            Price</label>
                        <input type="number" name="quoted_price" step="0.01"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="0.00">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">Estimated
                            Cost</label>
                        <input type="number" name="estimated_cost" step="0.01"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="0.00">
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('printing.dashboard') }}"
                    class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition text-sm font-medium">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
                    Create Print Job
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
