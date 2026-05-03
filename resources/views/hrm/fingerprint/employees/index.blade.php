@extends('layouts.app')

@section('title', 'Status Fingerprint Karyawan')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Status Fingerprint Karyawan</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola registrasi fingerprint untuk semua karyawan</p>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Karyawan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Jabatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Departemen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                            Status Fingerprint</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $employee->name }}</div>
                                <div class="text-sm text-gray-500">{{ $employee->employee_id }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $employee->position ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $employee->department ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($employee->fingerprint_registered)
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                        ✓ Terdaftar
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">{{ $employee->fingerprint_uid }}</div>
                                @else
                                    <span
                                        class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                        Belum Terdaftar
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a href="{{ route('hrm.fingerprint.employees.register', $employee) }}"
                                    class="text-blue-600 hover:text-blue-900">
                                    {{ $employee->fingerprint_registered ? 'Kelola' : 'Daftarkan' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                Tidak ada data karyawan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employees->hasPages())
            <div class="mt-4">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
@endsection
