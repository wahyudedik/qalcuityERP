<x-app-layout>
    <x-slot name="header">{{ __('Notification Rule Details') }} -
                {{ $rule->name }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.notifications.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-bell mr-2 text-blue-600"></i>Rule Configuration</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Rule Name</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $rule->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Trigger Event</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-indigo-100 text-indigo-800">{{ str_replace('_', ' ', ucfirst($rule->trigger_event)) }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Priority</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-sm font-semibold rounded-full {{ $rule->priority === 'critical' ? 'bg-red-100 text-red-800' : ($rule->priority === 'high' ? 'bg-orange-100 text-orange-800' : ($rule->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800')) }}">{{ ucfirst($rule->priority) }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Channels</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ str_replace(',', ', ', $rule->channels) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if ($rule->is_active)
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800"><i
                                            class="fas fa-circle text-green-500 mr-1"></i>Active</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800"><i
                                            class="fas fa-circle text-gray-500 mr-1"></i>Inactive</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-file-alt mr-2 text-purple-600"></i>Templates</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Subject Template</dt>
                            <dd class="mt-1 text-sm font-mono bg-gray-50 p-3 rounded">{{ $rule->subject_template }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Message Template</dt>
                            <dd class="mt-1 text-sm font-mono bg-gray-50 p-3 rounded whitespace-pre-line">
                                {{ $rule->message_template }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if ($notifications && $notifications->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-history mr-2 text-green-600"></i>Recent Notifications
                        ({{ $notifications->count() }})</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Recipient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Channel
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($notifications->take(20) as $notification)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $notification->recipient?->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ ucfirst($notification->channel) }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full {{ $notification->sent_at ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $notification->sent_at ? 'Sent' : 'Pending' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
