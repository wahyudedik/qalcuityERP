<x-app-layout>
    <x-slot name="header"><i class="fas fa-certificate mr-2 text-blue-600"></i>BPOM Registration Dashboard</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('cosmetic.bpom.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                <i class="fas fa-plus mr-2"></i>New Registration
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Registrations</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Pending/Submitted</div>
                    <div class="mt-2 text-3xl font-bold text-yellow-600">{{ $stats['pending'] + $stats['submitted'] }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Approved</div>
                    <div class="mt-2 text-3xl font-bold text-green-600">{{ $stats['approved'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Expiring Soon</div>
                    <div class="mt-2 text-3xl font-bold text-red-600">{{ $stats['expiring_soon'] }}</div>
                </div>
            </div>

            <!-- Alerts -->
            @if ($expiringInfo['expiring_count'] > 0 || $expiringInfo['expired_count'] > 0)
                <div class="space-y-4">
                    @if ($expiringInfo['expiring_count'] > 0)
                        <div
                            class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i
                                    class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                                <div>
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        {{ $expiringInfo['expiring_count'] }} Registration(s) Expiring Within 90 Days
                                    </h3>
                                    <p class="mt-1 text-sm text-yellow-700">
                                        Please review and renew registrations before expiry
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($expiringInfo['expired_count'] > 0)
                        <div
                            class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-times-circle text-red-600 mt-1 mr-3"></i>
                                <div>
                                    <h3 class="text-sm font-medium text-red-800">
                                        {{ $expiringInfo['expired_count'] }} Registration(s) Expired
                                    </h3>
                                    <p class="mt-1 text-sm text-red-700">
                                        These products cannot be sold legally until renewed
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="GET" action="{{ route('cosmetic.bpom.dashboard') }}"
                    class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search by registration number or product name..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <select name="status"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted
                        </option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved
                        </option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected
                        </option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                    @if ($categories->count() > 0)
                        <select name="category"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}"
                                    {{ request('category') == $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                </form>
            </div>

            <!-- Registrations Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Reg. Number</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Product</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Category</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Submitted</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Expiry</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($registrations as $reg)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-blue-600">
                                            {{ $reg->registration_number }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $reg->registration_type }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $reg->product_name }}</div>
                                        @if ($reg->formula)
                                            <div class="text-xs text-gray-500">
                                                {{ $reg->formula->formula_code }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="text-sm text-gray-600">{{ ucfirst($reg->product_category) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full
                                        @if ($reg->status == 'pending') bg-gray-100 text-gray-800
                                        @elseif($reg->status == 'submitted') bg-yellow-100 text-yellow-800
                                        @elseif($reg->status == 'approved') bg-green-100 text-green-800
                                        @elseif($reg->status == 'rejected') bg-red-100 text-red-800
                                        @else bg-orange-100 text-orange-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $reg->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-600">
                                            {{ $reg->submission_date ? $reg->submission_date->format('d M Y') : '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div
                                            class="text-sm
                                        @if ($reg->expiry_date && $reg->expiry_date->lt(now())) text-red-600 font-bold
                                        @elseif($reg->expiry_date && $reg->expiry_date->lt(now()->addDays(90))) text-yellow-600
                                        @else text-gray-600 @endif">
                                            {{ $reg->expiry_date ? $reg->expiry_date->format('d M Y') : '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="{{ route('cosmetic.bpom.show', $reg) }}"
                                            class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if ($reg->status == 'pending')
                                            <form method="POST" action="{{ route('cosmetic.bpom.submit', $reg) }}"
                                                class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="text-green-600 hover:text-green-900"
                                                    onclick="return confirm('Submit this registration to BPOM?')">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-certificate text-4xl mb-2"></i>
                                        <p>No registrations found. Create your first BPOM registration.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if ($registrations->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $registrations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
