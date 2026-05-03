<x-app-layout>
    <x-slot name="header">{{ __('Notification Rules') }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.notification-rules.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                    class="fas fa-plus mr-2"></i>New Rule</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><i
                                class="fas fa-bell text-blue-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Rules</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['total_rules'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3"><i
                                class="fas fa-check-circle text-green-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Active Rules</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['active_rules'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3"><i
                                class="fas fa-paper-plane text-purple-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Notifications Today</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['notifications_today'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Notification Rules</h3>
                </div>
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rule Name</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Trigger Event</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Channels</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Priority</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($rules as $rule)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $rule->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">{{ str_replace('_', ' ', ucfirst($rule->trigger_event)) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ str_replace(',', ', ', $rule->channels) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full {{ $rule->priority === 'critical' ? 'bg-red-100 text-red-800' : ($rule->priority === 'high' ? 'bg-orange-100 text-orange-800' : ($rule->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800')) }}">{{ ucfirst($rule->priority) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($rule->is_active)
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"><i
                                                    class="fas fa-circle text-green-500 mr-1"></i>Active</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800"><i
                                                    class="fas fa-circle text-gray-500 mr-1"></i>Inactive</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('healthcare.notifications.show', $rule) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-3"><i
                                                class="fas fa-eye"></i></a>
                                        <a href="{{ route('healthcare.notifications.edit', $rule) }}"
                                            class="text-yellow-600 hover:text-yellow-900 mr-3"><i
                                                class="fas fa-edit"></i></a>
                                        <form action="{{ route('healthcare.notifications.destroy', $rule) }}"
                                            method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900"><i
                                                    class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No notification rules
                                        found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $rules->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
