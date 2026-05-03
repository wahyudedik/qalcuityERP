@extends('layouts.app')

@section('title', 'Buat Workflow')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('automation.workflows.index') }}"
                class="text-blue-600 hover:text-blue-900 text-sm">
                ← Kembali ke Workflows
            </a>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-2">Buat Workflow Baru</h1>
        </div>

        <form action="{{ route('automation.workflows.store') }}" method="POST" x-data="{ triggerType: 'event' }">
            @csrf

            <!-- Basic Information -->
            <div class="bg-white shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Informasi Dasar</h3>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama
                                Workflow</label>
                            <input type="text" name="name" id="name" required value="{{ old('name') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="contoh: Auto-Buat PO Saat Stok Rendah">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="description" id="description" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                placeholder="Jelaskan apa yang dilakukan workflow ini...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="priority"
                                class="block text-sm font-medium text-gray-700">Prioritas (0-100)</label>
                            <input type="number" name="priority" id="priority" value="{{ old('priority', 0) }}"
                                min="0" max="100"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">Workflow dengan prioritas lebih tinggi
                                dieksekusi lebih dulu</p>
                            @error('priority')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trigger Configuration -->
            <div class="bg-white shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Konfigurasi Trigger</h3>

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe
                                Trigger</label>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="trigger_type" value="event" x-model="triggerType" checked
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Berbasis Event</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="trigger_type" value="schedule" x-model="triggerType"
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Terjadwal</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="trigger_type" value="condition" x-model="triggerType"
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Berbasis Kondisi</span>
                                </label>
                            </div>
                        </div>

                        <!-- Event Trigger Config -->
                        <div x-show="triggerType === 'event'" class="border-t border-gray-200 pt-4">
                            <label for="event_name" class="block text-sm font-medium text-gray-700">Nama
                                Event</label>
                            <select name="trigger_config[event]" id="event_name"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Pilih event...</option>
                                <option value="inventory.stock_low">Stok Inventory Rendah</option>
                                <option value="inventory.stock_updated">Stok Inventory Diperbarui</option>
                                <option value="sales.order_completed">Sales Order Selesai</option>
                                <option value="invoice.overdue">Invoice Jatuh Tempo</option>
                                <option value="invoice.paid">Invoice Dibayar</option>
                                <option value="customer.created">Pelanggan Baru Dibuat</option>
                                <option value="purchase.order_approved">PO Disetujui</option>
                                <option value="hrm.leave_requested">Pengajuan Cuti</option>
                                <option value="hrm.contract_expiring">Kontrak Hampir Berakhir</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Pilih event yang akan memicu workflow
                                ini</p>
                        </div>

                        <!-- Schedule Trigger Config -->
                        <div x-show="triggerType === 'schedule'" class="border-t border-gray-200 pt-4">
                            <label for="schedule_type"
                                class="block text-sm font-medium text-gray-700">Jadwal</label>
                            <select name="trigger_config[schedule]" id="schedule_type"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Pilih jadwal...</option>
                                <option value="every_minute">Setiap Menit</option>
                                <option value="hourly">Setiap Jam</option>
                                <option value="daily_9am">Harian Jam 9 Pagi</option>
                                <option value="daily_midnight">Harian Tengah Malam</option>
                                <option value="weekly_monday">Mingguan Hari Senin</option>
                                <option value="monthly_first">Bulanan Tanggal 1</option>
                                <option value="invoice_overdue_check">Cek Invoice Jatuh Tempo (Harian Jam 9)</option>
                                <option value="monthly_bonus_calculation">Kalkulasi Bonus Bulanan</option>
                            </select>
                        </div>

                        <!-- Condition Trigger Config -->
                        <div x-show="triggerType === 'condition'"
                            class="border-t border-gray-200 pt-4">
                            <p class="text-sm text-gray-500">Konfigurasi kondisi akan tersedia setelah
                                workflow dibuat. Anda dapat menambahkan kondisi di halaman detail workflow.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Status -->
            <div class="bg-white shadow sm:rounded-lg mb-6">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                            Aktifkan workflow segera
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <a href="{{ route('automation.workflows.index') }}"
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Buat Workflow
                </button>
            </div>
        </form>
    </div>
@endsection
