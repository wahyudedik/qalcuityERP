@extends('layouts.app')

@section('content')
    <!-- Pull to Refresh Indicator -->
    <div id="pull-indicator">
        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
    </div>

    <div class="mobile-container safe-top safe-bottom pb-20">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="mobile-h1 font-bold text-gray-900">Dashboard</h1>
            <p class="text-gray-600 mt-1">Selamat datang kembali!</p>
        </div>

        <!-- Quick Stats (Swipeable Cards) -->
        <div class="mb-6 -mx-4 px-4 overflow-x-auto hide-scrollbar smooth-scroll">
            <div class="flex space-x-4" style="width: max-content;">
                <!-- Revenue Card -->
                <div class="swipe-card bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-lg touch-target"
                    style="min-width: 280px;">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-blue-100">Pendapatan Hari Ini</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold">Rp 8.5jt</p>
                    <p class="text-blue-100 text-sm mt-2">↑ 12% dari kemarin</p>
                </div>

                <!-- Orders Card -->
                <div class="swipe-card bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-lg touch-target"
                    style="min-width: 280px;">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-green-100">Order Baru</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold">24</p>
                    <p class="text-green-100 text-sm mt-2">↑ 8% dari kemarin</p>
                </div>

                <!-- Customers Card -->
                <div class="swipe-card bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-lg touch-target"
                    style="min-width: 280px;">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-purple-100">Pelanggan</span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <p class="text-3xl font-bold">156</p>
                    <p class="text-purple-100 text-sm mt-2">Total aktif</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions Grid -->
        <div class="grid grid-cols-2 gap-4 mb-6">
            <a href="{{ route('pos.index') }}"
                class="touch-target-lg bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col items-center justify-center space-y-2 active:scale-95 transition-transform">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <span class="font-semibold text-gray-900">Kasir POS</span>
            </a>

            <a href="{{ route('invoices.create') }}"
                class="touch-target-lg bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col items-center justify-center space-y-2 active:scale-95 transition-transform">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <span class="font-semibold text-gray-900">Invoice</span>
            </a>

            <button onclick="openCameraScanner()"
                class="touch-target-lg bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col items-center justify-center space-y-2 active:scale-95 transition-transform">
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <span class="font-semibold text-gray-900">Scan Barcode</span>
            </button>

            <a href="{{ route('inventory.index') }}"
                class="touch-target-lg bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col items-center justify-center space-y-2 active:scale-95 transition-transform">
                <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <span class="font-semibold text-gray-900">Stok</span>
            </a>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Aktivitas Terbaru</h2>
                <a href="#" class="text-blue-600 text-sm font-medium">Lihat Semua</a>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach (range(1, 5) as $i)
                    <div class="px-4 py-3 flex items-center space-x-3 touch-target active:bg-gray-50">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 truncate">Invoice
                                #INV-{{ str_pad($i, 4, '0', STR_PAD_LEFT) }}</p>
                            <p class="text-sm text-gray-600">Rp {{ number_format(rand(100000, 5000000), 0, ',', '.') }}</p>
                        </div>
                        <span class="text-xs text-gray-500">{{ $i }} jam lalu</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
            <div class="flex items-start space-x-3">
                <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <h3 class="font-semibold text-yellow-900">Stok Menipis</h3>
                    <p class="text-sm text-yellow-800 mt-1">3 produk perlu restock segera</p>
                    <a href="{{ route('inventory.index') }}"
                        class="inline-block mt-2 text-sm font-medium text-yellow-900 underline">
                        Lihat Detail →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <button class="fab touch-ripple" onclick="showQuickActions()">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
    </button>

    <!-- Camera Scanner Modal -->
    <div id="camera-modal" class="hidden camera-overlay">
        <div class="camera-viewfinder">
            <video id="camera-video" autoplay playsinline></video>
            <canvas id="camera-canvas" class="hidden"></canvas>
        </div>
        <div class="camera-controls">
            <button onclick="closeCameraScanner()" class="text-white text-lg font-medium px-4 py-2">
                Batal
            </button>
            <button id="capture-button" class="capture-button" style="display: none;"></button>
            <button onclick="toggleFlashlight()" class="text-white">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="{{ route('dashboard') }}" class="bottom-nav-item active">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span>Home</span>
        </a>
        <a href="{{ route('pos.index') }}" class="bottom-nav-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <span>POS</span>
        </a>
        <a href="{{ route('inventory.index') }}" class="bottom-nav-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span>Stok</span>
        </a>
        <a href="{{ route('reports.index') }}" class="bottom-nav-item">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Laporan</span>
        </a>
    </nav>
@endsection

@push('scripts')
    <script src="{{ asset('js/mobile-gestures.js') }}"></script>
    <script src="{{ asset('js/camera-scanner.js') }}"></script>
    <script>
        // Open camera scanner for barcode
        function openCameraScanner() {
            document.getElementById('camera-modal').classList.remove('hidden');

            window.cameraScanner.startBarcodeScanning(
                (result) => {
                    alert('Barcode scanned: ' + result);
                    closeCameraScanner();
                },
                (error) => {
                    console.error('Scan error:', error);
                    alert('Scan failed. Please try again.');
                }
            );
        }

        // Close camera scanner
        function closeCameraScanner() {
            window.cameraScanner.stopScanning();
            document.getElementById('camera-modal').classList.add('hidden');
        }

        // Toggle flashlight
        function toggleFlashlight() {
            // Implementation would go here
            alert('Flashlight toggled');
        }

        // Show quick actions menu
        function showQuickActions() {
            // Could show a modal or expandable menu
            alert('Quick actions menu');
        }

        // Register service worker for PWA
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('SW registered:', registration);
                })
                .catch(error => {
                    console.log('SW registration failed:', error);
                });
        }

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                console.log('Notification permission:', permission);
            });
        }
    </script>
@endpush
