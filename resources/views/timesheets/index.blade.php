<x-app-layout>
    <x-slot name="header">Timesheet</x-slot>

    <div class="space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Jam (filter)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalHours, 1) }} <span class="text-sm font-normal text-gray-400">jam</span></p>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Biaya (filter)</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($totalCost, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Add Entry --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Catat Waktu Kerja</h2>
            <form method="POST" action="{{ route('timesheets.store') }}"
                  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Proyek *</label>
                    <select name="project_id" required
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                        <option value="">Pilih proyek...</option>
                        @foreach($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Tanggal *</label>
                    <input type="date" name="date" required value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}"
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Jam *</label>
                    <input type="number" name="hours" required min="0.25" max="24" step="0.25" placeholder="8"
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Tarif/Jam (Rp)</label>
                    <input type="number" name="hourly_rate" min="0" step="1000" placeholder="0"
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Deskripsi *</label>
                    <input type="text" name="description" required placeholder="Pekerjaan yang dilakukan..."
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <div class="sm:col-span-2 lg:col-span-5 flex justify-end">
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">
                        Simpan Entri
                    </button>
                </div>
            </form>
        </div>

        {{-- Filter --}}
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="project_id"
                class="bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                <option value="">Semua Proyek</option>
                @foreach($projects as $project)
                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
            @if($users->isNotEmpty())
            <select name="user_id"
                class="bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                <option value="">Semua Anggota</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
            @endif
            <input type="month" name="month" value="{{ request('month') }}"
                class="bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">Filter</button>
            @if(request()->hasAny(['project_id','user_id','month']))
            <a href="{{ route('timesheets.index') }}" class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 text-sm font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-white/5 transition">Reset</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            @if($timesheets->isEmpty())
                <div class="px-6 py-16 text-center text-gray-400 dark:text-slate-500 text-sm">Belum ada entri timesheet.</div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Tanggal</th>
                            <th class="px-6 py-3 text-left">Proyek</th>
                            <th class="px-6 py-3 text-left hidden sm:table-cell">Anggota</th>
                            <th class="px-6 py-3 text-left">Deskripsi</th>
                            <th class="px-6 py-3 text-right">Jam</th>
                            <th class="px-6 py-3 text-right hidden md:table-cell">Biaya</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($timesheets as $ts)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-6 py-3 text-gray-500 dark:text-slate-400 whitespace-nowrap">{{ $ts->date->format('d M Y') }}</td>
                            <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">{{ $ts->project?->name ?? '-' }}</td>
                            <td class="px-6 py-3 text-gray-500 dark:text-slate-400 hidden sm:table-cell">{{ $ts->user?->name ?? '-' }}</td>
                            <td class="px-6 py-3 text-gray-600 dark:text-slate-300 max-w-xs truncate">{{ $ts->description }}</td>
                            <td class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">{{ number_format($ts->hours, 1) }}</td>
                            <td class="px-6 py-3 text-right text-gray-500 dark:text-slate-400 hidden md:table-cell">
                                @if($ts->hourly_rate > 0)
                                Rp {{ number_format($ts->laborCost(), 0, ',', '.') }}
                                @else
                                -
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                @if(auth()->user()->hasRole(['admin','manager']) || $ts->user_id === auth()->id())
                                <form method="POST" action="{{ route('timesheets.destroy', $ts) }}"
                                      onsubmit="return confirm('Hapus entri ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="text-red-400 hover:text-red-300 text-xs font-medium transition">Hapus</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5">
                {{ $timesheets->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
