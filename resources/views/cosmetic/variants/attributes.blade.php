@extends('layouts.app')

@section('title', 'Variant Attributes')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('cosmetic.variants.index') }}" class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                ← Back to Variants
            </a>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Variant Attributes</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage variant attribute templates (shades, sizes, etc.)</p>
                </div>
                <button onclick="document.getElementById('add-attribute-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Attribute Template
                </button>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Attributes by Category -->
        <div class="space-y-6">
            <!-- Shade/Color Attributes -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">🎨 Shades & Colors</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @forelse($attributes->where('attribute_type', 'shade') as $attr)
                            <div
                                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div class="flex items-center gap-2">
                                    @if ($attr->hex_code)
                                        <div class="w-6 h-6 rounded border border-gray-300"
                                            style="background-color: {{ $attr->hex_code }}"></div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $attr->attribute_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $attr->usage_count }} variants</div>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('cosmetic.variants.attributes.destroy', $attr->id) }}"
                                    onsubmit="return confirm('Delete this attribute?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="col-span-4 text-center text-gray-500 py-4">No shade attributes defined</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Size Attributes -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">📏 Sizes</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @forelse($attributes->where('attribute_type', 'size') as $attr)
                            <div
                                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $attr->attribute_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $attr->usage_count }} variants</div>
                                </div>
                                <form method="POST" action="{{ route('cosmetic.variants.attributes.destroy', $attr->id) }}"
                                    onsubmit="return confirm('Delete this attribute?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="col-span-4 text-center text-gray-500 py-4">No size attributes defined</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Fragrance Attributes -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">🌸 Fragrances</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @forelse($attributes->where('attribute_type', 'fragrance') as $attr)
                            <div
                                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $attr->attribute_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $attr->usage_count }} variants</div>
                                </div>
                                <form method="POST"
                                    action="{{ route('cosmetic.variants.attributes.destroy', $attr->id) }}"
                                    onsubmit="return confirm('Delete this attribute?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="col-span-4 text-center text-gray-500 py-4">No fragrance attributes defined</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Packaging Attributes -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">📦 Packaging</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @forelse($attributes->where('attribute_type', 'packaging') as $attr)
                            <div
                                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $attr->attribute_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $attr->usage_count }} variants</div>
                                </div>
                                <form method="POST"
                                    action="{{ route('cosmetic.variants.attributes.destroy', $attr->id) }}"
                                    onsubmit="return confirm('Delete this attribute?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="col-span-4 text-center text-gray-500 py-4">No packaging attributes defined</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Attribute Modal -->
    <div id="add-attribute-modal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Attribute Template</h3>
                <button onclick="document.getElementById('add-attribute-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('cosmetic.variants.attributes.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attribute Type *</label>
                    <select name="attribute_type" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Type</option>
                        <option value="shade">Shade/Color</option>
                        <option value="size">Size</option>
                        <option value="fragrance">Fragrance</option>
                        <option value="packaging">Packaging</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attribute Name *</label>
                    <input type="text" name="attribute_name" required placeholder="e.g., Ruby Red, 50ml, Rose, Tube"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hex Code (for colors)</label>
                    <div class="flex gap-2">
                        <input type="color" id="color-picker" class="h-10 w-20 rounded border-gray-300"
                            onchange="document.getElementById('hex-input').value = this.value">
                        <input type="text" id="hex-input" name="hex_code" placeholder="#FF0000"
                            class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="button"
                        onclick="document.getElementById('add-attribute-modal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                        Add Attribute
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
