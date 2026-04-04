<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('hotel.guests.show', $guest) }}"
                class="text-gray-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <span>{{ $guest->name }} - Preferences</span>
            @php
                $vipColor = match ($guest->vip_level) {
                    'platinum' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                    'gold' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400',
                    'silver' => 'bg-slate-100 text-slate-600 dark:bg-slate-500/20 dark:text-slate-400',
                    default => '',
                };
            @endphp
            @if ($guest->vip_level && $guest->vip_level !== 'regular')
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $vipColor }}">
                    {{ strtoupper($guest->vip_level) }} VIP
                </span>
            @endif
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto">
        {{-- Success/Error Messages --}}
        @if (session('success'))
            <div
                class="mb-6 p-4 rounded-xl bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20">
                <p class="text-green-800 dark:text-green-200 text-sm">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Guest Summary Card --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div
                        class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center text-2xl font-bold text-blue-600 dark:text-blue-400 shrink-0">
                        {{ substr($guest->name ?? '?', 0, 1) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $guest->name }}</h2>
                        <p class="text-sm text-gray-500 dark:text-slate-400">{{ $guest->email ?? 'No email provided' }}
                        </p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs text-gray-500 dark:text-slate-400">Loyalty Points:</span>
                            <span
                                class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ number_format($guest->loyalty_points ?? 0) }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button onclick="openAwardPointsModal()"
                        class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium transition">
                        Award Points
                    </button>
                    <button onclick="openAddPreferenceModal()"
                        class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                        Add Preference
                    </button>
                </div>
            </div>
        </div>

        {{-- Preferences by Category --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Room Preferences --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Room Preferences
                    </h3>
                    <button onclick="openAddPreferenceModal('room')"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm font-medium">
                        + Add
                    </button>
                </div>
                <div class="space-y-3">
                    @forelse ($preferences->where('category', 'room') as $preference)
                        <div class="flex items-start justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-gray-900 dark:text-white">{{ ucwords(str_replace('_', ' ', $preference->preference_key)) }}</span>
                                    @if ($preference->priority >= 3)
                                        <span
                                            class="px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">High</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 dark:text-slate-300 mt-1">
                                    {{ $preference->preference_value ?? 'Not specified' }}</p>
                                @if ($preference->is_auto_applied)
                                    <span class="text-xs text-green-600 dark:text-green-400 mt-1 block">Auto-applied to
                                        reservations</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    onclick="editPreference({{ $preference->id }}, '{{ $preference->preference_key }}', '{{ $preference->preference_value }}', {{ $preference->priority }}, {{ $preference->is_auto_applied ? 'true' : 'false' }})"
                                    class="p-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-white/10 transition">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form
                                    action="{{ route('hotel.guests.destroy-preference', [$guest->id, $preference->id]) }}"
                                    method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition"
                                        onclick="return confirm('Delete this preference?')">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-slate-400 text-center py-4">No room preferences yet
                        </p>
                    @endforelse
                </div>
            </div>

            {{-- Amenity Preferences --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        Amenity Preferences
                    </h3>
                    <button onclick="openAddPreferenceModal('amenity')"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm font-medium">
                        + Add
                    </button>
                </div>
                <div class="space-y-3">
                    @forelse ($preferences->where('category', 'amenity') as $preference)
                        <div class="flex items-start justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-gray-900 dark:text-white">{{ ucwords(str_replace('_', ' ', $preference->preference_key)) }}</span>
                                    @if ($preference->priority >= 3)
                                        <span
                                            class="px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400">High</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 dark:text-slate-300 mt-1">
                                    {{ $preference->preference_value ?? 'Not specified' }}</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    onclick="editPreference({{ $preference->id }}, '{{ $preference->preference_key }}', '{{ $preference->preference_value }}', {{ $preference->priority }}, {{ $preference->is_auto_applied ? 'true' : 'false' }})"
                                    class="p-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-white/10 transition">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form
                                    action="{{ route('hotel.guests.destroy-preference', [$guest->id, $preference->id]) }}"
                                    method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition"
                                        onclick="return confirm('Delete this preference?')">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-slate-400 text-center py-4">No amenity preferences
                            yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Dietary Preferences --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        Dietary Preferences
                    </h3>
                    <button onclick="openAddPreferenceModal('dietary')"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm font-medium">
                        + Add
                    </button>
                </div>
                <div class="space-y-3">
                    @forelse ($preferences->where('category', 'dietary') as $preference)
                        <div class="flex items-start justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-gray-900 dark:text-white">{{ ucwords(str_replace('_', ' ', $preference->preference_key)) }}</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-slate-300 mt-1">
                                    {{ $preference->preference_value ?? 'Not specified' }}</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    onclick="editPreference({{ $preference->id }}, '{{ $preference->preference_key }}', '{{ $preference->preference_value }}', {{ $preference->priority }}, {{ $preference->is_auto_applied ? 'true' : 'false' }})"
                                    class="p-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-white/10 transition">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form
                                    action="{{ route('hotel.guests.destroy-preference', [$guest->id, $preference->id]) }}"
                                    method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition"
                                        onclick="return confirm('Delete this preference?')">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-slate-400 text-center py-4">No dietary preferences
                            yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Communication Preferences --}}
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                        Communication Preferences
                    </h3>
                    <button onclick="openAddPreferenceModal('communication')"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm font-medium">
                        + Add
                    </button>
                </div>
                <div class="space-y-3">
                    @forelse ($preferences->where('category', 'communication') as $preference)
                        <div class="flex items-start justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-sm font-medium text-gray-900 dark:text-white">{{ ucwords(str_replace('_', ' ', $preference->preference_key)) }}</span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-slate-300 mt-1">
                                    {{ $preference->preference_value ?? 'Not specified' }}</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    onclick="editPreference({{ $preference->id }}, '{{ $preference->preference_key }}', '{{ $preference->preference_value }}', {{ $preference->priority }}, {{ $preference->is_auto_applied ? 'true' : 'false' }})"
                                    class="p-1.5 rounded-lg hover:bg-gray-200 dark:hover:bg-white/10 transition">
                                    <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <form
                                    action="{{ route('hotel.guests.destroy-preference', [$guest->id, $preference->id]) }}"
                                    method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="p-1.5 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition"
                                        onclick="return confirm('Delete this preference?')">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-slate-400 text-center py-4">No communication
                            preferences yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Add/Edit Preference Modal --}}
    <div id="modal-preference" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <form id="form-preference" method="POST" action="">
                @csrf
                <input type="hidden" id="preference-id" name="_method" value="POST">

                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4" id="modal-title">Add
                        Preference</h3>

                    <input type="hidden" name="category" id="pref-category-input" value="room">

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Preference
                                Key *</label>
                            <input type="text" name="preference_key" id="pref-key" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., high_floor, extra_pillow">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Preference
                                Value</label>
                            <textarea name="preference_value" id="pref-value" rows="2"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., Yes, 2 pillows"></textarea>
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Priority</label>
                            <select name="priority" id="pref-priority"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="1">Low</option>
                                <option value="2">Medium</option>
                                <option value="3">High</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="is_auto_applied" id="pref-auto-apply" value="1"
                                checked class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                            <label for="pref-auto-apply" class="text-sm text-gray-700 dark:text-slate-300">
                                Auto-apply to future reservations
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 pb-6">
                    <button type="button" onclick="closePreferenceModal()"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Save Preference
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Award Points Modal --}}
    <div id="modal-award-points" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <form action="{{ route('hotel.guests.award-points', $guest) }}" method="POST">
                @csrf

                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Award Loyalty Points</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Points to
                                Award *</label>
                            <input type="number" name="points" required min="1"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="e.g., 100">
                        </div>

                        <div>
                            <label
                                class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Reason</label>
                            <textarea name="reason" rows="2"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Reason for awarding points"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 px-6 pb-6">
                    <button type="button"
                        onclick="document.getElementById('modal-award-points').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">
                        Award Points
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function openAddPreferenceModal(category = 'room') {
                document.getElementById('pref-category-input').value = category;
                document.getElementById('modal-title').textContent = 'Add Preference';
                document.getElementById('form-preference').action = "{{ route('hotel.guests.store-preference', $guest) }}";
                document.getElementById('preference-id').value = 'POST';
                document.getElementById('pref-key').value = '';
                document.getElementById('pref-value').value = '';
                document.getElementById('pref-priority').value = '1';
                document.getElementById('pref-auto-apply').checked = true;
                document.getElementById('modal-preference').classList.remove('hidden');
            }

            function editPreference(id, key, value, priority, autoApply) {
                document.getElementById('modal-title').textContent = 'Edit Preference';
                document.getElementById('form-preference').action =
                    "{{ route('hotel.guests.update-preference', [$guest->id, '__ID__']) }}".replace('__ID__', id);
                document.getElementById('preference-id').value = 'PATCH';
                document.getElementById('pref-key').value = key;
                document.getElementById('pref-value').value = value || '';
                document.getElementById('pref-priority').value = priority;
                document.getElementById('pref-auto-apply').checked = autoApply;
                document.getElementById('modal-preference').classList.remove('hidden');
            }

            function closePreferenceModal() {
                document.getElementById('modal-preference').classList.add('hidden');
            }

            function openAwardPointsModal() {
                document.getElementById('modal-award-points').classList.remove('hidden');
            }
        </script>
    @endpush
</x-app-layout>
