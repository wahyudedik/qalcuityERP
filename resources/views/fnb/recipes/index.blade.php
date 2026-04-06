@extends('layouts.app')
@section('title', 'Recipe Cost Calculator')
@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Recipe Cost Calculator</h1>
                <p class="mt-1 text-sm text-gray-600">Real-time recipe costing and profit analysis</p>
            </div>
            <a href="{{ route('fnb.recipes.low-margin') }}"
                class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                View Low Margin Recipes
            </a>
        </div>

        @if ($lowMarginRecipes->isNotEmpty())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-red-700 font-medium">{{ $lowMarginRecipes->count() }} recipes have low profit margins
                        (<30%)< /span>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($recipes as $recipe)
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="font-bold text-lg">{{ $recipe->name }}</h3>
                        <span
                            class="px-2 py-1 text-xs rounded-full {{ $recipe->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $recipe->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-600 mb-2">{{ $recipe->menuItem?->name ?? 'No menu item linked' }}</div>

                    <div class="space-y-1 text-sm mb-3">
                        <div class="flex justify-between">
                            <span>Yield:</span>
                            <span class="font-medium">{{ $recipe->yield_quantity }} {{ $recipe->yield_unit }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Prep Time:</span>
                            <span>{{ $recipe->preparation_time_minutes ?? '-' }} min</span>
                        </div>
                    </div>

                    <a href="{{ route('fnb.recipes.calculate', $recipe) }}"
                        class="block w-full text-center bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Calculate Cost
                    </a>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-gray-500">
                    No recipes found. Create your first recipe to start calculating costs.
                </div>
            @endforelse
        </div>
    </div>
@endsection
