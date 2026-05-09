<x-app-layout title="Spa Treatments">
    <x-slot name="header">Spa Treatments</x-slot>

    <x-slot name="pageTitle">Daftar Treatment</x-slot>

    {{-- Category Filter --}}
    @if ($categories->isNotEmpty())
        <div class="flex flex-wrap gap-2 mb-4">
            <a href="{{ route('hotel.spa.treatments.index') }}"
                class="px-3 py-1.5 text-xs font-medium rounded-full {{ !request('category') ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                Semua
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('hotel.spa.treatments.index', ['category' => $category]) }}"
                    class="px-3 py-1.5 text-xs font-medium rounded-full {{ request('category') === $category ? 'bg-blue-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
                    {{ ucfirst($category) }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Treatments Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        @if ($treatments->isEmpty())
            <div class="p-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <p>Belum ada treatment</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Nama</th>
                            <th class="px-6 py-3 text-left">Kategori</th>
                            <th class="px-6 py-3 text-center">Durasi</th>
                            <th class="px-6 py-3 text-right">Harga</th>
                            <th class="px-6 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($treatments as $treatment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $treatment->name }}</div>
                                    @if ($treatment->description)
                                        <div class="text-xs text-gray-500 mt-0.5 line-clamp-1">
                                            {{ $treatment->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    <span
                                        class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-blue-50 text-blue-700">
                                        {{ ucfirst($treatment->category ?? '-') }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-center text-gray-700">
                                    {{ $treatment->duration ?? '-' }} menit
                                </td>
                                <td class="px-6 py-3 text-right text-gray-900 whitespace-nowrap">
                                    Rp {{ number_format($treatment->price ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-3 text-center">
                                    @if ($treatment->is_active ?? true)
                                        <span
                                            class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700">Aktif</span>
                                    @else
                                        <span
                                            class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
