@extends('layouts.app')

@section('title', 'Table Management')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Table Management</h1>
                <p class="mt-1 text-sm text-gray-600">Manage restaurant tables and reservations</p>
            </div>
            <button onclick="document.getElementById('reservationModal').classList.remove('hidden')"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                + New Reservation
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm text-gray-500">Total Tables</div>
                <div class="text-2xl font-bold">{{ $stats['total_tables'] }}</div>
            </div>
            <div class="bg-green-50 rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="text-sm text-green-600">Available</div>
                <div class="text-2xl font-bold text-green-700">{{ $stats['available'] }}</div>
            </div>
            <div class="bg-red-50 rounded-lg shadow p-4 border-l-4 border-red-500">
                <div class="text-sm text-red-600">Occupied</div>
                <div class="text-2xl font-bold text-red-700">{{ $stats['occupied'] }}</div>
            </div>
            <div class="bg-blue-50 rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="text-sm text-blue-600">Today's Reservations</div>
                <div class="text-2xl font-bold text-blue-700">{{ $stats['today_reservations'] }}</div>
            </div>
        </div>

        <!-- Tables Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach ($tables as $table)
                <div
                    class="bg-white rounded-lg shadow p-4 border-2 {{ $table->status === 'available' ? 'border-green-400' : ($table->status === 'occupied' ? 'border-red-400' : 'border-yellow-400') }}">
                    <div class="flex justify-between items-start mb-2">
                        <div class="text-lg font-bold">Table {{ $table->table_number }}</div>
                        <span
                            class="px-2 py-1 text-xs rounded-full {{ $table->status === 'available' ? 'bg-green-100 text-green-800' : ($table->status === 'occupied' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($table->status) }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-600 mb-2">Capacity: {{ $table->capacity }} persons</div>
                    <div class="text-xs text-gray-500">{{ $table->location ?? 'Main Area' }}</div>

                    @if ($table->reservations->isNotEmpty())
                        <div class="mt-2 pt-2 border-t">
                            <div class="text-xs font-semibold text-blue-600">
                                {{ $table->reservations->first()->customer_name }} -
                                {{ $table->reservations->first()->reservation_time }}
                            </div>
                        </div>
                    @endif

                    <a href="{{ route('fnb.tables.reservations', $table) }}"
                        class="mt-2 block text-center text-xs text-blue-600 hover:text-blue-800">
                        View Reservations →
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <!-- New Reservation Modal -->
    <div id="reservationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h2 class="text-xl font-bold mb-4">New Reservation</h2>
            <form action="{{ route('fnb.tables.reservations.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Table</label>
                        <select name="table_id" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @foreach ($tables->where('status', 'available') as $table)
                                <option value="{{ $table->id }}">Table {{ $table->table_number }}
                                    ({{ $table->capacity }} pax)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Customer Name</label>
                        <input type="text" name="customer_name" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="tel" name="customer_phone" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="reservation_date" required value="{{ today()->format('Y-m-d') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Time</label>
                            <input type="time" name="reservation_time" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Party Size</label>
                            <input type="number" name="party_size" required min="1"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Duration (min)</label>
                            <input type="number" name="duration_minutes" required value="120" min="30"
                                max="300" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('reservationModal').classList.add('hidden')"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create
                        Reservation</button>
                </div>
            </form>
        </div>
    </div>
@endsection
