<x-app-layout>
    <x-slot name="header">Import Data CSV</x-slot>

    <div class="max-w-3xl space-y-6">

        {{-- Result banner --}}
        @if(session('import_result'))
        @php $r = session('import_result'); @endphp
        <div class="rounded-2xl border p-5 {{ count($r['errors']) > 0 ? 'bg-amber-50 border-amber-200 dark:bg-amber-900/20 dark:border-amber-700' : 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-700' }}">
            <p class="font-semibold text-sm {{ count($r['errors']) > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-green-700 dark:text-green-400' }}">
                Import selesai: {{ $r['created'] }} data berhasil ditambahkan, {{ $r['skipped'] }} dilewati (sudah ada).
            </p>
            @if(count($r['errors']) > 0)
            <ul class="mt-2 space-y-1">
                @foreach($r['errors'] as $err)
                <li class="text-xs text-amber-600 dark:text-amber-400">• {{ $err }}</li>
                @endforeach
            </ul>
            @endif
        </div>
        @endif

        {{-- Info --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-2xl p-5 flex gap-4">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="text-sm text-blue-700 dark:text-blue-300">
                <p class="font-semibold mb-1">Cara import:</p>
                <ol class="list-decimal list-inside space-y-0.5 text-blue-600 dark:text-blue-400">
                    <li>Download template CSV sesuai jenis data</li>
                    <li>Isi data di spreadsheet (Excel/Google Sheets), simpan sebagai CSV</li>
                    <li>Upload file CSV di form di bawah</li>
                    <li>Data yang sudah ada (nama sama) akan dilewati otomatis</li>
                </ol>
            </div>
        </div>

        {{-- Import cards --}}
        @php
        $importTypes = [
            [
                'key'   => 'products',
                'label' => 'Produk',
                'icon'  => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                'color' => 'blue',
                'route' => 'import.products',
                'cols'  => 'name*, sku, barcode, category, unit, price_sell, price_buy, stock_min, initial_stock',
            ],
            [
                'key'   => 'employees',
                'label' => 'Karyawan',
                'icon'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                'color' => 'purple',
                'route' => 'import.employees',
                'cols'  => 'name*, employee_id, email, phone, position, department, join_date, salary, bank_name, bank_account',
            ],
            [
                'key'   => 'customers',
                'label' => 'Customer',
                'icon'  => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'color' => 'green',
                'route' => 'import.customers',
                'cols'  => 'name*, email, phone, company, address, npwp, credit_limit',
            ],
        ];
        @endphp

        @foreach($importTypes as $type)
        @php
        $colors = [
            'blue'   => ['bg' => 'bg-blue-50 dark:bg-blue-900/20',   'icon' => 'text-blue-500', 'border' => 'border-blue-200 dark:border-blue-700', 'btn' => 'bg-blue-600 hover:bg-blue-700'],
            'purple' => ['bg' => 'bg-purple-50 dark:bg-purple-900/20','icon' => 'text-purple-500','border' => 'border-purple-200 dark:border-purple-700','btn' => 'bg-purple-600 hover:bg-purple-700'],
            'green'  => ['bg' => 'bg-green-50 dark:bg-green-900/20',  'icon' => 'text-green-500', 'border' => 'border-green-200 dark:border-green-700', 'btn' => 'bg-green-600 hover:bg-green-700'],
        ];
        $c = $colors[$type['color']];
        @endphp
        <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 overflow-hidden">
            <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $c['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $type['icon'] }}"/></svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-900 dark:text-white">Import {{ $type['label'] }}</p>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Kolom: <span class="font-mono">{{ $type['cols'] }}</span> <span class="text-gray-300">(*wajib)</span></p>
                </div>
                <a href="{{ route('import.template', $type['key']) }}"
                   class="shrink-0 flex items-center gap-1.5 text-xs text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white border border-gray-200 dark:border-white/10 px-3 py-1.5 rounded-lg transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Template CSV
                </a>
            </div>
            <form method="POST" action="{{ route($type['route']) }}" enctype="multipart/form-data" class="px-6 py-4">
                @csrf
                <div class="flex gap-3 items-end">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">Pilih file CSV</label>
                        <input type="file" name="file" accept=".csv,.txt" required
                            class="w-full text-sm text-gray-600 dark:text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 dark:file:bg-white/10 file:text-gray-700 dark:file:text-white hover:file:bg-gray-200 dark:hover:file:bg-white/20 file:cursor-pointer cursor-pointer">
                    </div>
                    <button type="submit" class="shrink-0 px-5 py-2 rounded-xl {{ $c['btn'] }} text-white text-sm font-medium transition">
                        Upload & Import
                    </button>
                </div>
            </form>
        </div>
        @endforeach

    </div>
</x-app-layout>
