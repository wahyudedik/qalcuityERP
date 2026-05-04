<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-smile text-blue-600"></i> Patient Satisfaction Score
            </h1>
            <p class="text-gray-500">Patient experience and satisfaction metrics</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="w-full md:w-1/4">
                            <h2 class="text-amber-600">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i
                                        class="fas fa-star {{ $i <= ($stats['avg_rating'] ?? 0) ? 'text-amber-600' : 'text-gray-500' }}"></i>
                                @endfor
                            </h2>
                            <small class="text-gray-500">Average Rating</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-emerald-600">{{ $stats['satisfaction_rate'] ?? 0 }}%</h2>
                            <small class="text-gray-500">Satisfaction Rate</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-blue-600">{{ $stats['total_surveys'] ?? 0 }}</h2>
                            <small class="text-gray-500">Total Surveys</small>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h2 class="text-sky-600">{{ $stats['response_rate'] ?? 0 }}%</h2>
                            <small class="text-gray-500">Response Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Satisfaction by Category</h5>
                </div>
                <div class="p-5">
                    @forelse($categoryScores ?? [] as $category)
                        <div class="mb-3">
                            <div class="flex justify-between mb-1">
                                <strong>{{ $category['name'] }}</strong>
                                <span>{{ $category['score'] }}/5.0</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full overflow-hidden" style="height: 25px;">
                                <div class="h-full rounded-full bg-{{ $category['score'] >= 4 ? 'emerald-500' : ($category['score'] >= 3 ? 'amber-500' : 'red-500')   }}"
                                    style="width: {{ ($category['score'] / 5) * 100 }}%">
                                    {{ $category['score'] }}/5.0
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center">No category data available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="w-full md:w-1/2">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Satisfaction Trend</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Rating</th>
                                    <th>Surveys</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($satisfactionTrend ?? [] as $period)
                                    <tr>
                                        <td>{{ $period['period'] }}</td>
                                        <td>
                                            <strong>{{ $period['rating'] }}/5.0</strong>
                                        </td>
                                        <td>{{ $period['surveys'] }}</td>
                                        <td>
                                            @if ($period['trend'] > 0)
                                                <span class="text-emerald-600"><i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-up"></i>
                                                    +{{ $period['trend'] }}%</span>
                                            @elseif($period['trend'] < 0)
                                                <span class="text-red-600"><i class="fas fa-argrid grid-cols-1 md:grid-cols-2 gap-6-down"></i>
                                                    {{ $period['trend'] }}%</span>
                                            @else
                                                <span class="text-gray-500"><i class="fas fa-minus"></i> 0%</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-gray-400">No trend data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Recent Feedback</h5>
                </div>
                <div class="p-5">
                    @forelse($recentFeedback ?? [] as $feedback)
                        <div class="mb-3 p-3 bg-gray-50 rounded">
                            <div class="flex justify-between mb-2">
                                <div>
                                    <strong>{{ $feedback['patient_name'] ?? 'Anonymous' }}</strong>
                                    <br><small class="text-gray-500">{{ $feedback['created_at'] ?? '-' }}</small>
                                </div>
                                <div>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i
                                            class="fas fa-star {{ $i <= ($feedback['rating'] ?? 0) ? 'text-amber-600' : 'text-gray-500' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <p class="mb-0">{{ $feedback['comment'] ?? 'No comment' }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center">No recent feedback</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
