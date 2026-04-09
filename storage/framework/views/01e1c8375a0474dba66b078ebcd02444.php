<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi - Qalcuity ERP</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta name="description" content="Dokumentasi lengkap Qalcuity ERP - Panduan modul, fitur, dan manual pengguna">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #60a5fa, #818cf8, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .sidebar-link {
            transition: all 0.2s;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
        }
    </style>
</head>

<body class="font-[Inter,sans-serif] bg-gray-50 text-gray-900 antialiased">

    
    <nav class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-xl border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="<?php echo e(route('landing')); ?>" class="flex items-center gap-2.5">
                <img src="/logo.png" alt="Qalcuity ERP" class="h-8 w-auto object-contain brightness-0" loading="lazy">
                <span class="text-sm font-semibold text-gray-600">|</span>
                <span class="text-sm font-semibold text-gray-700">Dokumentasi</span>
            </a>
            <div class="flex items-center gap-3">
                <a href="<?php echo e(route('landing')); ?>"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900 px-4 py-2 rounded-xl hover:bg-gray-100 transition">
                    ← Kembali ke Beranda
                </a>
                <a href="<?php echo e(route('register')); ?>"
                    class="text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 px-5 py-2.5 rounded-xl transition shadow-sm">
                    Coba Gratis
                </a>
            </div>
        </div>
    </nav>

    
    <div class="pt-16 flex min-h-screen">
        
        
        <aside class="fixed left-0 top-16 w-72 h-[calc(100vh-4rem)] bg-white border-r border-gray-200 overflow-y-auto">
            <div class="p-6 space-y-6">
                
                
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Memulai</h3>
                    <div class="space-y-1">
                        <a href="#overview" class="sidebar-link active block px-4 py-2.5 rounded-lg text-sm font-medium">
                            📋 Overview Sistem
                        </a>
                        <a href="#quick-start" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            🚀 Quick Start
                        </a>
                        <a href="#first-login" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            🔐 Login Pertama
                        </a>
                    </div>
                </div>

                
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Modul Utama</h3>
                    <div class="space-y-1">
                        <a href="#finance" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            💰 Finance & Accounting
                        </a>
                        <a href="#sales" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            🛒 Sales & CRM
                        </a>
                        <a href="#purchasing" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            📦 Purchasing
                        </a>
                        <a href="#inventory" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            🏭 Inventory & Warehouse
                        </a>
                        <a href="#hrm" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            👥 HRM & Payroll
                        </a>
                    </div>
                </div>

                
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Modul Industri</h3>
                    <div class="space-y-1">
                        <a href="#healthcare" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            🏥 Healthcare
                        </a>
                        <a href="#hotel" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            🏨 Hotel Management
                        </a>
                        <a href="#manufacturing" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            🏭 Manufacturing
                        </a>
                        <a href="#agriculture" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            🌾 Agriculture
                        </a>
                        <a href="#fisheries" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            🐟 Fisheries
                        </a>
                        <a href="#livestock" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            🐄 Livestock
                        </a>
                        <a href="#cosmetics" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            💄 Cosmetics
                        </a>
                        <a href="#tour-travel" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            ✈️ Tour & Travel
                        </a>
                    </div>
                </div>

                
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Panduan Pengguna</h3>
                    <div class="space-y-1">
                        <a href="#common-tasks" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            📝 Common Tasks
                        </a>
                        <a href="#reports" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            📊 Reports & Export
                        </a>
                        <a href="#keyboard-shortcuts" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            ⌨️ Keyboard Shortcuts
                        </a>
                        <a href="#troubleshooting" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            🔧 Troubleshooting
                        </a>
                        <a href="#faq" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            ❓ FAQ
                        </a>
                    </div>
                </div>

                
                <div>
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Resources</h3>
                    <div class="space-y-1">
                        <a href="/api-docs" target="_blank" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            📖 API Documentation
                        </a>
                        <a href="#best-practices" class="sidebar-link block px-4 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:text-white">
                            ✅ Best Practices
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        
        <main class="ml-72 flex-1 max-w-5xl px-8 py-12">
            
            
            <section id="overview" class="mb-16">
                <h1 class="text-4xl font-bold mb-4 gradient-text">Dokumentasi Qalcuity ERP</h1>
                <p class="text-xl text-gray-600 mb-8">Panduan lengkap untuk menggunakan semua modul dan fitur Qalcuity ERP</p>
                
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm">
                    <h2 class="text-2xl font-bold mb-4">Apa itu Qalcuity ERP?</h2>
                    <p class="text-gray-700 leading-relaxed mb-4">
                        Qalcuity ERP adalah platform ERP (Enterprise Resource Planning) berbasis AI yang dirancang khusus untuk berbagai industri di Indonesia. 
                        Dengan 70+ modul terintegrasi, Anda dapat mengelola seluruh aspek bisnis dari satu platform.
                    </p>
                    
                    <div class="grid md:grid-cols-3 gap-6 mt-8">
                        <div class="bg-blue-50 rounded-xl p-6">
                            <div class="text-3xl font-bold text-blue-600 mb-2">70+</div>
                            <div class="text-sm text-gray-700">Modul Terintegrasi</div>
                        </div>
                        <div class="bg-indigo-50 rounded-xl p-6">
                            <div class="text-3xl font-bold text-indigo-600 mb-2">10+</div>
                            <div class="text-sm text-gray-700">Industri Didukung</div>
                        </div>
                        <div class="bg-purple-50 rounded-xl p-6">
                            <div class="text-3xl font-bold text-purple-600 mb-2">AI</div>
                            <div class="text-sm text-gray-700">Powered Assistant</div>
                        </div>
                    </div>
                </div>
            </section>

            
            <section id="quick-start" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">🚀 Quick Start</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm space-y-6">
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">1</div>
                        <div>
                            <h3 class="font-semibold text-lg mb-2">Daftar Akun</h3>
                            <p class="text-gray-700">Klik tombol "Coba Gratis" di halaman beranda dan isi formulir pendaftaran. Anda akan mendapatkan akses 14 hari gratis.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">2</div>
                        <div>
                            <h3 class="font-semibold text-lg mb-2">Verifikasi Email</h3>
                            <p class="text-gray-700">Cek email Anda dan klik link verifikasi untuk mengaktifkan akun.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">3</div>
                        <div>
                            <h3 class="font-semibold text-lg mb-2">Setup Profil Perusahaan</h3>
                            <p class="text-gray-700">Lengkapi informasi perusahaan, logo, dan pengaturan awal setelah login pertama kali.</p>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">4</div>
                        <div>
                            <h3 class="font-semibold text-lg mb-2">Mulai Menggunakan</h3>
                            <p class="text-gray-700">Jelajahi dashboard dan mulai tambahkan data bisnis Anda. Gunakan panduan modul di bawah untuk detail setiap fitur.</p>
                        </div>
                    </div>
                </div>
            </section>

            
            <section id="first-login" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">🔐 Login Pertama</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm">
                    <h3 class="text-xl font-semibold mb-4">Langkah Login:</h3>
                    <ol class="list-decimal list-inside space-y-3 text-gray-700">
                        <li>Buka <strong>http://qalcuityerp.test</strong> atau URL perusahaan Anda</li>
                        <li>Klik tombol <strong>"Masuk"</strong> di pojok kanan atas</li>
                        <li>Masukkan <strong>email</strong> dan <strong>password</strong> Anda</li>
                        <li>Klik <strong>"Sign In"</strong></li>
                        <li>Jika 2FA diaktifkan, masukkan kode 6 digit dari aplikasi authenticator</li>
                        <li>Anda akan diarahkan ke <strong>Dashboard</strong></li>
                    </ol>
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-6">
                        <p class="text-sm text-yellow-800">
                            <strong>Tips:</strong> Untuk keamanan, aktifkan Two-Factor Authentication (2FA) di menu Profile > Security Settings.
                        </p>
                    </div>
                </div>
            </section>

            
            <section id="finance" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">💰 Finance & Accounting</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold mb-3">Fitur Utama:</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li><strong>General Ledger:</strong> Pencatatan jurnal double-entry</li>
                            <li><strong>Accounts Payable/Receivable:</strong> Pengelolaan hutang & piutang</li>
                            <li><strong>Budget Management:</strong> Perencanaan & tracking anggaran</li>
                            <li><strong>Financial Reports:</strong> Balance Sheet, Income Statement, Cash Flow</li>
                            <li><strong>Multi-currency:</strong> Dukungan multi mata uang</li>
                            <li><strong>Tax Management:</strong> Perhitungan & pelaporan pajak</li>
                        </ul>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="text-xl font-semibold mb-3">Cara Membuat Invoice:</h3>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700">
                            <li>Buka menu <strong>Finance > Invoices</strong></li>
                            <li>Klik tombol <strong>"New Invoice"</strong></li>
                            <li>Pilih <strong>Customer</strong> dari daftar</li>
                            <li>Tambahkan <strong>line items</strong> (produk/jasa)</li>
                            <li>Review total dan tambahkan catatan (opsional)</li>
                            <li>Klik <strong>"Save & Send"</strong> untuk kirim email ke customer</li>
                        </ol>
                    </div>
                </div>
            </section>

            
            <section id="sales" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">🛒 Sales & CRM</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold mb-3">Fitur Utama:</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li><strong>Lead Management:</strong> Tracking calon customer</li>
                            <li><strong>Opportunity Pipeline:</strong> Visualisasi sales pipeline</li>
                            <li><strong>Sales Orders:</strong> Pengelolaan pesanan penjualan</li>
                            <li><strong>Quotations:</strong> Pembuatan penawaran harga</li>
                            <li><strong>Customer Database:</strong> Database lengkap customer</li>
                            <li><strong>Sales Analytics:</strong> Laporan & analisis penjualan</li>
                        </ul>
                    </div>
                </div>
            </section>

            
            <section id="inventory" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">🏭 Inventory & Warehouse</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold mb-3">Fitur Utama:</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li><strong>Real-time Stock Tracking:</strong> Monitoring stok real-time</li>
                            <li><strong>Multi-location:</strong> Dukungan multi-gudang</li>
                            <li><strong>Batch/Lot Tracking:</strong> Tracking batch & lot</li>
                            <li><strong>Barcode & QR Code:</strong> Support barcode scanning</li>
                            <li><strong>Stock Count:</strong> Opname stok</li>
                            <li><strong>Low Stock Alerts:</strong> Notifikasi stok menipis</li>
                            <li><strong>Expiry Management:</strong> Tracking tanggal kadaluarsa</li>
                        </ul>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="text-xl font-semibold mb-3">Cara Stock In (Penerimaan Barang):</h3>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700">
                            <li>Buka menu <strong>Inventory > Stock In</strong></li>
                            <li>Klik <strong>"New Receipt"</strong></li>
                            <li>Pilih Purchase Order (jika ada)</li>
                            <li>Scan barcode atau cari produk</li>
                            <li>Masukkan jumlah yang diterima</li>
                            <li>Klik <strong>"Complete Receipt"</strong></li>
                        </ol>
                    </div>
                </div>
            </section>

            
            <section id="hrm" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">👥 HRM & Payroll</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold mb-3">Fitur Utama:</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li><strong>Employee Database:</strong> Data lengkap karyawan</li>
                            <li><strong>Attendance:</strong> Clock in/out dengan GPS</li>
                            <li><strong>Leave Management:</strong> Pengajuan & approval cuti</li>
                            <li><strong>Payroll:</strong> Perhitungan gaji otomatis</li>
                            <li><strong>Shift Scheduling:</strong> Jadwal shift</li>
                            <li><strong>Overtime:</strong> Tracking lembur</li>
                        </ul>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="text-xl font-semibold mb-3">Cara Check-in Attendance:</h3>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700">
                            <li>Buka menu <strong>HRM > Attendance</strong></li>
                            <li>Klik tombol <strong>"Check In"</strong></li>
                            <li>Sistem mencatat waktu & lokasi otomatis</li>
                            <li>Untuk check-out, klik <strong>"Check Out"</strong></li>
                        </ol>
                    </div>
                </div>
            </section>

            
            <section id="healthcare" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">🏥 Healthcare</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold mb-3">Fitur Utama:</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li><strong>Patient Registration:</strong> Pendaftaran pasien dengan MRN</li>
                            <li><strong>Electronic Medical Records (EMR):</strong> Rekam medis elektronik</li>
                            <li><strong>Doctor Management:</strong> Pengelolaan dokter & jadwal</li>
                            <li><strong>Appointment System:</strong> Sistem janji temu</li>
                            <li><strong>Surgery Scheduling:</strong> Penjadwalan operasi</li>
                            <li><strong>Admission & Discharge:</strong> Rawat inap</li>
                            <li><strong>Bed Management:</strong> Pengelolaan tempat tidur</li>
                            <li><strong>Medical Billing:</strong> Billing & klaim asuransi (BPJS)</li>
                            <li><strong>Patient Portal:</strong> Portal untuk pasien</li>
                        </ul>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="text-xl font-semibold mb-3">Cara Mendaftarkan Pasien Baru:</h3>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700">
                            <li>Buka menu <strong>Healthcare > Patients</strong></li>
                            <li>Klik <strong>"Register Patient"</strong></li>
                            <li>Isi data pasien (MRN otomatis/generated manual)</li>
                            <li>Masukkan informasi kontak & darurat</li>
                            <li>Tambahkan detail asuransi (jika ada)</li>
                            <li>Klik <strong>"Register"</strong></li>
                        </ol>
                    </div>
                </div>
            </section>

            
            <section id="hotel" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">🏨 Hotel Management</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold mb-3">Fitur Utama:</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li><strong>Room Management:</strong> Pengelolaan kamar & tipe</li>
                            <li><strong>Reservation System:</strong> Sistem reservasi</li>
                            <li><strong>Check-in/Check-out:</strong> Dashboard front desk</li>
                            <li><strong>Housekeeping:</strong> Manajemen kebersihan</li>
                            <li><strong>F&B Integration:</strong> Integrasi restoran</li>
                            <li><strong>Guest Database:</strong> Database tamu</li>
                        </ul>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="text-xl font-semibold mb-3">Cara Check-in Tamu:</h3>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700">
                            <li>Buka menu <strong>Front Desk > Check-ins</strong></li>
                            <li>Cari reservasi (nama/nomor konfirmasi)</li>
                            <li>Verifikasi identitas tamu</li>
                            <li>Assign kamar (atau auto-assign)</li>
                            <li>Kumpulkan deposit (jika diperlukan)</li>
                            <li>Klik <strong>"Complete Check-in"</strong></li>
                        </ol>
                    </div>
                </div>
            </section>

            
            <section id="manufacturing" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">🏭 Manufacturing</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm">
                    <h3 class="text-xl font-semibold mb-3">Fitur Utama:</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li><strong>Production Orders:</strong> Pesanan produksi</li>
                        <li><strong>Bill of Materials (BOM):</strong> Daftar bahan baku</li>
                        <li><strong>Work Orders:</strong> Order kerja per tahap</li>
                        <li><strong>Quality Control:</strong> Inspeksi kualitas</li>
                        <li><strong>Machine Management:</strong> Pengelolaan mesin</li>
                        <li><strong>Capacity Planning:</strong> Perencanaan kapasitas</li>
                    </ul>
                </div>
            </section>

            
            <section id="agriculture" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">🌾 Agriculture</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm">
                    <h3 class="text-xl font-semibold mb-3">Fitur Utama:</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li><strong>Crop Management:</strong> Pengelolaan tanaman</li>
                        <li><strong>Planting Schedules:</strong> Jadwal tanam</li>
                        <li><strong>Harvest Tracking:</strong> Tracking panen</li>
                        <li><strong>Field Management:</strong> Pengelolaan lahan</li>
                        <li><strong>Soil Monitoring:</strong> Monitoring tanah</li>
                        <li><strong>Irrigation Scheduling:</strong> Jadwal irigasi</li>
                    </ul>
                </div>
            </section>

            
            <section id="fisheries" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">🐟 Fisheries</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm">
                    <h3 class="text-xl font-semibold mb-3">Fitur Utama:</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li><strong>Pond Management:</strong> Pengelolaan kolam</li>
                        <li><strong>Water Quality Monitoring:</strong> Monitoring kualitas air</li>
                        <li><strong>Stocking Records:</strong> Pencatatan penebaran</li>
                        <li><strong>Feed Management:</strong> Pengelolaan pakan</li>
                        <li><strong>Harvest Tracking:</strong> Tracking panen</li>
                        <li><strong>FCR Calculation:</strong> Kalkulasi Feed Conversion Ratio</li>
                    </ul>
                </div>
            </section>

            
            <section id="livestock" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">🐄 Livestock</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm">
                    <h3 class="text-xl font-semibold mb-3">Fitur Utama:</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li><strong>Animal Registry:</strong> Registrasi hewan</li>
                        <li><strong>Health Records:</strong> Catatan kesehatan</li>
                        <li><strong>Breeding Management:</strong> Pengelolaan breeding</li>
                        <li><strong>Feed & Nutrition:</strong> Pakan & nutrisi</li>
                        <li><strong>Growth Monitoring:</strong> Monitoring pertumbuhan</li>
                    </ul>
                </div>
            </section>

            
            <section id="cosmetics" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">💄 Cosmetics</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm">
                    <h3 class="text-xl font-semibold mb-3">Fitur Utama:</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li><strong>Product Formulation:</strong> Formulasi produk</li>
                        <li><strong>Ingredient Tracking:</strong> Tracking bahan baku</li>
                        <li><strong>Batch Production:</strong> Produksi batch</li>
                        <li><strong>BPOM Compliance:</strong> Kepatuhan BPOM</li>
                        <li><strong>Expiry Management:</strong> Manajemen kadaluarsa</li>
                    </ul>
                </div>
            </section>

            
            <section id="tour-travel" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">✈️ Tour & Travel</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm">
                    <h3 class="text-xl font-semibold mb-3">Fitur Utama:</h3>
                    <ul class="list-disc list-inside space-y-2 text-gray-700">
                        <li><strong>Tour Packages:</strong> Paket wisata</li>
                        <li><strong>Booking System:</strong> Sistem booking</li>
                        <li><strong>Itinerary Planning:</strong> Perencanaan itinerary</li>
                        <li><strong>Vehicle Management:</strong> Pengelolaan kendaraan</li>
                        <li><strong>Guide Assignment:</strong> Penugasan guide</li>
                    </ul>
                </div>
            </section>

            
            <section id="common-tasks" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">📝 Common Tasks</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm space-y-6">
                    <div>
                        <h3 class="text-xl font-semibold mb-3">Cara Search & Filter:</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li><strong>Global Search:</strong> Klik search bar di atas, ketik keyword, tekan Enter</li>
                            <li><strong>Filter:</strong> Klik tombol "Filters" di list page, pilih kriteria, klik "Apply"</li>
                            <li><strong>Advanced Search:</strong> Klik icon 🔍 > "Advanced Search" > pilih field & kondisi</li>
                        </ul>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="text-xl font-semibold mb-3">Cara Export Data:</h3>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700">
                            <li>Buka list view yang diinginkan</li>
                            <li>Klik tombol <strong>"Export"</strong></li>
                            <li>Pilih format: Excel, CSV, atau PDF</li>
                            <li>Pilih kolom yang akan di-export</li>
                            <li>Klik <strong>"Download"</strong></li>
                        </ol>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="text-xl font-semibold mb-3">Cara Generate Report:</h3>
                        <ol class="list-decimal list-inside space-y-2 text-gray-700">
                            <li>Buka menu <strong>Reports</strong></li>
                            <li>Pilih jenis report</li>
                            <li>Set date range & filter</li>
                            <li>Klik <strong>"Generate Report"</strong></li>
                            <li>Export ke PDF/Excel/CSV sesuai kebutuhan</li>
                        </ol>
                    </div>
                </div>
            </section>

            
            <section id="keyboard-shortcuts" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">⌨️ Keyboard Shortcuts</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="font-semibold mb-3">Navigation:</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between"><span>Global Search</span> <kbd class="px-2 py-1 bg-gray-100 rounded">Ctrl + K</kbd></div>
                                <div class="flex justify-between"><span>Dashboard</span> <kbd class="px-2 py-1 bg-gray-100 rounded">G + D</kbd></div>
                                <div class="flex justify-between"><span>Sales</span> <kbd class="px-2 py-1 bg-gray-100 rounded">G + S</kbd></div>
                                <div class="flex justify-between"><span>Finance</span> <kbd class="px-2 py-1 bg-gray-100 rounded">G + F</kbd></div>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold mb-3">Actions:</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between"><span>New Record</span> <kbd class="px-2 py-1 bg-gray-100 rounded">Ctrl + N</kbd></div>
                                <div class="flex justify-between"><span>Save</span> <kbd class="px-2 py-1 bg-gray-100 rounded">Ctrl + S</kbd></div>
                                <div class="flex justify-between"><span>Export</span> <kbd class="px-2 py-1 bg-gray-100 rounded">Ctrl + E</kbd></div>
                                <div class="flex justify-between"><span>Print</span> <kbd class="px-2 py-1 bg-gray-100 rounded">Ctrl + P</kbd></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            
            <section id="troubleshooting" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">🔧 Troubleshooting</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm space-y-6">
                    <div>
                        <h3 class="font-semibold text-lg mb-2">Cannot Login</h3>
                        <p class="text-gray-700 mb-2"><strong>Solusi:</strong></p>
                        <ul class="list-disc list-inside space-y-1 text-gray-700">
                            <li>Periksa username (email) benar</li>
                            <li>Verifikasi password (case-sensitive)</li>
                            <li>Klik "Forgot Password?" untuk reset</li>
                            <li>Hubungi admin jika akun terkunci</li>
                        </ul>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="font-semibold text-lg mb-2">Slow Performance</h3>
                        <p class="text-gray-700 mb-2"><strong>Solusi:</strong></p>
                        <ul class="list-disc list-inside space-y-1 text-gray-700">
                            <li>Periksa koneksi internet</li>
                            <li>Clear browser cache</li>
                            <li>Tutup tab browser yang tidak digunakan</li>
                            <li>Coba browser lain</li>
                        </ul>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="font-semibold text-lg mb-2">Export Failed</h3>
                        <p class="text-gray-700 mb-2"><strong>Solusi:</strong></p>
                        <ul class="list-disc list-inside space-y-1 text-gray-700">
                            <li>Periksa pengaturan download browser</li>
                            <li>Pastikan ada space disk cukup</li>
                            <li>Coba format export berbeda</li>
                            <li>Kurangi data dengan filter</li>
                        </ul>
                    </div>
                </div>
            </section>

            
            <section id="faq" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">❓ FAQ</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm space-y-6">
                    <div x-data="{ open: false }" class="border-b pb-4">
                        <button @click="open = !open" class="flex justify-between items-center w-full text-left font-semibold">
                            <span>Bagaimana cara reset password?</span>
                            <svg class="w-5 h-5 transform transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" class="mt-3 text-gray-700">
                            Klik "Forgot Password?" di halaman login, masukkan email Anda, lalu ikuti link reset yang dikirim ke email.
                        </div>
                    </div>
                    
                    <div x-data="{ open: false }" class="border-b pb-4">
                        <button @click="open = !open" class="flex justify-between items-center w-full text-left font-semibold">
                            <span>Apakah bisa digunakan offline?</span>
                            <svg class="w-5 h-5 transform transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" class="mt-3 text-gray-700">
                            Mode offline terbatas tersedia untuk POS dan attendance. Data akan otomatis sync saat koneksi pulih.
                        </div>
                    </div>
                    
                    <div x-data="{ open: false }" class="border-b pb-4">
                        <button @click="open = !open" class="flex justify-between items-center w-full text-left font-semibold">
                            <span>Browser apa saja yang didukung?</span>
                            <svg class="w-5 h-5 transform transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" class="mt-3 text-gray-700">
                            Chrome 90+, Firefox 88+, Edge 90+, Safari 14+. Direkomendasikan menggunakan browser versi terbaru.
                        </div>
                    </div>
                    
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="flex justify-between items-center w-full text-left font-semibold">
                            <span>Bagaimana cara backup data?</span>
                            <svg class="w-5 h-5 transform transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" class="mt-3 text-gray-700">
                            Backup dilakukan otomatis setiap hari oleh sistem. Anda juga bisa export data manual melalui menu Export di setiap modul.
                        </div>
                    </div>
                </div>
            </section>

            
            <section id="best-practices" class="mb-16">
                <h2 class="text-3xl font-bold mb-6">✅ Best Practices</h2>
                <div class="bg-white rounded-2xl p-8 border border-gray-200 shadow-sm space-y-6">
                    <div>
                        <h3 class="font-semibold text-lg mb-3">Data Entry:</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li>✅ Selalu isi field required</li>
                            <li>✅ Gunakan naming convention konsisten</li>
                            <li>✅ Double-check sebelum save</li>
                            <li>✅ Attach dokumen relevan</li>
                            <li>✅ Tambahkan notes untuk clarity</li>
                        </ul>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="font-semibold text-lg mb-3">Security:</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li>🔒 Gunakan password kuat</li>
                            <li>🔒 Aktifkan 2FA</li>
                            <li>🔒 Logout setelah selesai</li>
                            <li>🔒 Jangan share credentials</li>
                            <li>🔒 Report aktivitas mencurigakan</li>
                        </ul>
                    </div>
                    
                    <div class="border-t pt-6">
                        <h3 class="font-semibold text-lg mb-3">Performance:</h3>
                        <ul class="list-disc list-inside space-y-2 text-gray-700">
                            <li>⚡ Clear cache bulanan</li>
                            <li>⚡ Gunakan filter untuk data besar</li>
                            <li>⚡ Export daripada view 1000+ rows</li>
                            <li>⚡ Tutup tab yang tidak digunakan</li>
                            <li>⚡ Gunakan keyboard shortcuts</li>
                        </ul>
                    </div>
                </div>
            </section>

        </main>
    </div>

    
    <footer class="ml-72 bg-white border-t border-gray-200 py-8">
        <div class="max-w-5xl px-8 text-center">
            <p class="text-gray-600 mb-2">© 2026 Qalcuity ERP. All rights reserved.</p>
            <p class="text-sm text-gray-500">
                <a href="<?php echo e(route('landing')); ?>" class="hover:text-blue-600">Beranda</a>
                <span class="mx-2">•</span>
                <a href="/api-docs" target="_blank" class="hover:text-blue-600">API Docs</a>
                <span class="mx-2">•</span>
                <a href="mailto:support@qalcuity.com" class="hover:text-blue-600">Support</a>
            </p>
        </div>
    </footer>

    
    <script>
        // Smooth scroll & active link
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', function(e) {
                document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/documentation.blade.php ENDPATH**/ ?>