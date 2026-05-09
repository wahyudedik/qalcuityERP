<x-app-layout>
    <x-slot name="header">{{ __('Cosmetic Formulas') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('cosmetic.formulas.builder') }}"
            class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition">
            <i class="fas fa-flask mr-2"></i>Formula Builder
        </a>
        <a href="{{ route('cosmetic.formulas.create') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
            <i class="fas fa-plus mr-2"></i>Create Formula
        </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Formulas</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_formulas'] }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">In Testing</div>
                    <div class="mt-2 text-3xl font-bold text-yellow-600">{{ $stats['in_testing'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Approved</div>
                    <div class="mt-2 text-3xl font-bold text-green-600">{{ $stats['approved'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">In Production</div>
                    <div class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['in_production'] }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('cosmetic.formulas.index') }}"
                    class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search by code, name, or brand..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <select name="status"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="testing" {{ request('status') == 'testing' ? 'selected' : '' }}>In Testing
                        </option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved
                        </option>
                        <option value="production" {{ request('status') == 'production' ? 'selected' : '' }}>In
                            Production
                        </option>
                        <option value="discontinued" {{ request('status') == 'discontinued' ? 'selected' : '' }}>
                            Discontinued
                        </option>
                    </select>
                    @if ($productTypes->count() > 0)
                        <select name="product_type"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">All Types</option>
                            @foreach ($productTypes as $type)
                                <option value="{{ $type }}"
                                    {{ request('product_type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst($type) }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button type="submit"
                        class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                </form>
            </div>

            <!-- Formulas Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Formula
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ingredients</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Cost</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($formulas as $formula)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $formula->formula_code }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $formula->formula_name }}</div>
                                        @if ($formula->brand)
                                            <div class="text-xs text-gray-400">{{ $formula->brand }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                            {{ ucfirst($formula->product_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full
                            @if ($formula->status == 'draft') bg-gray-100 text-gray-800
                            @elseif($formula->status == 'testing') bg-yellow-100 text-yellow-800
                            @elseif($formula->status == 'approved') bg-green-100 text-green-800
                            @elseif($formula->status == 'production') bg-blue-100 text-blue-800
                            @else bg-red-100 text-red-800 @endif">
                                            {{ $formula->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $formula->ingredients->count() }} items
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">Rp
                                            {{ number_format($formula->total_cost, 0, ',', '.') }}</div>
                                        @if ($formula->cost_per_unit)
                                            <div class="text-xs text-gray-500">Rp
                                                {{ number_format($formula->cost_per_unit, 0, ',', '.') }}/unit</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $formula->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('cosmetic.formulas.show', $formula) }}"
                                                class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('cosmetic.formulas.builder.edit', $formula->id) }}"
                                                class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST"
                                                action="{{ route('cosmetic.formulas.destroy', $formula) }}"
                                                class="inline"
                                                onsubmit="return confirm('Are you sure you want to delete this formula?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="text-gray-400">
                                            <i class="fas fa-flask text-6xl text-gray-300 mb-4"></i>
                                            <p class="mt-2 text-sm">No formulas found</p>
                                            <a href="{{ route('cosmetic.formulas.create') }}"
                                                class="mt-2 inline-block text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-plus mr-1"></i>Create your first formula
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if ($formulas->hasPages())
                <div class="mt-4">
                    {{ $formulas->links() }}
                </div>
            @endif
        </div>
</x-app-layout>
