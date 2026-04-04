@extends('layouts.app')

@section('title', 'Menu Management')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Menu Management</h1>
                <p class="text-gray-600">Manage restaurant menus and categories</p>
            </div>
            <button onclick="document.getElementById('createModal').classList.remove('hidden')"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + New Menu
            </button>
        </div>

        <!-- Menus Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($menus as $menu)
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-semibold text-lg">{{ $menu->name }}</h3>
                                <span class="inline-block mt-1 px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">
                                    {{ ucfirst(str_replace('_', ' ', $menu->type)) }}
                                </span>
                            </div>
                            <span
                                class="px-2 py-1 text-xs rounded {{ $menu->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $menu->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        @if ($menu->description)
                            <p class="text-sm text-gray-600 mb-4">{{ Str::limit($menu->description, 100) }}</p>
                        @endif

                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            @if ($menu->available_from && $menu->available_until)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $menu->available_from }} - {{ $menu->available_until }}
                                </div>
                            @endif
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                {{ $menu->items_count }} items
                            </div>
                        </div>

                        <div class="flex space-x-2">
                            <a href="{{ route('hotel.fb.menus.items', $menu) }}"
                                class="flex-1 text-center px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                                View Items
                            </a>
                            <button onclick="editMenu({{ $menu->id }})"
                                class="px-3 py-2 border rounded hover:bg-gray-50 text-sm">
                                Edit
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500">No menus created yet. Create your first menu to get started.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Modal -->
    <div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white rounded-lg shadow-lg max-w-lg w-full">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Create Menu</h3>
                    <button onclick="document.getElementById('createModal').classList.add('hidden')"
                        class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('hotel.fb.menus.store') }}" method="POST" class="p-6">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Menu Name</label>
                            <input type="text" name="name" required class="w-full border rounded px-3 py-2"
                                placeholder="e.g., Breakfast Menu">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full border rounded px-3 py-2"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Menu Type</label>
                            <select name="type" required class="w-full border rounded px-3 py-2">
                                <option value="breakfast">Breakfast</option>
                                <option value="lunch">Lunch</option>
                                <option value="dinner">Dinner</option>
                                <option value="all_day">All Day</option>
                                <option value="room_service">Room Service</option>
                                <option value="bar">Bar</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Available From</label>
                                <input type="time" name="available_from" class="w-full border rounded px-3 py-2">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Available Until</label>
                                <input type="time" name="available_until" class="w-full border rounded px-3 py-2">
                            </div>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" checked class="mr-2">
                                <span class="text-sm">Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')"
                            class="px-4 py-2 border rounded hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create
                            Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
