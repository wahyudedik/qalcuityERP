<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HelpTopicsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * TASK-015: Seed help topics with Indonesian content
     */
    public function run(): void
    {
        $topics = [
            // Sales Module
            [
                'topic_key' => 'customer-selection',
                'module' => 'sales',
                'page' => 'invoices.create',
                'field' => 'customer_id',
                'title' => 'Cara Memilih Customer',
                'content' => 'Pilih customer dari daftar yang tersedia. Anda bisa mencari customer berdasarkan nama, email, atau nomor telepon.',
                'tips' => json_encode([
                    'Gunakan kolom search untuk mencari customer dengan cepat',
                    'Klik "Tambah Customer Baru" jika customer belum ada di daftar',
                    'Customer yang sudah dinonaktifkan tidak akan muncul di daftar',
                    'Anda bisa melihat detail customer dengan klik ikon mata'
                ]),
                'video_url' => '/help/videos/customer-selection.mp4',
                'documentation_url' => '/docs/sales/customers',
                'order' => 1,
                'is_active' => true,
            ],

            [
                'topic_key' => 'invoice-status',
                'module' => 'sales',
                'page' => 'invoices.index',
                'field' => null,
                'title' => 'Status Invoice',
                'content' => 'Invoice memiliki beberapa status yang menunjukkan tahap pembayaran:',
                'tips' => json_encode([
                    'Unpaid: Invoice belum dibayar',
                    'Partial: Invoice sudah dibayar sebagian',
                    'Paid: Invoice sudah lunas',
                    'Overdue: Invoice sudah melewati jatuh tempo',
                    'Cancelled: Invoice dibatalkan'
                ]),
                'order' => 2,
                'is_active' => true,
            ],

            [
                'topic_key' => 'product-pricing',
                'module' => 'sales',
                'page' => 'products.create',
                'field' => 'price_sell',
                'title' => 'Cara Menentukan Harga Produk',
                'content' => 'Anda bisa menentukan harga jual produk dengan beberapa cara:',
                'tips' => json_encode([
                    'Harga Manual: Masukkan harga secara langsung',
                    'Markup dari Harga Beli: Sistem otomatis menghitung dari harga beli + markup',
                    'Harga Bertingkat: Set harga berbeda untuk quantity berbeda',
                    'Harga Khusus Customer: Set harga khusus untuk customer tertentu'
                ]),
                'order' => 3,
                'is_active' => true,
            ],

            // Inventory Module
            [
                'topic_key' => 'stock-management',
                'module' => 'inventory',
                'page' => 'products.index',
                'field' => null,
                'title' => 'Manajemen Stok',
                'content' => 'Kelola stok produk dengan fitur berikut:',
                'tips' => json_encode([
                    'Stok minimum: Set batas minimum untuk alert restock',
                    'Multi-gudang: Kelola stok di beberapa gudang',
                    'Batch tracking: Lacak produk berdasarkan batch',
                    'Expiry date: Kelola produk yang memiliki tanggal kadaluarsa',
                    'Stock opname: Lakukan stock opname secara berkala'
                ]),
                'order' => 1,
                'is_active' => true,
            ],

            // HRM Module
            [
                'topic_key' => 'employee-status',
                'module' => 'hrm',
                'page' => 'employees.index',
                'field' => null,
                'title' => 'Status Karyawan',
                'content' => 'Status karyawan menunjukkan keadaan kepegawaian:',
                'tips' => json_encode([
                    'Active: Karyawan masih aktif bekerja',
                    'Inactive: Karyawan tidak aktif (cuti panjang, dll)',
                    'Terminated: Karyawan sudah tidak bekerja',
                    'Probation: Karyawan masih masa percobaan',
                    'Contract: Karyawan kontrak'
                ]),
                'order' => 1,
                'is_active' => true,
            ],

            [
                'topic_key' => 'payroll-processing',
                'module' => 'hrm',
                'page' => 'payroll.process',
                'field' => null,
                'title' => 'Proses Payroll',
                'content' => 'Proses payroll bulanan untuk menghitung gaji karyawan:',
                'tips' => json_encode([
                    'Pastikan semua attendance sudah terinput',
                    'Cek overtime yang belum diproses',
                    'Review potongan dan tunjangan',
                    'Generate slip gaji otomatis',
                    'Export ke format yang dibutuhkan'
                ]),
                'order' => 2,
                'is_active' => true,
            ],

            // Finance Module
            [
                'topic_key' => 'journal-entry',
                'module' => 'finance',
                'page' => 'journals.create',
                'field' => null,
                'title' => 'Cara Membuat Jurnal',
                'content' => 'Jurnal akuntansi mencatat transaksi keuangan:',
                'tips' => json_encode([
                    'Pastikan debit = credit (balance)',
                    'Pilih akun yang sesuai dengan transaksi',
                    'Isi deskripsi yang jelas untuk memudahkan tracking',
                    'Attach dokumen pendukung jika ada',
                    'Review sebelum posting'
                ]),
                'order' => 1,
                'is_active' => true,
            ],

            // Manufacturing Module
            [
                'topic_key' => 'bom-creation',
                'module' => 'manufacturing',
                'page' => 'boms.create',
                'field' => null,
                'title' => 'Membuat Bill of Materials (BOM)',
                'content' => 'BOM mendefinisikan komponen yang dibutuhkan untuk membuat produk:',
                'tips' => json_encode([
                    'Pilih produk jadi terlebih dahulu',
                    'Tambahkan semua komponen dengan quantity yang tepat',
                    'Set loss percentage jika ada waste',
                    'Review total cost sebelum menyimpan',
                    'BOM bisa memiliki versi untuk tracking perubahan'
                ]),
                'order' => 1,
                'is_active' => true,
            ],
        ];

        // Insert topics
        foreach ($topics as $topic) {
            DB::table('help_topics')->insert([
                'topic_key' => $topic['topic_key'],
                'module' => $topic['module'],
                'page' => $topic['page'],
                'field' => $topic['field'],
                'title' => $topic['title'],
                'content' => $topic['content'],
                'tips' => $topic['tips'],
                'video_url' => $topic['video_url'] ?? null,
                'documentation_url' => $topic['documentation_url'] ?? null,
                'order' => $topic['order'],
                'is_active' => $topic['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Help topics seeded successfully!');
    }
}
