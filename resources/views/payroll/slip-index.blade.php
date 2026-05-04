<x-app-layout>
    <x-slot name="header">Slip Gaji Saya</x-slot>

    @if(!$employee)
    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <p class="text-gray-500 text-sm">Akun Anda belum terhubung ke data karyawan.</p>
        <p class="text-gray-400 text-xs mt-1">Hubungi admin untuk menghubungkan akun ke profil karyawan.</p>
    </div>
    @else

    {{-- Employee info card --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
            <span class="text-blue-600 font-bold text-lg">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
        </div>
        <div>
            <p class="font-semibold text-gray-900">{{ $employee->name }}</p>
            <p class="text-sm text-gray-500">{{ $employee->position ?? '-' }} {{ $employee->department ? '· '.$employee->department : '' }}</p>
        </div>
        <div class="ml-auto text-right hidden sm:block">
            <p class="text-xs text-gray-400">NIK</p>
            <p class="text-sm font-medium text-gray-700">{{ $employee->employee_id ?? '-' }}</p>
        </div>
    </div>

    @if($items->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
        <p class="text-gray-400 text-sm">Belum ada slip gaji tersedia.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($items ?? [] as $item)
        @php
            $run    = $item->payrollRun;
            $period = $run?->period ?? '—';
            [$yr, $mo] = str_contains($period, '-') ? explode('-', $period) : [$period, ''];
            $monthName = $mo ? \Carbon\Carbon::createFromFormat('m', $mo)->locale('id')->monthName : '';
        @endphp
        <a href="{{ route('payroll.slip.show', $item) }}"
            class="bg-white rounded-2xl border border-gray-200 p-5 hover:border-blue-500/50 hover:shadow-lg transition group">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide">{{ $yr }}</p>
                    <p class="text-lg font-bold text-gray-900 capitalize">{{ $monthName ?: $period }}</p>
                </div>
                <span class="px-2 py-0.5 rounded-full text-xs
                    {{ $item->status === 'paid'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-amber-100 text-amber-700' }}">
                    {{ $item->status === 'paid' ? 'Dibayar' : 'Diproses' }}
                </span>
            </div>

            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Gaji Pokok</span>
                    <span class="text-gray-700">Rp {{ number_format($item->base_salary, 0, ',', '.') }}</span>
                </div>
                @if($item->overtime_pay > 0)
                <div class="flex justify-between">
                    <span class="text-gray-500">Lembur</span>
                    <span class="text-green-600">+Rp {{ number_format($item->overtime_pay, 0, ',', '.') }}</span>
                </div>
                @endif
                @php $totalDeduct = ($item->deduction_absent ?? 0) + ($item->deduction_late ?? 0) + ($item->bpjs_employee ?? 0) + ($item->tax_pph21 ?? 0); @endphp
                @if($totalDeduct > 0)
                <div class="flex justify-between">
                    <span class="text-gray-500">Potongan</span>
                    <span class="text-red-500">-Rp {{ number_format($totalDeduct, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between pt-2 border-t border-gray-100 font-semibold">
                    <span class="text-gray-900">Take Home Pay</span>
                    <span class="text-blue-600">Rp {{ number_format($item->net_salary, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-1 text-xs text-blue-500 group-hover:gap-2 transition-all">
                Lihat Detail
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </a>
        @endforeach
    </div>
    @endif
    @endif
</x-app-layout>
