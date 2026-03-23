<x-app-layout>
    <x-slot name="header">Price List</x-slot>

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500 dark:text-slate-400">Kelola harga khusus per customer berdasarkan tier, kontrak, atau promosi.</p>
        <a href="{{ route('price-lists.create') }}" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Price List Baru</a>
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($priceLists as $pl)
        @php
            $typeColor = match($pl->type) {
                'tier'     => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                'contract' => 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400',
                'promo'    => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                default    => 'bg-gray-100 text-gray-500',
            };
            $isValid = $pl->isValid();
        @endphp
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $pl->name }}</h3>
                    @if($pl->code)<p class="text-xs text-gray-400 dark:text-slate-500">{{ $pl->code }}</p>@endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $typeColor }}">{{ $pl->typeLabel() }}</span>
                    <span class="w-2 h-2 rounded-full {{ $isValid ? 'bg-green-500' : 'bg-gray-300 dark:bg-white/20' }}"></span>
                </div>
            </div>

            @if($pl->description)
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-3">{{ $pl->description }}</p>
            @endif

            <div class="flex gap-4 text-xs text-gray-500 dark:text-slate-400 mb-3">
                <span>📦 {{ $pl->items_count }} produk</span>
                <span>👥 {{ $pl->customers->count() }} customer</span>
            </div>

            @if($pl->valid_from || $pl->valid_until)
            <p class="text-xs text-gray-400 dark:text-slate-500 mb-3">
                Berlaku: {{ $pl->valid_from?->format('d M Y') ?? '∞' }} – {{ $pl->valid_until?->format('d M Y') ?? '∞' }}
            </p>
            @endif

            <div class="flex items-center gap-2 pt-3 border-t border-gray-100 dark:border-white/5">
                <a href="{{ route('price-lists.show', $pl) }}" class="flex-1 text-center px-3 py-1.5 text-xs border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5">Detail</a>
                <form method="POST" action="{{ route('price-lists.destroy', $pl) }}" onsubmit="return confirm('Hapus price list ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-xs text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg">Hapus</button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-3 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada price list. <a href="{{ route('price-lists.create') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Buat sekarang</a>.</div>
        @endforelse
    </div>
</x-app-layout>
